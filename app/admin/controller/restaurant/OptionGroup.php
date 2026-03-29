<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\admin\model\restaurant\ItemModel;
use app\admin\model\restaurant\ItemOptionGroupModel;
use think\facade\View;
use think\Response;

class OptionGroup extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->getTenantId();
            View::assign('itemList', ItemModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
            View::assign('title', '规格/口味分组');
            return $this->fetchWithLayout('restaurant/option_group/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = ItemOptionGroupModel::with(['item'])->order('sort', 'desc')->order('id', 'desc');
        $query->where('tenant_id', $tenantId);

        $itemId = (int) $this->request->get('item_id', 0);
        if ($itemId > 0) {
            $query->where('item_id', $itemId);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['item_name'] = $row['item']['name'] ?? '-';
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        $tenantId = $this->getTenantId();
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['item_id']) || empty($params['name'])) {
                return $this->error('请选择菜品并填写分组名称');
            }
            $now = time();
            $params['tenant_id'] = $tenantId;
            $params['item_id'] = (int) $params['item_id'];
            $params['required'] = isset($params['required']) ? (int) $params['required'] : 0;
            $params['min_select'] = isset($params['min_select']) ? (int) $params['min_select'] : 0;
            $params['max_select'] = isset($params['max_select']) ? (int) $params['max_select'] : 1;
            $params['sort'] = isset($params['sort']) ? (int) $params['sort'] : 0;
            $params['status'] = isset($params['status']) ? (int) $params['status'] : 1;
            $params['create_time'] = $now;
            $params['update_time'] = $now;
            try {
                $row = ItemOptionGroupModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        View::assign('itemList', ItemModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
        View::assign('title', '添加分组');
        return $this->fetchWithLayout('restaurant/option_group/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = ItemOptionGroupModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['item_id']) || empty($params['name'])) {
                return $this->error('请选择菜品并填写分组名称');
            }
            $params['item_id'] = (int) $params['item_id'];
            $params['required'] = isset($params['required']) ? (int) $params['required'] : (int) $row->required;
            $params['min_select'] = isset($params['min_select']) ? (int) $params['min_select'] : (int) $row->min_select;
            $params['max_select'] = isset($params['max_select']) ? (int) $params['max_select'] : (int) $row->max_select;
            $params['sort'] = isset($params['sort']) ? (int) $params['sort'] : (int) $row->sort;
            $params['status'] = isset($params['status']) ? (int) $params['status'] : (int) $row->status;
            $params['update_time'] = time();
            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }
        View::assign('itemList', ItemModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
        View::assign('row', $row);
        View::assign('title', '编辑分组');
        return $this->fetchWithLayout('restaurant/option_group/edit');
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
            $row = ItemOptionGroupModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }
}

