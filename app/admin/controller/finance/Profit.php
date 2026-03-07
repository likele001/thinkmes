<?php
declare(strict_types=1);

namespace app\admin\controller\finance;

use app\admin\controller\Backend;
use app\admin\model\crm\SalesOrderModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 订单毛利/利润统计（简化：按销售订单汇总）
 */
class Profit extends Backend
{
    public function index(): string|Response
    {
        if (!$this->request->isAjax()) {
            View::assign('title', '利润统计');
            return $this->fetchWithLayout('finance/profit/index');
        }
        $tenantId = $this->getTenantId();
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-d'));

        $query = SalesOrderModel::where('tenant_id', $tenantId > 0 ? $tenantId : '>', 0);
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        }
        $query->where('create_time', '>=', strtotime($startDate . ' 00:00:00'))
            ->where('create_time', '<=', strtotime($endDate . ' 23:59:59'));

        $orders = $query->order('id', 'desc')->select()->toArray();
        $totalAmount = 0;
        $totalCost = 0;
        foreach ($orders as &$o) {
            $totalAmount += (float) ($o['total_amount'] ?? $o['amount'] ?? 0);
            $totalCost += (float) ($o['cost_amount'] ?? 0); // 若表无此字段则为 0
        }
        unset($o);
        $totalProfit = $totalAmount - $totalCost;
        $list = array_slice($orders, 0, 100);

        return $this->success('', [
            'list'         => $list,
            'total_amount' => round($totalAmount, 2),
            'total_cost'   => round($totalCost, 2),
            'total_profit' => round($totalProfit, 2),
            'start_date'   => $startDate,
            'end_date'     => $endDate,
        ]);
    }
}
