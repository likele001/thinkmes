<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ProcessModel;
use app\admin\model\mes\UserProcessCapacityModel;
use app\common\model\UserModel;
use think\facade\View;
use think\Response;

class UserProcessCapacity extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->getTenantId();
            View::assign('title', '员工产能(计件)');
            View::assign('tenant_id', $tenantId);
            return $this->fetchWithLayout('mes/user_process_capacity/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = UserProcessCapacityModel::with(['user', 'process'])->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) $query->where('tenant_id', $tenantParam);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['user_name'] = $row['user']['nickname'] ?? ($row['user']['username'] ?? '-');
            $row['process_name'] = $row['process']['name'] ?? '-';
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) $tenantId = (int) $this->request->param('tenant_id', 0);
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) return $this->error('参数不能为空');
            $userId = (int) ($params['user_id'] ?? 0);
            $processId = (int) ($params['process_id'] ?? 0);
            $cap = (int) ($params['capacity_per_day'] ?? 0);
            if ($tenantId <= 0 || $userId <= 0 || $processId <= 0) return $this->error('请选择租户/员工/工序');
            if ($cap <= 0) return $this->error('日产能必须大于0');
            $now = time();
            $data = [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'process_id' => $processId,
                'capacity_per_day' => $cap,
                'status' => (int) ($params['status'] ?? 1) ? 1 : 0,
                'create_time' => $now,
                'update_time' => $now,
            ];
            try {
                UserProcessCapacityModel::create($data);
                return $this->success('添加成功');
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        $users = UserModel::where('tenant_id', $tenantId)->where('status', 1)->column('nickname', 'id');
        $processes = ProcessModel::where('tenant_id', $tenantId)->where('status', 1)->column('name', 'id');
        View::assign('users', $users ?: []);
        View::assign('processes', $processes ?: []);
        View::assign('tenant_id', $tenantId);
        View::assign('title', '添加员工产能');
        return $this->fetchWithLayout('mes/user_process_capacity/add');
    }

    public function edit(): string|Response
    {
        $idParam = $this->request->param('ids');
        if ($idParam === null || $idParam === '') $idParam = $this->request->param('id');
        $id = (int) $idParam;
        if ($id <= 0) return $this->error('参数错误');

        $tenantId = $this->getTenantId();
        $row = UserProcessCapacityModel::where('id', $id)->find();
        if (!$row) return $this->error('记录不存在');
        if ($tenantId > 0 && (int) $row->tenant_id !== $tenantId) return $this->error('无权限');

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) return $this->error('参数不能为空');
            $cap = isset($params['capacity_per_day']) ? (int) $params['capacity_per_day'] : (int) $row->capacity_per_day;
            if ($cap <= 0) return $this->error('日产能必须大于0');
            $data = [
                'capacity_per_day' => $cap,
                'status' => isset($params['status']) ? ((int) $params['status'] ? 1 : 0) : (int) $row->status,
                'update_time' => time(),
            ];
            try {
                $row->save($data);
                return $this->success('保存成功');
            } catch (\Throwable $e) {
                return $this->error('保存失败：' . $e->getMessage());
            }
        }

        $users = UserModel::where('tenant_id', (int) $row->tenant_id)->where('status', 1)->column('nickname', 'id');
        $processes = ProcessModel::where('tenant_id', (int) $row->tenant_id)->where('status', 1)->column('name', 'id');
        View::assign('row', $row);
        View::assign('users', $users ?: []);
        View::assign('processes', $processes ?: []);
        View::assign('tenant_id', (int) $row->tenant_id);
        View::assign('title', '编辑员工产能');
        return $this->fetchWithLayout('mes/user_process_capacity/edit');
    }

    public function del(): Response
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $ids = $this->request->post('ids');
        if (empty($ids)) return $this->error('请选择要删除的记录');
        $ids = is_array($ids) ? $ids : explode(',', (string) $ids);
        $tenantId = $this->getTenantId();
        $query = UserProcessCapacityModel::whereIn('id', $ids);
        if ($tenantId > 0) $query->where('tenant_id', $tenantId);
        $count = (int) $query->count();
        $query->delete();
        return $this->success('删除成功', ['count' => $count]);
    }
}

