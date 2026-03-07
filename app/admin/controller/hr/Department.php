<?php
declare(strict_types=1);

namespace app\admin\controller\hr;

use app\admin\controller\Backend;
use app\admin\model\hr\HrDepartmentModel;
use think\facade\View;
use think\Response;

class Department extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() || $limitParam === null || $limitParam === '') {
            View::assign('title', '部门管理');
            return $this->fetchWithLayout('hr/department/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $page = max(1, (int) $this->request->get('page', 1));
        $tenantId = $this->getTenantId();
        $query = HrDepartmentModel::with(['parent'])->order('sort', 'asc')->order('id', 'asc');
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
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['parent_name'] = $item['parent']['name'] ?? '-';
        }
        unset($item);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['name'])) {
                return $this->error('部门名称不能为空');
            }
            $params['tenant_id'] = $this->getTenantId();
            $params['create_time'] = time();
            $params['update_time'] = time();
            try {
                $row = HrDepartmentModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        $tenantId = $this->getTenantId();
        $parentList = HrDepartmentModel::where('tenant_id', $tenantId)->order('sort')->select();
        View::assign('parentList', $parentList);
        View::assign('title', '添加部门');
        return $this->fetchWithLayout('hr/department/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = HrDepartmentModel::where('tenant_id', $tenantId)->find($ids);
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
        $parentList = HrDepartmentModel::where('tenant_id', $tenantId)->where('id', '<>', $row->id)->order('sort')->select();
        View::assign('parentList', $parentList);
        View::assign('row', $row);
        View::assign('title', '编辑部门');
        return $this->fetchWithLayout('hr/department/edit');
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
            $row = HrDepartmentModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }
}
