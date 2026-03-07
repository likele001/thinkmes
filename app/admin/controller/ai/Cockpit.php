<?php
declare(strict_types=1);

namespace app\admin\controller\ai;

use app\admin\controller\Backend;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\MaterialModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * AI 驾驶舱 - 经营数据大屏（聚合 CRM、MES、库存、财务、设备）
 */
class Cockpit extends Backend
{
    public function index(): string
    {
        View::assign('title', '经营数据大屏');
        return $this->fetchWithLayout('ai/cockpit/index');
    }

    /**
     * 获取驾驶舱汇总数据
     */
    public function getCockpitData(): Response
    {
        $tenantId = $this->getTenantId();
        $today = date('Y-m-d');
        $todayStart = strtotime($today . ' 00:00:00');
        $todayEnd = strtotime($today . ' 23:59:59');
        $monthStart = strtotime(date('Y-m-01') . ' 00:00:00');
        $monthEnd = strtotime(date('Y-m-t') . ' 23:59:59');

        $tenantWhere = $tenantId > 0 ? ['tenant_id' => $tenantId] : '1=1';
        $tenantWhereMes = function ($q) use ($tenantId) {
            if ($tenantId > 0) {
                $q->where('tenant_id', $tenantId);
            } else {
                $tp = (int) request()->get('tenant_id', 0);
                if ($tp > 0) {
                    $q->where('tenant_id', $tp);
                }
            }
        };

        // CRM：今日跟进数、商机金额汇总、本月回款
        $crmFollowToday = 0;
        $crmOpportunityAmount = 0;
        $crmPaymentMonth = 0;
        try {
            $crmFollowToday = (int) Db::name('crm_follow')->where($tenantWhere)->where('create_time', 'between', [$todayStart, $todayEnd])->count();
        } catch (\Throwable $e) {}
        try {
            $crmOpportunityAmount = (float) Db::name('crm_opportunity')->where($tenantWhere)->where('status', '<>', 'lost')->sum('amount');
        } catch (\Throwable $e) {}
        try {
            $crmPaymentMonth = (float) Db::name('crm_payment')->where($tenantWhere)->where('pay_time', 'between', [date('Y-m-01'), date('Y-m-t 23:59:59')])->sum('amount');
        } catch (\Throwable $e) {}

        // MES：今日产量、待审核报工
        $mesTodayQuantity = 0;
        $mesPendingReports = 0;
        $mesTodayReports = ReportModel::where($tenantWhereMes)->where('status', 1)->where('create_time', 'between', [$todayStart, $todayEnd])->field('SUM(quantity) as total')->find();
        $mesTodayQuantity = (float) ($mesTodayReports->total ?? 0);
        $mesPendingReports = (int) ReportModel::where($tenantWhereMes)->where('status', 0)->count();

        // 库存：预警条数（stock < min_stock）
        $stockAlertCount = 0;
        $stockAlertCount = MaterialModel::where($tenantWhereMes)->whereColumn('stock', '<', 'min_stock')->where('min_stock', '>', 0)->count();

        // 财务：本月应收/应付/已收/已付
        $financeReceivable = 0;
        $financePayable = 0;
        $financeReceived = 0;
        $financePaid = 0;
        try {
            $financeReceivable = (float) Db::name('finance_receivable')->where($tenantWhere)->where('create_time', 'between', [$monthStart, $monthEnd])->sum('amount');
            $financeReceived = (float) Db::name('finance_receive')->where($tenantWhere)->where('pay_time', 'between', [date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59')])->sum('amount');
        } catch (\Throwable $e) {}
        try {
            $financePayable = (float) Db::name('finance_payable')->where($tenantWhere)->where('create_time', 'between', [$monthStart, $monthEnd])->sum('amount');
            $financePaid = (float) Db::name('finance_pay')->where($tenantWhere)->where('pay_time', 'between', [date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59')])->sum('amount');
        } catch (\Throwable $e) {}

        // 设备：逾期未保养数、近期维修数
        $equipmentOverdueMaintenance = 0;
        $equipmentRecentRepair = 0;
        try {
            $equipmentOverdueMaintenance = (int) Db::name('equipment_maintenance_plan')->where($tenantWhere)->where('next_date', '<', $today)->whereNotNull('next_date')->count();
        } catch (\Throwable $e) {}
        try {
            $last30 = date('Y-m-d', strtotime('-30 days'));
            $equipmentRecentRepair = (int) Db::name('equipment_repair')->where($tenantWhere)->where('repair_date', '>=', $last30)->count();
        } catch (\Throwable $e) {}

        $data = [
            'crm' => [
                'follow_today' => $crmFollowToday,
                'opportunity_amount' => round($crmOpportunityAmount, 2),
                'payment_month' => round($crmPaymentMonth, 2),
            ],
            'mes' => [
                'today_quantity' => $mesTodayQuantity,
                'pending_reports' => $mesPendingReports,
            ],
            'stock' => [
                'alert_count' => $stockAlertCount,
            ],
            'finance' => [
                'receivable_month' => round($financeReceivable, 2),
                'payable_month' => round($financePayable, 2),
                'received_month' => round($financeReceived, 2),
                'paid_month' => round($financePaid, 2),
            ],
            'equipment' => [
                'overdue_maintenance' => $equipmentOverdueMaintenance,
                'recent_repair' => $equipmentRecentRepair,
            ],
        ];

        return $this->success('', $data);
    }
}
