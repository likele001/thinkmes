<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\admin\model\restaurant\CategoryModel;
use app\admin\model\restaurant\ComboItemModel;
use app\admin\model\restaurant\ComboModel;
use app\admin\model\restaurant\ItemModel;
use app\admin\model\restaurant\StoreModel;
use think\facade\View;
use think\Response;

class Combo extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->getTenantId();
            View::assign('storeList', StoreModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
            View::assign('categoryList', CategoryModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'desc')->order('id', 'desc')->select());
            View::assign('title', '套餐管理');
            return $this->fetchWithLayout('restaurant/combo/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = ComboModel::order('sort', 'desc')->order('id', 'desc');
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
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    private function parseItemsSpec(string $spec): array
    {
        $spec = trim($spec);
        if ($spec === '') {
            return [];
        }
        $parts = preg_split('/\s*,\s*/', $spec);
        $items = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '') continue;
            $pair = explode(':', $p, 2);
            $itemId = (int) trim($pair[0]);
            $qty = isset($pair[1]) ? (float) trim($pair[1]) : 1.0;
            if ($itemId <= 0) continue;
            if ($qty <= 0) $qty = 1.0;
            $items[] = ['item_id' => $itemId, 'quantity' => $qty];
        }
        return $items;
    }

    private function comboItemsToSpec(int $tenantId, int $comboId): string
    {
        $rows = ComboItemModel::where('tenant_id', $tenantId)->where('combo_id', $comboId)->order('id', 'asc')->select()->toArray();
        $parts = [];
        foreach ($rows as $r) {
            $id = (int) ($r['item_id'] ?? 0);
            if ($id <= 0) continue;
            $qty = (float) ($r['quantity'] ?? 1);
            $parts[] = $id . ':' . rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
        }
        return implode(',', $parts);
    }

    public function add(): string|Response
    {
        $tenantId = $this->getTenantId();
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['name']) || !isset($params['price'])) {
                return $this->error('请填写套餐名称与价格');
            }
            $itemsSpec = (string) ($params['items_spec'] ?? '');
            unset($params['items_spec']);
            $items = $this->parseItemsSpec($itemsSpec);
            if (empty($items)) {
                return $this->error('请填写套餐包含的菜品');
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
                $row = ComboModel::create($params);
                foreach ($items as $it) {
                    ComboItemModel::create([
                        'tenant_id' => $tenantId,
                        'combo_id' => (int) $row->id,
                        'item_id' => (int) $it['item_id'],
                        'quantity' => (float) $it['quantity'],
                        'create_time' => $now,
                        'update_time' => $now,
                    ]);
                }
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        View::assign('storeList', StoreModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
        View::assign('categoryList', CategoryModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'desc')->order('id', 'desc')->select());
        View::assign('title', '添加套餐');
        return $this->fetchWithLayout('restaurant/combo/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = ComboModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['name']) || !isset($params['price'])) {
                return $this->error('请填写套餐名称与价格');
            }
            $itemsSpec = (string) ($params['items_spec'] ?? '');
            unset($params['items_spec']);
            $items = $this->parseItemsSpec($itemsSpec);
            if (empty($items)) {
                return $this->error('请填写套餐包含的菜品');
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
                ComboItemModel::where('tenant_id', $tenantId)->where('combo_id', (int) $row->id)->delete();
                $now = time();
                foreach ($items as $it) {
                    ComboItemModel::create([
                        'tenant_id' => $tenantId,
                        'combo_id' => (int) $row->id,
                        'item_id' => (int) $it['item_id'],
                        'quantity' => (float) $it['quantity'],
                        'create_time' => $now,
                        'update_time' => $now,
                    ]);
                }
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }
        View::assign('storeList', StoreModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
        View::assign('categoryList', CategoryModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'desc')->order('id', 'desc')->select());
        View::assign('items_spec', $this->comboItemsToSpec($tenantId, (int) $row->id));
        View::assign('row', $row);
        View::assign('title', '编辑套餐');
        return $this->fetchWithLayout('restaurant/combo/edit');
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
            $row = ComboModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                ComboItemModel::where('tenant_id', $tenantId)->where('combo_id', (int) $row->id)->delete();
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }
}

