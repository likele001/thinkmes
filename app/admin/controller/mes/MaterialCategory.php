<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\MaterialCategoryModel;
use think\facade\View;
use think\Response;

/**
 * 物料分类（与 report scanwork MaterialCategory 对应）
 */
class MaterialCategory extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '物料分类');
            return $this->fetchWithLayout('mes/material_category/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = MaterialCategoryModel::order('sort', 'asc')->order('id', 'asc');
        if ($tenantId > 0) {
            $query->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->whereOr('tenant_id', 0);
            });
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where(function ($q) use ($tenantParam) {
                    $q->where('tenant_id', $tenantParam)->whereOr('tenant_id', 0);
                });
            }
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['name'])) {
                return $this->error('分类名称不能为空');
            }
            $params['tenant_id'] = $this->getTenantId();
            $params['code'] = $params['code'] ?? ('CAT' . date('YmdHis'));
            $params['sort'] = (int) ($params['sort'] ?? 0);
            $params['status'] = isset($params['status']) ? (int) $params['status'] : 1;
            $params['create_time'] = time();
            $params['update_time'] = time();
            try {
                MaterialCategoryModel::create($params);
                return $this->success('添加成功');
            } catch (\Throwable $e) {
                return $this->error('添加失败');
            }
        }
        View::assign('title', '添加物料分类');
        return $this->fetchWithLayout('mes/material_category/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = MaterialCategoryModel::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->whereOr('tenant_id', 0);
        })->find($ids);
        if (!$row) {
            return $this->error('分类不存在');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            $params['update_time'] = time();
            try {
                $row->save($params);
                return $this->success('编辑成功');
            } catch (\Throwable $e) {
                return $this->error('编辑失败');
            }
        }
        View::assign('row', $row);
        View::assign('ids', $ids);
        View::assign('title', '编辑物料分类');
        return $this->fetchWithLayout('mes/material_category/edit');
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
        foreach ($idsArr as $id) {
            $row = MaterialCategoryModel::where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->whereOr('tenant_id', 0);
            })->find($id);
            if ($row) {
                $row->delete();
            }
        }
        return $this->success('删除成功');
    }
}
