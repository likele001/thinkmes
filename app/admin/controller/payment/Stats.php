<?php
declare(strict_types=1);

namespace app\admin\controller\payment;

use app\admin\controller\Backend;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 支付统计报表（单用户版，不分租户）
 */
class Stats extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            $todayStart = strtotime(date('Y-m-d'));
            $monthStart = strtotime(date('Y-m-01'));
            $totalOrders = (int) Db::name('payment_order')->count();
            $paidOrders = (int) Db::name('payment_order')->where('status', 1)->count();
            $todayPaid = (int) Db::name('payment_order')->where('status', 1)->where('pay_time', '>=', $todayStart)->count();
            $monthPaid = (int) Db::name('payment_order')->where('status', 1)->where('pay_time', '>=', $monthStart)->count();
            $totalAmount = (float) Db::name('payment_order')->where('status', 1)->sum('amount');
            $todayAmount = (float) Db::name('payment_order')->where('status', 1)->where('pay_time', '>=', $todayStart)->sum('amount');
            $monthAmount = (float) Db::name('payment_order')->where('status', 1)->where('pay_time', '>=', $monthStart)->sum('amount');
            return $this->success('', [
                'total_orders'  => $totalOrders,
                'paid_orders'   => $paidOrders,
                'today_paid'    => $todayPaid,
                'month_paid'    => $monthPaid,
                'total_amount'  => round($totalAmount, 2),
                'today_amount'  => round($todayAmount, 2),
                'month_amount'  => round($monthAmount, 2),
            ]);
        }
        View::assign('title', '统计报表');
        return $this->fetchWithLayout('payment/stats/index');
    }
}
