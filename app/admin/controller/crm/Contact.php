<?php
declare(strict_types=1);

namespace app\admin\controller\crm;

use app\admin\controller\Backend;
use app\admin\model\crm\ContactModel;
use app\admin\model\crm\CustomerModel;
use think\facade\View;
use think\Response;

/**
 * CRM 联系人管理
 */
class Contact extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $customerId = (int) $this->request->get('customer_id', 0);
            $customer = null;
            if ($customerId > 0) {
                $customer = CustomerModel::find($customerId);
            }
            View::assign('customer', $customer);
            View::assign('title', '联系人管理');
            return $this->fetchWithLayout('crm/contact/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $customerId = (int) $this->request->get('customer_id', 0);
        $name = trim((string) $this->request->get('name'));

        $tenantId = $this->getTenantId();
        $query = ContactModel::order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        }
        if ($customerId > 0) {
            $query->where('customer_id', $customerId);
        }
        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }

        $total = $query->count();
        $rows = $query->page($page, $limit)->select();

        // 关联客户名称
        $list = [];
        foreach ($rows as $row) {
            $arr = $row->toArray();
            if ($row->customer_id > 0) {
                $customer = CustomerModel::find($row->customer_id);
                $arr['customer_name'] = $customer ? $customer->name : '';
            }
            $list[] = $arr;
        }

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
            $customerId = (int) ($params['customer_id'] ?? 0);

            if ($name === '') {
                return $this->error('请输入联系人姓名');
            }
            if ($customerId <= 0) {
                return $this->error('请选择客户');
            }

            // 如果设为主联系人，先取消该客户的其他主联系人
            if (isset($params['is_main']) && $params['is_main'] == 1) {
                ContactModel::where('tenant_id', $tenantId)
                    ->where('customer_id', $customerId)
                    ->where('is_main', 1)
                    ->update(['is_main' => 0]);
            }

            $params['tenant_id'] = $tenantId;
            if (!isset($params['is_main'])) {
                $params['is_main'] = 0;
            }

            try {
                $contact = ContactModel::create($params);
                return $this->success('添加成功', ['id' => $contact->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        $customerId = (int) $this->request->get('customer_id', 0);
        $customers = [];
        if ($customerId > 0) {
            $customer = CustomerModel::find($customerId);
            if ($customer) {
                $customers[] = ['id' => $customer->id, 'name' => $customer->name];
            }
        } else {
            $tenantId = $this->getTenantId();
            $customers = CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->select()->toArray();
        }

        View::assign('customers', $customers);
        View::assign('customer_id', $customerId);
        View::assign('title', '添加联系人');
        return $this->fetchWithLayout('crm/contact/add');
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
        $row = ContactModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('联系人不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $name = trim((string) ($params['name'] ?? ''));
            if ($name === '') {
                return $this->error('请输入联系人姓名');
            }

            // 如果设为主联系人，先取消该客户的其他主联系人
            if (isset($params['is_main']) && $params['is_main'] == 1 && $row->customer_id > 0) {
                ContactModel::where('tenant_id', $tenantId)
                    ->where('customer_id', $row->customer_id)
                    ->where('is_main', 1)
                    ->where('id', '<>', $row->id)
                    ->update(['is_main' => 0]);
            }

            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        $customers = CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->select()->toArray();
        View::assign('customers', $customers);
        View::assign('row', $row);
        View::assign('title', '编辑联系人');
        return $this->fetchWithLayout('crm/contact/edit');
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
                $contact = ContactModel::where('tenant_id', $tenantId)->find($id);
                if ($contact) {
                    $contact->delete();
                }
            }
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
