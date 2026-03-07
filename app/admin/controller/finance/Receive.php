<?php
declare(strict_types=1);

namespace app\admin\controller\finance;

use app\admin\controller\Backend;
use app\admin\model\finance\FinanceReceiveModel;
use app\admin\model\finance\FinanceReceivableModel;
use think\facade\View;
use think\Response;

class Receive extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() || $limitParam === null || $limitParam === '') {
            View::assign('title', '收款登记');
            return $this->fetchWithLayout('finance/receive/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $tenantId = $this->getTenantId();
        $query = FinanceReceiveModel::with(['receivable.customer'])->order('id', 'desc');
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
            $item['receivable_title'] = $item['receivable']['title'] ?? '-';
            $item['customer_name'] = $item['receivable']['customer']['name'] ?? '-';
        }
        unset($item);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $receivableId = (int) $this->request->post('receivable_id');
            $amount = (float) $this->request->post('amount');
            $payTime = str_replace('T', ' ', trim((string) $this->request->post('pay_time', '')));
            $remark = trim((string) $this->request->post('remark', ''));
            if ($receivableId <= 0 || $amount <= 0) {
                return $this->error('请选择应收单并填写收款金额');
            }
            $tenantId = $this->getTenantId();
            $receivable = FinanceReceivableModel::where('tenant_id', $tenantId)->find($receivableId);
            if (!$receivable) {
                return $this->error('应收单不存在');
            }
            $received = (float) $receivable->received + $amount;
            $receivable->received = $received;
            $receivable->status = $received >= (float) $receivable->amount ? 1 : 0;
            $receivable->update_time = time();
            $receivable->save();

            FinanceReceiveModel::create([
                'tenant_id'      => $tenantId,
                'receivable_id'  => $receivableId,
                'amount'         => $amount,
                'pay_time'       => $payTime ?: date('Y-m-d H:i:s'),
                'remark'         => $remark,
                'create_time'    => time(),
            ]);
            return $this->success('收款登记成功');
        }
        $tenantId = $this->getTenantId();
        $receivableList = FinanceReceivableModel::with('customer')
            ->where('tenant_id', $tenantId)
            ->where('status', 0)
            ->whereColumn('received', '<', 'amount')
            ->order('id', 'desc')
            ->select();
        View::assign('receivableList', $receivableList);
        View::assign('title', '收款登记');
        return $this->fetchWithLayout('finance/receive/add');
    }
}
