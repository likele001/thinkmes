<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\admin\model\restaurant\CategoryModel;
use app\admin\model\restaurant\ItemModel;
use app\admin\model\restaurant\StoreModel;
use think\facade\View;
use think\Response;

class Item extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->getTenantId();
            View::assign('storeList', StoreModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
            View::assign('categoryList', CategoryModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'desc')->order('id', 'desc')->select());
            View::assign('title', '菜品管理');
            return $this->fetchWithLayout('restaurant/item/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = ItemModel::with(['store', 'category'])->order('sort', 'desc')->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tp = (int) $this->request->get('tenant_id', 0);
            if ($tp > 0) {
                $query->where('tenant_id', $tp);
            }
        }

        $storeId = (int) $this->request->get('store_id', 0);
        if ($storeId > 0) {
            $query->where('store_id', $storeId);
        }
        $categoryId = (int) $this->request->get('category_id', 0);
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }
        $name = trim((string) $this->request->get('name', ''));
        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['store_name'] = $item['store']['name'] ?? '-';
            $item['category_name'] = $item['category']['name'] ?? '-';
        }
        unset($item);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        $tenantId = $this->getTenantId();
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['name']) || !isset($params['price'])) {
                return $this->error('请填写菜品名称与价格');
            }
            $now = time();
            $params['tenant_id'] = $tenantId;
            $params['store_id'] = isset($params['store_id']) ? (int) $params['store_id'] : 0;
            $params['category_id'] = isset($params['category_id']) ? (int) $params['category_id'] : 0;
            $params['price'] = (float) $params['price'];
            $params['sort'] = isset($params['sort']) ? (int) $params['sort'] : 0;
            $params['sold_out'] = isset($params['sold_out']) ? (int) $params['sold_out'] : 0;
            $params['status'] = isset($params['status']) ? (int) $params['status'] : 1;
            $params['create_time'] = $now;
            $params['update_time'] = $now;
            try {
                $row = ItemModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        View::assign('storeList', StoreModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
        View::assign('categoryList', CategoryModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'desc')->order('id', 'desc')->select());
        View::assign('title', '添加菜品');
        return $this->fetchWithLayout('restaurant/item/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = ItemModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['name']) || !isset($params['price'])) {
                return $this->error('请填写菜品名称与价格');
            }
            $params['store_id'] = isset($params['store_id']) ? (int) $params['store_id'] : (int) $row->store_id;
            $params['category_id'] = isset($params['category_id']) ? (int) $params['category_id'] : (int) $row->category_id;
            $params['price'] = (float) $params['price'];
            $params['sort'] = isset($params['sort']) ? (int) $params['sort'] : (int) $row->sort;
            $params['sold_out'] = isset($params['sold_out']) ? (int) $params['sold_out'] : (int) $row->sold_out;
            $params['status'] = isset($params['status']) ? (int) $params['status'] : (int) $row->status;
            $params['update_time'] = time();
            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }
        View::assign('storeList', StoreModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
        View::assign('categoryList', CategoryModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'desc')->order('id', 'desc')->select());
        View::assign('row', $row);
        View::assign('title', '编辑菜品');
        return $this->fetchWithLayout('restaurant/item/edit');
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
        $idsArr = is_array($ids) ? $ids : explode(',', (string) $ids);
        $count = 0;
        foreach ($idsArr as $id) {
            $row = ItemModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }
}

