<?php
declare(strict_types=1);

namespace app\admin\controller\finance;

use app\admin\controller\Backend;
use app\admin\model\finance\FinanceReceivableModel;
use app\admin\model\finance\FinancePayableModel;
use app\admin\model\finance\FinanceReceiveModel;
use app\admin\model\finance\FinancePayModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 对账单：按客户/供应商汇总应收应付及已收已付
 */
class Statement extends Backend
{
    public function index(): string|Response
    {
        $type = $this->request->get('type', 'receivable');
        $tenantId = $this->getTenantId();
        if ($this->request->isAjax()) {
            $startDate = $this->request->get('start_date', date('Y-m-01'));
            $endDate = $this->request->get('end_date', date('Y-m-d'));

            if ($type === 'receivable') {
                $list = FinanceReceivableModel::with('customer')
                    ->where('tenant_id', $tenantId > 0 ? $tenantId : '>', 0)
                    ->where('create_time', '>=', strtotime($startDate . ' 00:00:00'))
                    ->where('create_time', '<=', strtotime($endDate . ' 23:59:59'))
                    ->select()
                    ->toArray();
                $byCustomer = [];
                foreach ($list as $row) {
                    $cid = $row['customer_id'] ?: 0;
                    $name = $row['customer']['name'] ?? '未关联客户';
                    if (!isset($byCustomer[$cid])) {
                        $byCustomer[$cid] = ['name' => $name, 'amount' => 0, 'received' => 0, 'balance' => 0];
                    }
                    $byCustomer[$cid]['amount'] += (float) $row['amount'];
                    $byCustomer[$cid]['received'] += (float) $row['received'];
                }
                foreach ($byCustomer as &$v) {
                    $v['balance'] = $v['amount'] - $v['received'];
                }
                unset($v);
                return $this->success('', ['list' => array_values($byCustomer), 'type' => 'receivable', 'start_date' => $startDate, 'end_date' => $endDate]);
            }

            $list = FinancePayableModel::with('supplier')
                ->where('tenant_id', $tenantId > 0 ? $tenantId : '>', 0)
                ->where('create_time', '>=', strtotime($startDate . ' 00:00:00'))
                ->where('create_time', '<=', strtotime($endDate . ' 23:59:59'))
                ->select()
                ->toArray();
            $bySupplier = [];
            foreach ($list as $row) {
                $sid = $row['supplier_id'] ?: 0;
                $name = $row['supplier']['name'] ?? '未关联供应商';
                if (!isset($bySupplier[$sid])) {
                    $bySupplier[$sid] = ['name' => $name, 'amount' => 0, 'paid' => 0, 'balance' => 0];
                }
                $bySupplier[$sid]['amount'] += (float) $row['amount'];
                $bySupplier[$sid]['paid'] += (float) $row['paid'];
            }
            foreach ($bySupplier as &$v) {
                $v['balance'] = $v['amount'] - $v['paid'];
            }
            unset($v);
            return $this->success('', ['list' => array_values($bySupplier), 'type' => 'payable', 'start_date' => $startDate, 'end_date' => $endDate]);
        }

        View::assign('title', '对账单');
        View::assign('type', $type);
        return $this->fetchWithLayout('finance/statement/index');
    }
}
