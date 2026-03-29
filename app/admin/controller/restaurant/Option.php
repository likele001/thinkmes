<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\admin\model\restaurant\ItemOptionGroupModel;
use app\admin\model\restaurant\ItemOptionModel;
use think\facade\View;
use think\Response;

class Option extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->getTenantId();
            View::assign('groupList', ItemOptionGroupModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'desc')->order('id', 'desc')->select());
            View::assign('title', '规格/口味选项');
            return $this->fetchWithLayout('restaurant/option/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = ItemOptionModel::with(['group'])->order('sort', 'desc')->order('id', 'desc');
        $query->where('tenant_id', $tenantId);

        $groupId = (int) $this->request->get('group_id', 0);
        if ($groupId > 0) {
            $query->where('group_id', $groupId);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['group_name'] = $row['group']['name'] ?? '-';
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        $tenantId = $this->getTenantId();
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['group_id']) || empty($params['name'])) {
                return $this->error('请选择分组并填写选项名称');
            }
            $now = time();
            $params['tenant_id'] = $tenantId;
            $params['group_id'] = (int) $params['group_id'];
            $params['price_delta'] = isset($params['price_delta']) ? (float) $params['price_delta'] : 0.0;
            $params['sort'] = isset($params['sort']) ? (int) $params['sort'] : 0;
            $params['status'] = isset($params['status']) ? (int) $params['status'] : 1;
            $params['create_time'] = $now;
            $params['update_time'] = $now;
            try {
                $row = ItemOptionModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        View::assign('groupList', ItemOptionGroupModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'desc')->order('id', 'desc')->select());
        View::assign('title', '添加选项');
        return $this->fetchWithLayout('restaurant/option/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = ItemOptionModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['group_id']) || empty($params['name'])) {
                return $this->error('请选择分组并填写选项名称');
            }
            $params['group_id'] = (int) $params['group_id'];
            $params['price_delta'] = isset($params['price_delta']) ? (float) $params['price_delta'] : (float) $row->price_delta;
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
        View::assign('groupList', ItemOptionGroupModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'desc')->order('id', 'desc')->select());
        View::assign('row', $row);
        View::assign('title', '编辑选项');
        return $this->fetchWithLayout('restaurant/option/edit');
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
            $row = ItemOptionModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }
}

