<?php
declare(strict_types=1);

namespace app\admin\controller\crm;

use app\admin\controller\Backend;
use app\admin\model\crm\OpportunityModel;
use app\admin\model\crm\CustomerModel;
use app\admin\model\crm\ContactModel;
use think\facade\Session;
use think\facade\View;
use think\Response;

/**
 * CRM 商机管理
 */
class Opportunity extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '商机管理');
            return $this->fetchWithLayout('crm/opportunity/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $customerId = (int) $this->request->get('customer_id', 0);
        $stage = trim((string) $this->request->get('stage'));

        $tenantId = $this->getTenantId();
        $query = OpportunityModel::order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        }
        if ($customerId > 0) {
            $query->where('customer_id', $customerId);
        }
        if ($stage !== '') {
            $query->where('stage', $stage);
        }

        $total = $query->count();
        $rows = $query->page($page, $limit)->select();

        $list = [];
        foreach ($rows as $row) {
            $arr = $row->toArray();
            if ($row->customer_id > 0) {
                $customer = CustomerModel::find($row->customer_id);
                $arr['customer_name'] = $customer ? $customer->name : '';
            }
            if ($row->contact_id > 0) {
                $contact = ContactModel::find($row->contact_id);
                $arr['contact_name'] = $contact ? $contact->name : '';
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
                return $this->error('请输入商机名称');
            }
            if ($customerId <= 0) {
                return $this->error('请选择客户');
            }

            $params['tenant_id'] = $tenantId;
            $params['owner_id'] = $params['owner_id'] ?? Session::get('admin_info.id', 0);
            if (!isset($params['stage']) || $params['stage'] === '') {
                $params['stage'] = 'lead';
            }
            if (isset($params['expected_date']) && $params['expected_date'] !== '') {
                $params['expected_date'] = strtotime($params['expected_date']);
            }

            try {
                $opportunity = OpportunityModel::create($params);
                return $this->success('添加成功', ['id' => $opportunity->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        $customerId = (int) $this->request->get('customer_id', 0);
        $tenantId = $this->getTenantId();
        $customers = CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->select()->toArray();
        $contacts = [];
        if ($customerId > 0) {
            $contacts = ContactModel::where('tenant_id', $tenantId)->where('customer_id', $customerId)->select()->toArray();
        }

        View::assign('customers', $customers);
        View::assign('contacts', $contacts);
        View::assign('customer_id', $customerId);
        View::assign('title', '添加商机');
        return $this->fetchWithLayout('crm/opportunity/add');
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
        $row = OpportunityModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('商机不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $name = trim((string) ($params['name'] ?? ''));
            if ($name === '') {
                return $this->error('请输入商机名称');
            }

            if (isset($params['expected_date']) && $params['expected_date'] !== '') {
                $params['expected_date'] = strtotime($params['expected_date']);
            }

            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        $tenantId = $this->getTenantId();
        $customers = CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->select()->toArray();
        $contacts = [];
        if ($row->customer_id > 0) {
            $contacts = ContactModel::where('tenant_id', $tenantId)->where('customer_id', $row->customer_id)->select()->toArray();
        }

        View::assign('customers', $customers);
        View::assign('contacts', $contacts);
        View::assign('row', $row);
        View::assign('title', '编辑商机');
        return $this->fetchWithLayout('crm/opportunity/edit');
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
                $opportunity = OpportunityModel::where('tenant_id', $tenantId)->find($id);
                if ($opportunity) {
                    $opportunity->delete();
                }
            }
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
