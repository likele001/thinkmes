<?php
declare(strict_types=1);

namespace app\admin\controller\hr;

use app\admin\controller\Backend;
use app\admin\model\hr\HrEmployeeModel;
use app\admin\model\hr\HrDepartmentModel;
use app\admin\model\hr\HrPositionModel;
use think\facade\View;
use think\Response;

/**
 * HR 员工管理
 * 提供员工档案的增删改查，含部门/岗位关联
 */
class Employee extends Backend
{
    /**
     * 员工列表（AJAX 返回 JSON，非 AJAX 返回视图）
     */
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() || $limitParam === null || $limitParam === '') {
            $tenantId = $this->getTenantId();
            View::assign('departmentList', HrDepartmentModel::where('tenant_id', $tenantId)->order('sort')->select());
            View::assign('positionList', HrPositionModel::where('tenant_id', $tenantId)->order('sort')->select());
            View::assign('title', '员工档案');
            return $this->fetchWithLayout('hr/employee/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $tenantId = $this->getTenantId();
        $query = HrEmployeeModel::with(['department', 'position'])->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tp = (int) $this->request->get('tenant_id', 0);
            if ($tp > 0) {
                $query->where('tenant_id', $tp);
            }
        }
        $name = trim((string) $this->request->get('name'));
        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }
        $departmentId = $this->request->get('department_id');
        if ($departmentId !== '' && $departmentId !== null) {
            $query->where('department_id', (int) $departmentId);
        }
        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['department_name'] = $item['department']['name'] ?? '-';
            $item['position_name'] = $item['position']['name'] ?? '-';
        }
        unset($item);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 添加员工（GET 返回表单视图，POST 处理保存）
     */
    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['name'])) {
                return $this->error('姓名不能为空');
            }
            $params['tenant_id'] = $this->getTenantId();
            $params['create_time'] = time();
            $params['update_time'] = time();
            try {
                $row = HrEmployeeModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        $tenantId = $this->getTenantId();
        View::assign('departmentList', HrDepartmentModel::where('tenant_id', $tenantId)->order('sort')->select());
        View::assign('positionList', HrPositionModel::where('tenant_id', $tenantId)->order('sort')->select());
        View::assign('statusList', HrEmployeeModel::getStatusList());
        View::assign('title', '添加员工');
        return $this->fetchWithLayout('hr/employee/add');
    }

    /**
     * 编辑员工（GET 返回表单视图，POST 处理保存）
     */
    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = HrEmployeeModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            $params['update_time'] = time();
            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }
        View::assign('departmentList', HrDepartmentModel::where('tenant_id', $tenantId)->order('sort')->select());
        View::assign('positionList', HrPositionModel::where('tenant_id', $tenantId)->order('sort')->select());
        View::assign('statusList', HrEmployeeModel::getStatusList());
        View::assign('row', $row);
        View::assign('title', '编辑员工');
        return $this->fetchWithLayout('hr/employee/edit');
    }

    /**
     * 删除员工（支持批量，ids 用逗号分隔）
     */
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
            $row = HrEmployeeModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }
}
