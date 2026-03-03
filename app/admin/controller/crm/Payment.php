<?php
declare(strict_types=1);

namespace app\admin\controller\crm;

use app\admin\controller\Backend;
use app\admin\model\crm\PaymentModel;
use app\admin\model\crm\ContractModel;
use app\admin\model\crm\CustomerModel;
use think\facade\View;
use think\Response;

/**
 * CRM 回款管理
 */
class Payment extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '回款管理');
            return $this->fetchWithLayout('crm/payment/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $contractId = (int) $this->request->get('contract_id', 0);

        $tenantId = $this->getTenantId();
        $query = PaymentModel::order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        }
        if ($contractId > 0) {
            $query->where('contract_id', $contractId);
        }

        $total = $query->count();
        $rows = $query->page($page, $limit)->select();

        $list = [];
        foreach ($rows as $row) {
            $arr = $row->toArray();
            if ($row->contract_id > 0) {
                $contract = ContractModel::find($row->contract_id);
                $arr['contract_no'] = $contract ? $contract->contract_no : '';
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
            $contractId = (int) ($params['contract_id'] ?? 0);
            $amount = (float) ($params['amount'] ?? 0);
            $payDate = trim((string) ($params['pay_date'] ?? ''));

            if ($contractId <= 0) {
                return $this->error('请选择合同');
            }
            if ($amount <= 0) {
                return $this->error('请输入回款金额');
            }
            if ($payDate === '') {
                return $this->error('请选择回款日期');
            }

            $params['tenant_id'] = $tenantId;
            $params['pay_date'] = strtotime($payDate);

            try {
                $payment = PaymentModel::create($params);
                return $this->success('添加成功', ['id' => $payment->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        $contractId = (int) $this->request->get('contract_id', 0);
        $tenantId = $this->getTenantId();
        $contracts = ContractModel::where('tenant_id', $tenantId)->select()->toArray();
        foreach ($contracts as &$c) {
            $c['customer_name'] = '';
            if (!empty($c['customer_id'])) {
                $customer = CustomerModel::find($c['customer_id']);
                $c['customer_name'] = $customer ? $customer->name : '';
            }
        }
        unset($c);

        View::assign('contracts', $contracts);
        View::assign('contract_id', $contractId);
        View::assign('title', '添加回款');
        return $this->fetchWithLayout('crm/payment/add');
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
        $row = PaymentModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('回款记录不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $amount = (float) ($params['amount'] ?? 0);
            if ($amount <= 0) {
                return $this->error('请输入回款金额');
            }

            if (isset($params['pay_date']) && $params['pay_date'] !== '') {
                $params['pay_date'] = strtotime($params['pay_date']);
            }

            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        $tenantId = $this->getTenantId();
        $contracts = ContractModel::where('tenant_id', $tenantId)->select()->toArray();
        foreach ($contracts as &$c) {
            $c['customer_name'] = '';
            if (!empty($c['customer_id'])) {
                $customer = CustomerModel::find($c['customer_id']);
                $c['customer_name'] = $customer ? $customer->name : '';
            }
        }
        unset($c);

        View::assign('contracts', $contracts);
        View::assign('row', $row);
        View::assign('title', '编辑回款');
        return $this->fetchWithLayout('crm/payment/edit');
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
                $payment = PaymentModel::where('tenant_id', $tenantId)->find($id);
                if ($payment) {
                    $payment->delete();
                }
            }
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
