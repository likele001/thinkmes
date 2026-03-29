<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use think\facade\Db;
use think\facade\View;
use think\Response;

class Report extends Backend
{
    public function index(): string|Response
    {
        View::assign('title', '餐饮报表');
        return $this->fetchWithLayout('restaurant/report/index');
    }

    public function overview(): Response
    {
        $tenantId = $this->getTenantId();
        $start = strtotime(date('Y-m-d 00:00:00'));
        $end = strtotime(date('Y-m-d 23:59:59'));

        $orderQuery = Db::name('restaurant_order')->where('tenant_id', $tenantId);
        $todayOrders = (clone $orderQuery)->whereBetweenTime('create_time', $start, $end)->count();
        $todayRevenue = (float) (clone $orderQuery)->whereBetweenTime('create_time', $start, $end)->sum('total_amount');
        $unpaid = (clone $orderQuery)->where('status', '<', 4)->count();

        $top = Db::name('restaurant_order_item')
            ->alias('oi')
            ->leftJoin('restaurant_item i', 'i.id = oi.item_id AND i.tenant_id = oi.tenant_id')
            ->where('oi.tenant_id', $tenantId)
            ->whereBetweenTime('oi.create_time', $start, $end)
            ->field('oi.item_id, IFNULL(i.name, \'\') as name, SUM(oi.quantity) as qty, SUM(oi.amount) as amount')
            ->group('oi.item_id')
            ->order('amount desc')
            ->limit(10)
            ->select()
            ->toArray();

        return $this->success('', [
            'today_orders' => $todayOrders,
            'today_revenue' => $todayRevenue,
            'unpaid_orders' => $unpaid,
            'top_items' => $top,
        ]);
    }
}

