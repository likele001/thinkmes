<?php
declare(strict_types=1);

namespace app\admin\controller\finance;

use app\admin\controller\Backend;
use app\admin\model\finance\FinancePayModel;
use app\admin\model\finance\FinancePayableModel;
use think\facade\View;
use think\Response;

class Pay extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() || $limitParam === null || $limitParam === '') {
            View::assign('title', '付款登记');
            return $this->fetchWithLayout('finance/pay/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $tenantId = $this->getTenantId();
        $query = FinancePayModel::with(['payable.supplier'])->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tp = (int) $this->request->get('tenant_id', 0);
            if ($tp > 0) {
                $query->where('tenant_id', $tp);
            }
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['payable_title'] = $item['payable']['title'] ?? '-';
            $item['supplier_name'] = $item['payable']['supplier']['name'] ?? '-';
        }
        unset($item);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $payableId = (int) $this->request->post('payable_id');
            $amount = (float) $this->request->post('amount');
            $payTime = str_replace('T', ' ', trim((string) $this->request->post('pay_time', '')));
            $remark = trim((string) $this->request->post('remark', ''));
            if ($payableId <= 0 || $amount <= 0) {
                return $this->error('请选择应付单并填写付款金额');
            }
            $tenantId = $this->getTenantId();
            $payable = FinancePayableModel::where('tenant_id', $tenantId)->find($payableId);
            if (!$payable) {
                return $this->error('应付单不存在');
            }
            $paid = (float) $payable->paid + $amount;
            $payable->paid = $paid;
            $payable->status = $paid >= (float) $payable->amount ? 1 : 0;
            $payable->update_time = time();
            $payable->save();

            FinancePayModel::create([
                'tenant_id'   => $tenantId,
                'payable_id'  => $payableId,
                'amount'      => $amount,
                'pay_time'    => $payTime ?: date('Y-m-d H:i:s'),
                'remark'      => $remark,
                'create_time' => time(),
            ]);
            return $this->success('付款登记成功');
        }
        $tenantId = $this->getTenantId();
        $payableList = FinancePayableModel::with('supplier')
            ->where('tenant_id', $tenantId)
            ->where('status', 0)
            ->whereColumn('paid', '<', 'amount')
            ->order('id', 'desc')
            ->select();
        View::assign('payableList', $payableList);
        View::assign('title', '付款登记');
        return $this->fetchWithLayout('finance/pay/add');
    }
}
