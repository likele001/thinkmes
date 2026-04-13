<?php
declare(strict_types=1);

namespace app\admin\service;

use app\admin\model\Workflow;
use app\admin\model\WorkflowState;
use app\admin\model\WorkflowTransition;
use app\admin\model\WorkflowInstance;
use app\admin\model\WorkflowApproval;
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

    public function startWorkflow(string $tableName, int $recordId, string $title): ?WorkflowInstance
    {
        $workflow = (new Workflow())->getActiveWorkflowByTable($tableName, $this->tenantId);
        if (!$workflow) {
            throw new \Exception('未找到激活的工作流');
        }

        $initialState = WorkflowState::where('workflow_id', $workflow->id)
            ->where('is_initial', 1)
            ->find();
        if (!$initialState) {
            throw new \Exception('工作流未设置初始状态');
        }

        Db::startTrans();
        try {
            $instance = WorkflowInstance::create([
                'workflow_id'     => $workflow->id,
                'table_name'      => $tableName,
                'record_id'       => $recordId,
                'current_state_id'=> $initialState->id,
                'title'           => $title,
                'initiator_id'    => $this->currentUserId,
                'initiator_name'  => $this->currentUserName,
                'tenant_id'       => $this->tenantId,
            ]);

            Db::commit();
            return $instance;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function getAvailableTransitions(string $tableName, int $recordId): array
    {
        $instance = (new WorkflowInstance())->getInstanceByRecord($tableName, $recordId);
        if (!$instance || $instance->is_completed) {
            return [];
        }

        $transitions = WorkflowTransition::where('workflow_id', $instance->workflow_id)
            ->where('from_state_id', $instance->current_state_id)
            ->order('sort', 'asc')
            ->select();

        return $transitions->toArray();
    }

    public function transition(string $tableName, int $recordId, string $transitionCode, string $comment = ''): WorkflowInstance
    {
        $instance = (new WorkflowInstance())->getInstanceByRecord($tableName, $recordId);
        if (!$instance) {
            throw new \Exception('工作流实例不存在');
        }

        if ($instance->is_completed) {
            throw new \Exception('工作流已完成，无法再进行转换');
        }

        $transition = WorkflowTransition::where('workflow_id', $instance->workflow_id)
            ->where('from_state_id', $instance->current_state_id)
            ->where('code', $transitionCode)
            ->find();

        if (!$transition) {
            throw new \Exception('无效的转换');
        }

        $toState = WorkflowState::find($transition->to_state_id);
        if (!$toState) {
            throw new \Exception('目标状态不存在');
        }

        if ($transition->require_approval) {
            return $this->startApproval($instance, $transition, $comment);
        } else {
            return $this->completeTransition($instance, $transition, $toState);
        }
    }

    protected function startApproval(WorkflowInstance $instance, WorkflowTransition $transition, string $comment = ''): WorkflowInstance
    {
        $approvers = $transition->approvers;
        if ($approvers->isEmpty()) {
            throw new \Exception('未设置审批人');
        }

        Db::startTrans();
        try {
            foreach ($approvers as $approver) {
                WorkflowApproval::create([
                    'instance_id'    => $instance->id,
                    'transition_id'  => $transition->id,
                    'from_state_id'  => $instance->current_state_id,
                    'to_state_id'    => $transition->to_state_id,
                    'approver_id'    => $approver->approver_id,
                    'approver_name'  => $approver->approver_name,
                    'status'         => 'pending',
                    'comment'        => '',
                ]);
            }

            Db::commit();
            return $instance->refresh();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function approve(int $instanceId, string $comment = '', bool $approve = true): WorkflowInstance
    {
        $instance = WorkflowInstance::find($instanceId);
        if (!$instance) {
            throw new \Exception('工作流实例不存在');
        }

        $approval = WorkflowApproval::where('instance_id', $instanceId)
            ->where('approver_id', $this->currentUserId)
            ->where('status', 'pending')
            ->find();

        if (!$approval) {
            throw new \Exception('没有待审批的记录');
        }

        $transition = WorkflowTransition::find($approval->transition_id);
        if (!$transition) {
            throw new \Exception('转换记录不存在');
        }

        Db::startTrans();
        try {
            $approval->status = $approve ? 'approved' : 'rejected';
            $approval->comment = $comment;
            $approval->approval_time = time();
            $approval->save();

            if (!$approve) {
                return $this->completeTransition($instance, $transition, WorkflowState::find($transition->to_state_id));
            }

            $pendingApprovals = WorkflowApproval::where('instance_id', $instanceId)
                ->where('status', 'pending')
                ->count();

            if ($pendingApprovals === 0) {
                $toState = WorkflowState::find($transition->to_state_id);
                return $this->completeTransition($instance, $transition, $toState);
            }

            Db::commit();
            return $instance->refresh();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    protected function completeTransition(WorkflowInstance $instance, WorkflowTransition $transition, WorkflowState $toState): WorkflowInstance
    {
        $instance->current_state_id = $toState->id;
        $instance->is_completed = $toState->is_final ? 1 : 0;
        $instance->completed_time = $toState->is_final ? time() : 0;
        $instance->save();

        WorkflowApproval::where('instance_id', $instance->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        return $instance->refresh();
    }

    public function getInstance(string $tableName, int $recordId): ?WorkflowInstance
    {
        return (new WorkflowInstance())->getInstanceByRecord($tableName, $recordId);
    }

    public function getInstanceHistory(string $tableName, int $recordId): array
    {
        $instance = $this->getInstance($tableName, $recordId);
        if (!$instance) {
            return [];
        }

        return WorkflowApproval::where('instance_id', $instance->id)
            ->order('create_time', 'desc')
            ->select()
            ->toArray();
    }

    public function getWorkflowGraph(string $tableName, int $recordId): array
    {
        $instance = $this->getInstance($tableName, $recordId);
        $workflow = null;

        if ($instance) {
            $workflow = Workflow::with(['states', 'transitions.fromState', 'transitions.toState'])
                ->find($instance->workflow_id);
        } else {
            $workflow = (new Workflow())->getActiveWorkflowByTable($tableName, $this->tenantId);
            if ($workflow) {
                $workflow->load(['states', 'transitions.fromState', 'transitions.toState']);
            }
        }

        if (!$workflow) {
            return [];
        }

        $states = [];
        foreach ($workflow->states as $state) {
            $states[] = [
                'id'         => $state->id,
                'name'       => $state->name,
                'code'       => $state->code,
                'is_initial' => (bool)$state->is_initial,
                'is_final'   => (bool)$state->is_final,
                'sort'       => (int)$state->sort,
            ];
        }

        $transitions = [];
        $transitionsMap = [];
        foreach ($workflow->transitions as $transition) {
            $transitions[] = [
                'id'               => $transition->id,
                'from_state_id'    => $transition->from_state_id,
                'to_state_id'      => $transition->to_state_id,
                'code'             => $transition->code,
                'name'             => $transition->name,
                'require_approval' => (bool)$transition->require_approval,
                'from_state'       => $transition->fromState ? $transition->fromState->name : '',
                'to_state'         => $transition->toState ? $transition->toState->name : '',
            ];
            $transitionsMap[$transition->from_state_id] = $transition->name ?: '';
        }

        usort($states, function ($a, $b) {
            return $a['sort'] <=> $b['sort'];
        });

        $currentStateSort = 0;
        if ($instance && $instance->current_state_id) {
            foreach ($states as $state) {
                if ($state['id'] === $instance->current_state_id) {
                    $currentStateSort = $state['sort'];
                    break;
                }
            }
        }

        foreach ($states as &$state) {
            if (!$instance) {
                $state['status'] = $state['is_initial'] ? 'active' : 'upcoming';
                $state['status_text'] = $state['is_initial'] ? '待启动' : '等待中';
            } elseif ($state['id'] === $instance->current_state_id) {
                $state['status'] = 'active';
                $state['status_text'] = '当前节点';
            } elseif ($currentStateSort && $state['sort'] < $currentStateSort) {
                $state['status'] = 'completed';
                $state['status_text'] = '已完成';
            } else {
                $state['status'] = 'upcoming';
                $state['status_text'] = $state['is_final'] ? '结束节点' : '待执行';
            }
            $state['next_transition_name'] = $transitionsMap[$state['id']] ?? '';
        }
        unset($state);

        return [
            'workflow_id'      => $workflow->id,
            'workflow_name'    => $workflow->name,
            'current_state_id' => $instance ? $instance->current_state_id : 0,
            'is_completed'     => $instance ? (bool)$instance->is_completed : false,
            'states'           => $states,
            'transitions'      => $transitions,
        ];
    }

    public function getCurrentStatus(string $tableName, int $recordId): ?array
    {
        $instance = $this->getInstance($tableName, $recordId);
        if (!$instance) {
            return null;
        }

        $instance->load(['currentState', 'workflow']);

        return [
            'instance_id'     => $instance->id,
            'workflow_name'   => $instance->workflow->name ?? '',
            'current_state'   => $instance->currentState->name ?? '',
            'current_state_code' => $instance->currentState->code ?? '',
            'state_color'     => $instance->currentState->color ?? '#1890ff',
            'is_completed'    => (bool)$instance->is_completed,
            'initiator_name'  => $instance->initiator_name,
            'create_time'     => $instance->create_time,
        ];
    }

    public function getPendingApprovals(int $userId): array
    {
        $approvals = WorkflowApproval::where('approver_id', $userId)
            ->where('status', 'pending')
            ->with(['instance', 'transition', 'fromState', 'toState'])
            ->order('create_time', 'asc')
            ->select()
            ->toArray();

        return $approvals;
    }

    public function withdraw(int $instanceId): WorkflowInstance
    {
        $instance = WorkflowInstance::find($instanceId);
        if (!$instance) {
            throw new \Exception('工作流实例不存在');
        }

        Db::startTrans();
        try {
            WorkflowApproval::where('instance_id', $instanceId)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            $instance->is_completed = 1;
            $instance->completed_time = time();
            $instance->save();

            Db::commit();
            return $instance->refresh();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function deleteInstance(string $tableName, int $recordId): bool
    {
        $instance = $this->getInstance($tableName, $recordId);
        if (!$instance) {
            return false;
        }

        WorkflowApproval::where('instance_id', $instance->id)->delete();
        $instance->delete();

        return true;
    }
}
