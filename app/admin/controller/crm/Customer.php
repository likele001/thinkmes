<?php
declare(strict_types=1);

namespace app\admin\controller\crm;

use app\admin\controller\Backend;
use app\admin\model\crm\CustomerModel;
use app\admin\model\crm\CustomerTagModel;
use app\admin\model\crm\CustomerTagRelModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * CRM 客户管理
 */
class Customer extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->getTenantId();
            View::assign('tagList', CustomerTagModel::where('tenant_id', $tenantId)->order('sort')->select());
            View::assign('title', '客户管理');
            return $this->fetchWithLayout('crm/customer/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $name = trim((string) $this->request->get('name'));
        $status = $this->request->get('status');
        $level = $this->request->get('level');

        $tenantId = $this->getTenantId();
        $query = CustomerModel::order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }

        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        if ($level !== '' && $level !== null) {
            $query->where('level', (int) $level);
        }
        $tagId = $this->request->get('tag_id');
        if ($tagId !== '' && $tagId !== null && (int) $tagId > 0) {
            $customerIds = CustomerTagRelModel::where('tag_id', (int) $tagId)->column('customer_id');
            $query->whereIn('id', $customerIds ?: [0]);
        }

        $total = $query->count();
        $rows = $query->page($page, $limit)->select();
        $list = $rows->toArray();
        $customerIds = array_column($list, 'id');
        $tagRels = [];
        if (!empty($customerIds)) {
            $rels = CustomerTagRelModel::with('tag')->whereIn('customer_id', $customerIds)->select();
            foreach ($rels as $r) {
                $cid = $r->customer_id;
                if (!isset($tagRels[$cid])) {
                    $tagRels[$cid] = [];
                }
                $tagRels[$cid][] = $r->tag->name ?? '';
            }
        }
        foreach ($list as &$item) {
            $item['tag_names'] = isset($tagRels[$item['id']]) ? implode(', ', $tagRels[$item['id']]) : '';
        }
        unset($item);

        return $this->success('', ['total' => $total, 'list' => $list]);
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
                return $this->error('请输入客户名称');
            }

            $params['tenant_id'] = $tenantId;
            if (!isset($params['status']) || $params['status'] === '') {
                $params['status'] = 1;
            }
            if (!isset($params['level']) || $params['level'] === '') {
                $params['level'] = 0;
            }
            $tagIds = $this->request->post('tag_ids/a');
            unset($params['tag_ids']);

            try {
                $customer = CustomerModel::create($params);
                if (!empty($tagIds) && is_array($tagIds)) {
                    foreach ($tagIds as $tid) {
                        $tid = (int) $tid;
                        if ($tid > 0) {
                            CustomerTagRelModel::create(['customer_id' => $customer->id, 'tag_id' => $tid]);
                        }
                    }
                }
                return $this->success('添加成功', ['id' => $customer->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        View::assign('tagList', CustomerTagModel::where('tenant_id', $this->getTenantId())->order('sort')->select());
        View::assign('title', '添加客户');
        return $this->fetchWithLayout('crm/customer/add');
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
        $row = CustomerModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('客户不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $name = trim((string) ($params['name'] ?? ''));
            if ($name === '') {
                return $this->error('请输入客户名称');
            }

            $tagIds = $this->request->post('tag_ids/a');
            unset($params['tag_ids']);

            try {
                $row->save($params);
                CustomerTagRelModel::where('customer_id', $row->id)->delete();
                if (!empty($tagIds) && is_array($tagIds)) {
                    foreach ($tagIds as $tid) {
                        $tid = (int) $tid;
                        if ($tid > 0) {
                            CustomerTagRelModel::create(['customer_id' => $row->id, 'tag_id' => $tid]);
                        }
                    }
                }
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        $tagIds = CustomerTagRelModel::where('customer_id', $row->id)->column('tag_id');
        View::assign('tagIds', $tagIds ?: []);
        View::assign('tagList', CustomerTagModel::where('tenant_id', $this->getTenantId())->order('sort')->select());
        View::assign('row', $row);
        View::assign('title', '编辑客户');
        return $this->fetchWithLayout('crm/customer/edit');
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
                $customer = CustomerModel::where('tenant_id', $tenantId)->find($id);
                if ($customer) {
                    $customer->delete();
                }
            }
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
