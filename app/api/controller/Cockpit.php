<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\MaterialModel;
use think\facade\Db;
use think\Response;

/**
 * 移动端经营看板数据（工人/销售/老板端 H5 或小程序用）
 * 与后台 AI 驾驶舱同口径，按当前用户租户汇总
 */
class Cockpit extends BaseController
{
    protected function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    /**
     * 获取经营看板汇总数据（与 admin ai/cockpit/getCockpitData 同结构）
     */
    public function getData(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }

        $today = date('Y-m-d');
        $todayStart = strtotime($today . ' 00:00:00');
        $todayEnd = strtotime($today . ' 23:59:59');
        $monthStart = strtotime(date('Y-m-01') . ' 00:00:00');
        $monthEnd = strtotime(date('Y-m-t') . ' 23:59:59');
        $tenantWhere = ['tenant_id' => $tenantId];

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

        $mesTodayReports = ReportModel::where('tenant_id', $tenantId)->where('status', 1)->where('create_time', 'between', [$todayStart, $todayEnd])->field('SUM(quantity) as total')->find();
        $mesTodayQuantity = (float) ($mesTodayReports->total ?? 0);
        $mesPendingReports = (int) ReportModel::where('tenant_id', $tenantId)->where('status', 0)->count();

        $stockAlertCount = MaterialModel::where('tenant_id', $tenantId)->whereColumn('stock', '<', 'min_stock')->where('min_stock', '>', 0)->count();

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
            'stock' => ['alert_count' => $stockAlertCount],
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
