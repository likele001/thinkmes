<?php
declare(strict_types=1);

namespace app\admin\controller\crm;

use app\admin\controller\Backend;
use app\admin\model\crm\ContractModel;
use app\admin\model\crm\CustomerModel;
use app\admin\model\crm\OpportunityModel;
use think\facade\View;
use think\Response;

/**
 * CRM 合同管理
 */
class Contract extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '合同管理');
            return $this->fetchWithLayout('crm/contract/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $customerId = (int) $this->request->get('customer_id', 0);
        $status = trim((string) $this->request->get('status'));

        $tenantId = $this->getTenantId();
        $query = ContractModel::order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        }
        if ($customerId > 0) {
            $query->where('customer_id', $customerId);
        }
        if ($status !== '') {
            $query->where('status', $status);
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
            $contractNo = trim((string) ($params['contract_no'] ?? ''));
            $customerId = (int) ($params['customer_id'] ?? 0);

            if ($contractNo === '') {
                return $this->error('请输入合同编号');
            }
            if ($customerId <= 0) {
                return $this->error('请选择客户');
            }

            // 检查合同编号是否重复
            $exists = ContractModel::where('tenant_id', $tenantId)
                ->where('contract_no', $contractNo)
                ->find();
            if ($exists) {
                return $this->error('合同编号已存在');
            }

            $params['tenant_id'] = $tenantId;
            if (!isset($params['status']) || $params['status'] === '') {
                $params['status'] = 'draft';
            }
            if (isset($params['sign_date']) && $params['sign_date'] !== '') {
                $params['sign_date'] = strtotime($params['sign_date']);
            }

            try {
                $contract = ContractModel::create($params);
                return $this->success('添加成功', ['id' => $contract->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        $customerId = (int) $this->request->get('customer_id', 0);
        $opportunityId = (int) $this->request->get('opportunity_id', 0);
        $tenantId = $this->getTenantId();
        $customers = CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->select()->toArray();
        $opportunities = [];
        if ($customerId > 0) {
            $opportunities = OpportunityModel::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->where('stage', 'in', ['won', 'negotiate'])
                ->select()->toArray();
        }

        View::assign('customers', $customers);
        View::assign('opportunities', $opportunities);
        View::assign('customer_id', $customerId);
        View::assign('opportunity_id', $opportunityId);
        View::assign('title', '添加合同');
        return $this->fetchWithLayout('crm/contract/add');
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
        $row = ContractModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('合同不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $contractNo = trim((string) ($params['contract_no'] ?? ''));
            if ($contractNo === '') {
                return $this->error('请输入合同编号');
            }

            // 检查合同编号是否重复
            $exists = ContractModel::where('tenant_id', $tenantId)
                ->where('contract_no', $contractNo)
                ->where('id', '<>', $row->id)
                ->find();
            if ($exists) {
                return $this->error('合同编号已存在');
            }

            if (isset($params['sign_date']) && $params['sign_date'] !== '') {
                $params['sign_date'] = strtotime($params['sign_date']);
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
        $opportunities = [];
        if ($row->customer_id > 0) {
            $opportunities = OpportunityModel::where('tenant_id', $tenantId)
                ->where('customer_id', $row->customer_id)
                ->select()->toArray();
        }

        View::assign('customers', $customers);
        View::assign('opportunities', $opportunities);
        View::assign('row', $row);
        View::assign('title', '编辑合同');
        return $this->fetchWithLayout('crm/contract/edit');
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
                $contract = ContractModel::where('tenant_id', $tenantId)->find($id);
                if ($contract) {
                    $contract->delete();
                }
            }
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
