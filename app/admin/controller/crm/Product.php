<?php
declare(strict_types=1);

namespace app\admin\controller\crm;

use app\admin\controller\Backend;
use app\admin\model\crm\ProductModel;
use think\facade\View;
use think\Response;

/**
 * CRM 产品管理
 */
class Product extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '产品管理');
            return $this->fetchWithLayout('crm/product/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $name = trim((string) $this->request->get('name'));
        $status = $this->request->get('status');

        $tenantId = $this->getTenantId();
        $query = ProductModel::order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        }
        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $rows = $query->page($page, $limit)->select();

        return $this->success('', ['total' => $total, 'list' => $rows->toArray()]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $tenantId = $this->getTenantId();
            $name = trim((string) ($params['name'] ?? ''));

            if ($name === '') {
                return $this->error('请输入产品名称');
            }

            $params['tenant_id'] = $tenantId;
            if (!isset($params['status']) || $params['status'] === '') {
                $params['status'] = 1;
            }
            if (!isset($params['unit']) || $params['unit'] === '') {
                $params['unit'] = '个';
            }

            try {
                $product = ProductModel::create($params);
                return $this->success('添加成功', ['id' => $product->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        View::assign('title', '添加产品');
        return $this->fetchWithLayout('crm/product/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            $ids = $this->request->param('id');
        }
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = ProductModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('产品不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $name = trim((string) ($params['name'] ?? ''));
            if ($name === '') {
                return $this->error('请输入产品名称');
            }

            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        View::assign('row', $row);
        View::assign('title', '编辑产品');
        return $this->fetchWithLayout('crm/product/edit');
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
        
        try {
            foreach ($idsArr as $id) {
                $product = ProductModel::where('tenant_id', $tenantId)->find($id);
                if ($product) {
                    $product->delete();
                }
            }
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
