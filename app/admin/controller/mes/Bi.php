<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\ProductionPlanModel;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\AllocationModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * BI报表和数据大屏
 */
class Bi extends Backend
{
    /**
     * 数据大屏 - 生产看板
     */
    public function dashboard(): string
    {
        View::assign('title', '生产数据大屏');
        return $this->fetchWithLayout('mes/bi/dashboard');
    }

    /**
     * 获取大屏数据
     */
    public function getDashboardData(): Response
    {
        $tenantId = $this->getTenantId();
        $today = date('Y-m-d');
        $todayStart = strtotime($today . ' 00:00:00');
        $todayEnd = strtotime($today . ' 23:59:59');
        
        // 今日报工统计
        $todayReports = ReportModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->where('create_time', 'between', [$todayStart, $todayEnd])
            ->field('SUM(quantity) as total_quantity, SUM(wage) as total_wage, COUNT(*) as report_count')
            ->find();
        
        // 订单统计
        $orderStats = OrderModel::where('tenant_id', $tenantId)
            ->field('status, COUNT(*) as count')
            ->group('status')
            ->select();
        
        $orderData = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
        foreach ($orderStats as $stat) {
            $orderData[$stat->status] = $stat->count;
        }
        
        // 生产计划统计
        $planStats = ProductionPlanModel::where('tenant_id', $tenantId)
            ->field('status, COUNT(*) as count')
            ->group('status')
            ->select();
        
        $planData = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
        foreach ($planStats as $stat) {
            $planData[$stat->status] = $stat->count;
        }
        
        // 进行中的分配
        $activeAllocations = AllocationModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->whereColumn('completed_quantity', '<', 'quantity')
            ->count();
        
        // 待审核的报工
        $pendingReports = ReportModel::where('tenant_id', $tenantId)
            ->where('status', 0)
            ->count();
        
        // 最近7天的报工趋势
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dateStart = strtotime($date . ' 00:00:00');
            $dateEnd = strtotime($date . ' 23:59:59');
            
            $dayReport = ReportModel::where('tenant_id', $tenantId)
                ->where('status', 1)
                ->where('create_time', 'between', [$dateStart, $dateEnd])
                ->field('SUM(quantity) as quantity, SUM(wage) as wage, COUNT(*) as count')
                ->find();
            
            $trendData[] = [
                'date' => $date,
                'quantity' => (float) ($dayReport->quantity ?? 0),
                'wage' => (float) ($dayReport->wage ?? 0),
                'count' => (int) ($dayReport->count ?? 0)
            ];
        }
        
        $data = [
            'today' => [
                'quantity' => (float) ($todayReports->total_quantity ?? 0),
                'wage' => (float) ($todayReports->total_wage ?? 0),
                'report_count' => (int) ($todayReports->report_count ?? 0)
            ],
            'orders' => $orderData,
            'plans' => $planData,
            'active_allocations' => $activeAllocations,
            'pending_reports' => $pendingReports,
            'trend' => $trendData
        ];
        
        return $this->success('', $data);
    }

    /**
     * 生产效率报表
     */
    public function productionEfficiency(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '生产效率报表');
            return $this->fetchWithLayout('mes/bi/production_efficiency');
        }
        
        $tenantId = $this->getTenantId();
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-d'));
        
        $startTime = strtotime($startDate . ' 00:00:00');
        $endTime = strtotime($endDate . ' 23:59:59');
        
        // 按日期统计生产效率
        $query = ReportModel::alias('r')
            ->join('mes_allocation a', 'r.allocation_id = a.id')
            ->where('r.tenant_id', $tenantId)
            ->where('r.status', 1)
            ->where('r.create_time', 'between', [$startTime, $endTime])
            ->field('DATE(FROM_UNIXTIME(r.create_time)) as stat_date,
                     COUNT(DISTINCT r.user_id) as worker_count,
                     SUM(r.quantity) as total_quantity,
                     SUM(r.work_hours) as total_hours,
                     SUM(r.wage) as total_wage,
                     COUNT(*) as report_count')
            ->group('stat_date')
            ->order('stat_date', 'desc');
        
        $total = $query->count();
        $list = $query->select()->toArray();
        
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 质量分析报表
     */
    public function qualityAnalysis(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '质量分析报表');
            return $this->fetchWithLayout('mes/bi/quality_analysis');
        }
        
        $tenantId = $this->getTenantId();
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-d'));
        
        $startTime = strtotime($startDate . ' 00:00:00');
        $endTime = strtotime($endDate . ' 23:59:59');
        
        // 按日期统计质量数据
        $query = ReportModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->where('create_time', 'between', [$startTime, $endTime])
            ->field('DATE(FROM_UNIXTIME(create_time)) as stat_date,
                     COUNT(*) as total_count,
                     SUM(CASE WHEN quality_status = 1 THEN 1 ELSE 0 END) as qualified_count,
                     SUM(CASE WHEN quality_status = 2 THEN 1 ELSE 0 END) as unqualified_count')
            ->group('stat_date')
            ->order('stat_date', 'desc');
        
        $total = $query->count();
        $list = $query->select()->toArray();
        
        // 计算合格率
        foreach ($list as &$row) {
            $row['qualified_rate'] = $row['total_count'] > 0 
                ? round(($row['qualified_count'] / $row['total_count']) * 100, 2) 
                : 0;
        }
        
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 成本分析报表
     */
    public function costAnalysis(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '成本分析报表');
            return $this->fetchWithLayout('mes/bi/cost_analysis');
        }
        
        $tenantId = $this->getTenantId();
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-d'));
        
        $startTime = strtotime($startDate . ' 00:00:00');
        $endTime = strtotime($endDate . ' 23:59:59');
        
        // 按订单统计成本
        $query = OrderModel::alias('o')
            ->leftJoin('mes_order_material om', 'o.id = om.order_id')
            ->where('o.tenant_id', $tenantId)
            ->where('o.create_time', 'between', [$startTime, $endTime])
            ->field('o.id, o.order_no, o.order_name,
                     SUM(om.estimated_amount) as material_cost,
                     (SELECT SUM(wage) FROM mes_wage WHERE order_id = o.id) as labor_cost')
            ->group('o.id')
            ->order('o.id', 'desc');
        
        $total = $query->count();
        $list = $query->select()->toArray();
        
        // 计算总成本
        foreach ($list as &$row) {
            $row['total_cost'] = (float) ($row['material_cost'] ?? 0) + (float) ($row['labor_cost'] ?? 0);
        }
        
        return $this->success('', ['total' => $total, 'list' => $list]);
    }
}
