<?php
declare(strict_types=1);

namespace app\admin\service;

use app\common\service\workflow\WorkflowEngine;
use think\facade\Db;

class WorkflowService
{
    protected int $tenantId = 0;
    protected int $currentUserId = 0;
    protected string $currentUserName = '';

    public function __construct(int $tenantId = 0, int $currentUserId = 0, string $currentUserName = '')
    {
        $this->tenantId = $tenantId;
        $this->currentUserId = $currentUserId;
        $this->currentUserName = $currentUserName;
    }

    public function startWorkflow(string $tableName, int $recordId, string $title): array
    {
        return WorkflowEngine::start(
            $this->tenantId,
            $tableName,
            $recordId,
            $this->currentUserId,
            ['business_title' => $title]
        );
    }

    public function getAvailableTransitions(string $tableName, int $recordId): array
    {
        $instance = $this->getInstance($tableName, $recordId);
        if (!$instance || (int) ($instance['status'] ?? 0) !== 0) {
            return [];
        }

        $nodeSort = (int) ($instance['current_sort'] ?? 0);
        $definitionId = (int) ($instance['definition_id'] ?? 0);
        if ($definitionId <= 0) {
            return [];
        }

        $nodes = Db::name('wf_node')
            ->where('tenant_id', $this->tenantId)
            ->where('definition_id', $definitionId)
            ->where('sort', '>', $nodeSort)
            ->order('sort', 'asc')
            ->field('id,name,sort')
            ->select()
            ->toArray();

        $out = [];
        foreach ($nodes as $node) {
            $out[] = [
                'id' => (int) ($node['id'] ?? 0),
                'code' => 'to_' . (int) ($node['sort'] ?? 0),
                'name' => (string) ($node['name'] ?? ''),
                'from_state_id' => (int) ($instance['current_node_id'] ?? 0),
                'to_state_id' => (int) ($node['id'] ?? 0),
            ];
        }
        return $out;
    }

    public function transition(string $tableName, int $recordId, string $transitionCode, string $comment = ''): array
    {
        throw new \RuntimeException('请使用审批中心执行流转操作');
    }

    public function approve(int $instanceId, string $comment = '', bool $approve = true): array
    {
        $task = Db::name('wf_task')
            ->where('tenant_id', $this->tenantId)
            ->where('instance_id', $instanceId)
            ->where('approver_id', $this->currentUserId)
            ->where('status', 0)
            ->order('id', 'asc')
            ->find();
        if (!$task) {
            throw new \Exception('工作流实例不存在');
        }
        if ($approve) {
            return WorkflowEngine::approve($this->tenantId, (int) $task['id'], $this->currentUserId, $comment);
        }
        return WorkflowEngine::reject($this->tenantId, (int) $task['id'], $this->currentUserId, $comment);
    }

    public function getInstance(string $tableName, int $recordId): ?array
    {
        return Db::name('wf_instance')
            ->where('tenant_id', $this->tenantId)
            ->where('module_code', $tableName)
            ->where('business_id', $recordId)
            ->find() ?: null;
    }

    public function getInstanceHistory(string $tableName, int $recordId): array
    {
        $instance = $this->getInstance($tableName, $recordId);
        if (!$instance) {
            return [];
        }

        return Db::name('wf_task')
            ->where('tenant_id', $this->tenantId)
            ->where('instance_id', (int) ($instance['id'] ?? 0))
            ->order('id', 'desc')
            ->select()
            ->toArray();
    }

    public function getWorkflowGraph(string $tableName, int $recordId): array
    {
        $instance = $this->getInstance($tableName, $recordId);
        $module = Db::name('wf_module')
            ->where('tenant_id', $this->tenantId)
            ->where('module_code', $tableName)
            ->find();
        $definitionId = (int) ($module['definition_id'] ?? 0);
        if ($definitionId <= 0) {
            return [];
        }
        $states = Db::name('wf_node')
            ->where('tenant_id', $this->tenantId)
            ->where('definition_id', $definitionId)
            ->order('sort', 'asc')
            ->field('id,name,sort')
            ->select()
            ->toArray();

        $currentStateSort = 0;
        if ($instance && (int) ($instance['current_node_id'] ?? 0) > 0) {
            foreach ($states as $state) {
                if ((int) ($state['id'] ?? 0) === (int) ($instance['current_node_id'] ?? 0)) {
                    $currentStateSort = (int) ($state['sort'] ?? 0);
                    break;
                }
            }
        }

        foreach ($states as &$state) {
            if (!$instance) {
                $state['status'] = (int) ($state['sort'] ?? 0) === 1 ? 'active' : 'upcoming';
                $state['status_text'] = (int) ($state['sort'] ?? 0) === 1 ? '待启动' : '等待中';
            } elseif ((int) ($state['id'] ?? 0) === (int) ($instance['current_node_id'] ?? 0)) {
                $state['status'] = 'active';
                $state['status_text'] = '当前节点';
            } elseif ($currentStateSort && $state['sort'] < $currentStateSort) {
                $state['status'] = 'completed';
                $state['status_text'] = '已完成';
            } else {
                $state['status'] = 'upcoming';
                $state['status_text'] = '待执行';
            }
            $state['next_transition_name'] = '';
        }
        unset($state);

        return [
            'workflow_id'      => $definitionId,
            'workflow_name'    => (string) ($module['module_name'] ?? $tableName),
            'current_state_id' => $instance ? (int) ($instance['current_node_id'] ?? 0) : 0,
            'is_completed'     => $instance ? ((int) ($instance['status'] ?? 0) !== 0) : false,
            'states'           => $states,
            'transitions'      => [],
        ];
    }

    public function getCurrentStatus(string $tableName, int $recordId): ?array
    {
        $instance = $this->getInstance($tableName, $recordId);
        if (!$instance) {
            return null;
        }

        $node = Db::name('wf_node')
            ->where('tenant_id', $this->tenantId)
            ->where('id', (int) ($instance['current_node_id'] ?? 0))
            ->find();

        return [
            'instance_id'     => (int) ($instance['id'] ?? 0),
            'workflow_name'   => '',
            'current_state'   => (string) ($node['name'] ?? ''),
            'current_state_code' => (string) ($node['id'] ?? ''),
            'state_color'     => '#1890ff',
            'is_completed'    => (int) ($instance['status'] ?? 0) !== 0,
            'initiator_name'  => (string) ($instance['initiator_name'] ?? ''),
            'create_time'     => (int) ($instance['create_time'] ?? 0),
        ];
    }

    public function getPendingApprovals(int $userId): array
    {
        return Db::name('wf_task')
            ->where('tenant_id', $this->tenantId)
            ->where('approver_id', $userId)
            ->where('status', 0)
            ->order('create_time', 'asc')
            ->select()
            ->toArray();
    }

    public function withdraw(int $instanceId): array
    {
        return WorkflowEngine::withdraw($this->tenantId, $instanceId, $this->currentUserId, '');
    }

    public function deleteInstance(string $tableName, int $recordId): bool
    {
        $instance = $this->getInstance($tableName, $recordId);
        if (!$instance) {
            return false;
        }

        Db::name('wf_task')
            ->where('tenant_id', $this->tenantId)
            ->where('instance_id', (int) ($instance['id'] ?? 0))
            ->delete();
        Db::name('wf_instance')
            ->where('tenant_id', $this->tenantId)
            ->where('id', (int) ($instance['id'] ?? 0))
            ->delete();
        return true;
    }
}
