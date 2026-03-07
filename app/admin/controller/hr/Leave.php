<?php
declare(strict_types=1);

namespace app\admin\controller\hr;

use app\admin\controller\Backend;
use app\admin\model\hr\HrLeaveModel;
use app\admin\model\hr\HrEmployeeModel;
use think\facade\View;
use think\Response;

class Leave extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() || $limitParam === null || $limitParam === '') {
            View::assign('title', '请假管理');
            return $this->fetchWithLayout('hr/leave/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $tenantId = $this->getTenantId();
        $query = HrLeaveModel::with(['employee'])->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tp = (int) $this->request->get('tenant_id', 0);
            if ($tp > 0) {
                $query->where('tenant_id', $tp);
            }
        }
        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $employeeId = $this->request->get('employee_id');
        if ($employeeId !== '' && $employeeId !== null) {
            $query->where('employee_id', (int) $employeeId);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['employee_name'] = $item['employee']['name'] ?? '-';
            $item['type_text'] = (HrLeaveModel::getTypeList())[$item['type']] ?? '';
            $item['status_text'] = (HrLeaveModel::getStatusList())[$item['status']] ?? '';
        }
        unset($item);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['employee_id']) || empty($params['start_date']) || empty($params['end_date'])) {
                return $this->error('请填写员工与日期');
            }
            $params['tenant_id'] = $this->getTenantId();
            $params['create_time'] = time();
            $params['update_time'] = time();
            $start = strtotime($params['start_date']);
            $end = strtotime($params['end_date']);
            $params['days'] = round(($end - $start) / 86400, 2) + 1;
            if (!isset($params['days']) || $params['days'] <= 0) {
                $params['days'] = 1;
            }
            try {
                $row = HrLeaveModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        $tenantId = $this->getTenantId();
        View::assign('employeeList', HrEmployeeModel::where('tenant_id', $tenantId)->where('status', 1)->order('no')->select());
        View::assign('typeList', HrLeaveModel::getTypeList());
        View::assign('statusList', HrLeaveModel::getStatusList());
        View::assign('title', '添加请假');
        return $this->fetchWithLayout('hr/leave/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = HrLeaveModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            $params['update_time'] = time();
            if (!empty($params['start_date']) && !empty($params['end_date'])) {
                $params['days'] = round((strtotime($params['end_date']) - strtotime($params['start_date'])) / 86400, 2) + 1;
            }
            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }
        View::assign('employeeList', HrEmployeeModel::where('tenant_id', $tenantId)->where('status', 1)->order('no')->select());
        View::assign('typeList', HrLeaveModel::getTypeList());
        View::assign('statusList', HrLeaveModel::getStatusList());
        View::assign('row', $row);
        View::assign('title', '编辑请假');
        return $this->fetchWithLayout('hr/leave/edit');
    }

    public function del(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : explode(',', $ids);
        $count = 0;
        foreach ($idsArr as $id) {
            $row = HrLeaveModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }

    public function approve(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $id = (int) $this->request->post('id');
        $status = (int) $this->request->post('status');
        if (!in_array($status, [1, 2], true)) {
            return $this->error('状态无效');
        }
        $tenantId = $this->getTenantId();
        $row = HrLeaveModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $row->status = $status;
        $row->update_time = time();
        $row->save();
        return $this->success($status === 1 ? '已通过' : '已拒绝');
    }
}
