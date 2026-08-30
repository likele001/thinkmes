<?php
declare(strict_types=1);

namespace app\admin\controller\workflow;

use app\admin\controller\Backend;
use app\common\service\workflow\WorkflowEngine;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

class Approval extends Backend
{
    public function index(): string
    {
        View::assign('title', '审批中心');
        return $this->fetchWithLayout('workflow/approval/index');
    }

    public function pending(): Response
    {
        $tenantId = $this->getTenantId();
        $admin = Session::get('admin_info') ?: [];
        $adminId = (int) ($admin['id'] ?? 0);
        if ($tenantId <= 0 || $adminId <= 0) {
            return $this->success('', ['total' => 0, 'list' => []]);
        }
        [$limit, $page] = $this->getPaginationParams(20, 100);
        $q = Db::name('wf_task')
            ->where('tenant_id', $tenantId)
            ->where('approver_id', $adminId)
            ->where('status', 0)
            ->order('id', 'desc');
        $total = (int) $q->count();
        $list = $q->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function done(): Response
    {
        $tenantId = $this->getTenantId();
        $admin = Session::get('admin_info') ?: [];
        $adminId = (int) ($admin['id'] ?? 0);
        if ($tenantId <= 0 || $adminId <= 0) {
            return $this->success('', ['total' => 0, 'list' => []]);
        }
        [$limit, $page] = $this->getPaginationParams(20, 100);
        $q = Db::name('wf_task')
            ->where('tenant_id', $tenantId)
            ->where('approver_id', $adminId)
            ->whereIn('status', [1, 2, 3, 4])
            ->order('action_time', 'desc')
            ->order('id', 'desc');
        $total = (int) $q->count();
        $list = $q->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function mine(): Response
    {
        $tenantId = $this->getTenantId();
        $admin = Session::get('admin_info') ?: [];
        $adminId = (int) ($admin['id'] ?? 0);
        if ($tenantId <= 0 || $adminId <= 0) {
            return $this->success('', ['total' => 0, 'list' => []]);
        }
        [$limit, $page] = $this->getPaginationParams(20, 100);
        $q = Db::name('wf_instance')
            ->where('tenant_id', $tenantId)
            ->where('initiator_id', $adminId)
            ->order('id', 'desc');
        $total = (int) $q->count();
        $list = $q->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function detail(): string|Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);
        if ($tenantId <= 0 || $id <= 0) {
            return $this->error('参数错误');
        }
        $inst = Db::name('wf_instance')->where('tenant_id', $tenantId)->where('id', $id)->find();
        if (!$inst) {
            return $this->error('实例不存在');
        }
        if ($this->request->isAjax()) {
            $nodes = Db::name('wf_node')
                ->where('tenant_id', $tenantId)
                ->where('definition_id', (int) ($inst['definition_id'] ?? 0))
                ->order('sort', 'asc')
                ->select()
                ->toArray();
            $tasks = Db::name('wf_task')
                ->where('tenant_id', $tenantId)
                ->where('instance_id', $id)
                ->order('id', 'asc')
                ->select()
                ->toArray();
            $logs = Db::name('wf_log')
                ->where('tenant_id', $tenantId)
                ->where('instance_id', $id)
                ->order('id', 'asc')
                ->select()
                ->toArray();
            return $this->success('', ['instance' => $inst, 'nodes' => $nodes, 'tasks' => $tasks, 'logs' => $logs]);
        }
        View::assign('title', '审批详情');
        View::assign('instance', $inst);
        return $this->fetchWithLayout('workflow/approval/detail');
    }

    public function doApprove(): Response
    {
        $tenantId = $this->getTenantId();
        $admin = Session::get('admin_info') ?: [];
        $adminId = (int) ($admin['id'] ?? 0);
        $taskId = (int) $this->request->post('task_id', 0);
        $comment = (string) $this->request->post('comment', '');
        if ($tenantId <= 0 || $adminId <= 0 || $taskId <= 0) {
            return $this->error('参数错误');
        }
        try {
            $res = WorkflowEngine::approve($tenantId, $taskId, $adminId, $comment);
            return $this->success('已通过', $res);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function doReject(): Response
    {
        $tenantId = $this->getTenantId();
        $admin = Session::get('admin_info') ?: [];
        $adminId = (int) ($admin['id'] ?? 0);
        $taskId = (int) $this->request->post('task_id', 0);
        $comment = (string) $this->request->post('comment', '');
        if ($tenantId <= 0 || $adminId <= 0 || $taskId <= 0) {
            return $this->error('参数错误');
        }
        try {
            $res = WorkflowEngine::reject($tenantId, $taskId, $adminId, $comment);
            return $this->success('已拒绝', $res);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function doTransfer(): Response
    {
        $tenantId = $this->getTenantId();
        $admin = Session::get('admin_info') ?: [];
        $adminId = (int) ($admin['id'] ?? 0);
        $taskId = (int) $this->request->post('task_id', 0);
        $toAdminId = (int) $this->request->post('to_admin_id', 0);
        $comment = (string) $this->request->post('comment', '');
        if ($tenantId <= 0 || $adminId <= 0 || $taskId <= 0 || $toAdminId <= 0) {
            return $this->error('参数错误');
        }
        try {
            $res = WorkflowEngine::transfer($tenantId, $taskId, $adminId, $toAdminId, $comment);
            return $this->success('已转交', $res);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function doWithdraw(): Response
    {
        $tenantId = $this->getTenantId();
        $admin = Session::get('admin_info') ?: [];
        $adminId = (int) ($admin['id'] ?? 0);
        $instanceId = (int) $this->request->post('instance_id', 0);
        $comment = (string) $this->request->post('comment', '');
        if ($tenantId <= 0 || $adminId <= 0 || $instanceId <= 0) {
            return $this->error('参数错误');
        }
        try {
            $res = WorkflowEngine::withdraw($tenantId, $instanceId, $adminId, $comment);
            return $this->success('已撤回', $res);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function adminOptions(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) {
            return $this->success('', ['list' => []]);
        }
        $list = Db::name('admin')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->order('id', 'asc')
            ->field('id,nickname,username')
            ->select()
            ->toArray();
        $out = [];
        foreach ($list as $r) {
            $id = (int) ($r['id'] ?? 0);
            $name = (string) (($r['nickname'] ?? '') ?: ($r['username'] ?? '') ?: ('admin' . $id));
            $out[] = ['id' => $id, 'name' => $name];
        }
        return $this->success('', ['list' => $out]);
    }

    public function stats(): Response
    {
        $tenantId = $this->getTenantId();
        $admin = Session::get('admin_info') ?: [];
        $adminId = (int) ($admin['id'] ?? 0);
        if ($tenantId <= 0 || $adminId <= 0) {
            return $this->success('', [
                'pending' => 0,
                'done' => 0,
                'mine' => 0,
                'avg_time' => '-'
            ]);
        }

        // 待审批数量
        $pending = (int) Db::name('wf_task')
            ->where('tenant_id', $tenantId)
            ->where('approver_id', $adminId)
            ->where('status', 0)
            ->count();

        // 已审批数量
        $done = (int) Db::name('wf_task')
            ->where('tenant_id', $tenantId)
            ->where('approver_id', $adminId)
            ->whereIn('status', [1, 2, 4])
            ->count();

        // 我发起的数量
        $mine = (int) Db::name('wf_instance')
            ->where('tenant_id', $tenantId)
            ->where('initiator_id', $adminId)
            ->count();

        // 平均耗时（小时）
        $avgTime = Db::name('wf_task')
            ->where('tenant_id', $tenantId)
            ->where('approver_id', $adminId)
            ->whereIn('status', [1, 2])
            ->field('AVG(action_time - create_time) as avg_time')
            ->find();
        $avgHours = 0;
        if ($avgTime && isset($avgTime['avg_time'])) {
            $avgHours = round((float) $avgTime['avg_time'] / 3600, 1);
        }

        return $this->success('', [
            'pending' => $pending,
            'done' => $done,
            'mine' => $mine,
            'avg_time' => $avgHours > 0 ? $avgHours . 'h' : '-'
        ]);
    }
}

