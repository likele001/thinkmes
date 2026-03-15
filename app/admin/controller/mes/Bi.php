<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\ProductionPlanModel;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\WageModel;
use app\common\model\UserModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * BI报表和数据大屏
 */
class Bi extends Backend
{
    /**
     * 数据报表入口（报表中心，非 MES 首页导航）
     */
    public function index(): string
    {
        View::assign('title', '数据报表');
        return $this->fetchWithLayout('mes/bi/index');
    }

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
        $todayReports = ReportModel::where(function($q) use ($tenantId) {
                if ($tenantId > 0) { $q->where('tenant_id', $tenantId); } 
                else { $tp = (int) request()->get('tenant_id', 0); if ($tp > 0) { $q->where('tenant_id', $tp); } }
            })
            ->where('status', 1)
            ->where('create_time', 'between', [$todayStart, $todayEnd])
            ->field('SUM(quantity) as total_quantity, SUM(wage) as total_wage, COUNT(*) as report_count')
            ->find();

        // 今日不良数（已审核报工中不合格数量）
        $todayBad = ReportModel::where(function($q) use ($tenantId) {
                if ($tenantId > 0) { $q->where('tenant_id', $tenantId); }
                else { $tp = (int) request()->get('tenant_id', 0); if ($tp > 0) { $q->where('tenant_id', $tp); } }
            })
            ->where('status', 1)
            ->where('quality_status', 2)
            ->where('create_time', 'between', [$todayStart, $todayEnd])
            ->sum('quantity');
        $todayBad = (int) $todayBad;

        // 进行中订单进度列表（用于大屏左侧）
        $orderT = Db::name('mes_order')->getTable();
        $planT = Db::name('mes_production_plan')->getTable();
        $allocT = Db::name('mes_allocation')->getTable();
        $reportT = Db::name('mes_report')->getTable();
        $orderListQuery = Db::table($orderT . ' o')
            ->leftJoin($planT . ' t', 'o.id = t.order_id')
            ->leftJoin($allocT . ' a', '((a.plan_id = t.id) OR (a.plan_id IS NULL AND a.order_id = o.id))')
            ->leftJoin($reportT . ' r', 'r.allocation_id = a.id AND r.status = 1')
            ->field('o.id, o.order_no, o.order_name as product_name, o.total_quantity as num, SUM(r.quantity) as finish_num, ROUND(IFNULL(SUM(r.quantity)/NULLIF(o.total_quantity,0)*100,0),1) as progress')
            ->group('o.id')
            ->order('o.id', 'desc')
            ->limit(50);
        if ($tenantId > 0) {
            $orderListQuery->where('o.tenant_id', $tenantId);
        } else {
            $tp = (int) request()->get('tenant_id', 0);
            if ($tp > 0) {
                $orderListQuery->where('o.tenant_id', $tp);
            }
        }
        $orderList = $orderListQuery->select()->toArray();
        foreach ($orderList as &$row) {
            $row['finish_num'] = (int) ($row['finish_num'] ?? 0);
            $row['progress'] = round((float) ($row['progress'] ?? 0), 1);
            $row['status_txt'] = $row['finish_num'] <= 0 ? '未开始' : ($row['progress'] >= 100 ? '已完成' : '生产中');
        }

        // 今日各工序产量（大屏右侧）
        $processToday = ReportModel::alias('r')
            ->join('mes_allocation a', 'r.allocation_id = a.id')
            ->join('mes_process p', 'a.process_id = p.id')
            ->where('r.status', 1)
            ->where('r.create_time', 'between', [$todayStart, $todayEnd])
            ->field('p.name as process_name, p.sort, SUM(r.quantity) as quantity')
            ->group('p.id,p.name,p.sort')
            ->order('p.sort', 'asc');
        if ($tenantId > 0) {
            $processToday->where('r.tenant_id', $tenantId);
        } else {
            $tp = (int) request()->get('tenant_id', 0);
            if ($tp > 0) {
                $processToday->where('r.tenant_id', $tp);
            }
        }
        $processTodayList = $processToday->select()->toArray();

        // 订单统计
        $orderStats = OrderModel::where(function($q) use ($tenantId) {
                if ($tenantId > 0) { $q->where('tenant_id', $tenantId); } 
                else { $tp = (int) request()->get('tenant_id', 0); if ($tp > 0) { $q->where('tenant_id', $tp); } }
            })
            ->field('status, COUNT(*) as count')
            ->group('status')
            ->select();
        
        $orderData = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
        foreach ($orderStats as $stat) {
            $orderData[$stat->status] = $stat->count;
        }
        
        // 生产计划统计
        $planStats = ProductionPlanModel::where(function($q) use ($tenantId) {
                if ($tenantId > 0) { $q->where('tenant_id', $tenantId); } 
                else { $tp = (int) request()->get('tenant_id', 0); if ($tp > 0) { $q->where('tenant_id', $tp); } }
            })
            ->field('status, COUNT(*) as count')
            ->group('status')
            ->select();
        
        $planData = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
        foreach ($planStats as $stat) {
            $planData[$stat->status] = $stat->count;
        }
        
        // 进行中的分配
        $activeAllocations = AllocationModel::where(function($q) use ($tenantId) {
                if ($tenantId > 0) { $q->where('tenant_id', $tenantId); } 
                else { $tp = (int) request()->get('tenant_id', 0); if ($tp > 0) { $q->where('tenant_id', $tp); } }
            })
            ->where('status', 1)
            ->whereColumn('completed_quantity', '<', 'quantity')
            ->count();
        
        // 待审核的报工
        $pendingReports = ReportModel::where(function($q) use ($tenantId) {
                if ($tenantId > 0) { $q->where('tenant_id', $tenantId); } 
                else { $tp = (int) request()->get('tenant_id', 0); if ($tp > 0) { $q->where('tenant_id', $tp); } }
            })
            ->where('status', 0)
            ->count();
        
        // 大屏总览 6 数 + 按订单/产品/工序/员工 4 表（与 scanwork 大屏一致）
        $overallStats = $this->getDashboardOverallStats($tenantId);
        $orderStats = $this->getDashboardOrderStats($tenantId);
        $productStats = $this->getDashboardProductStats($tenantId);
        $processStats = $this->getDashboardProcessStats($tenantId);
        $employeeStats = $this->getDashboardEmployeeStats($tenantId);

        // 最近7天的报工趋势
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dateStart = strtotime($date . ' 00:00:00');
            $dateEnd = strtotime($date . ' 23:59:59');
            
            $dayReport = ReportModel::where(function($q) use ($tenantId) {
                    if ($tenantId > 0) { $q->where('tenant_id', $tenantId); } 
                    else { $tp = (int) request()->get('tenant_id', 0); if ($tp > 0) { $q->where('tenant_id', $tp); } }
                })
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
                'report_count' => (int) ($todayReports->report_count ?? 0),
                'bad' => $todayBad,
            ],
            'orders' => $orderData,
            'plans' => $planData,
            'active_allocations' => $activeAllocations,
            'pending_reports' => $pendingReports,
            'order_list' => $orderList,
            'process_today_list' => $processTodayList,
            'exception_list' => $this->buildDashboardExceptions((int) $pendingReports, $todayBad),
            'overall_stats' => $overallStats,
            'order_stats' => $orderStats,
            'product_stats' => $productStats,
            'process_stats' => $processStats,
            'employee_stats' => $employeeStats,
            'trend' => $trendData
        ];

        return $this->success('', $data);
    }

    /**
     * 大屏总览 6 数：总订单数、总计划数、小工单数、总数量、已完成、完成率
     */
    private function getDashboardOverallStats(int $tenantId): array
    {
        $planT = Db::name('mes_production_plan')->getTable();
        $allocT = Db::name('mes_allocation')->getTable();
        $reportT = Db::name('mes_report')->getTable();
        $q = function () use ($tenantId) {
            return $tenantId > 0 ? ['tenant_id' => $tenantId] : [];
        };

        $totalPlans = (int) Db::table($planT)->when($q(), fn ($qry) => $qry->where('tenant_id', $tenantId))->count();
        $totalAllocations = (int) Db::table($allocT)->when($q(), fn ($qry) => $qry->where('tenant_id', $tenantId))->count();
        $totalQuantity = (int) Db::table($allocT)->when($q(), fn ($qry) => $qry->where('tenant_id', $tenantId))->sum('quantity');
        $completedQuantity = (int) ReportModel::when($q(), fn ($qry) => $qry->where('tenant_id', $tenantId))->where('status', 1)->sum('quantity');
        $orderIds = Db::table($planT)->when($q(), fn ($qry) => $qry->where('tenant_id', $tenantId))->group('order_id')->column('order_id');
        $totalOrders = count(array_unique(array_filter($orderIds)));
        // 若没有任何排产计划，则「总订单数」显示当前待生产/生产中的订单数，避免全屏为 0
        if ($totalPlans <= 0) {
            $orderT = Db::name('mes_order')->getTable();
            $totalOrders = (int) Db::table($orderT)->whereIn('status', [0, 1])->when($q(), fn ($qry) => $qry->where('tenant_id', $tenantId))->count();
        }
        $completionRate = $totalQuantity > 0 ? round($completedQuantity / $totalQuantity * 100, 1) : 0;

        return [
            'total_orders' => $totalOrders,
            'total_plans' => $totalPlans,
            'total_allocations' => $totalAllocations,
            'total_quantity' => $totalQuantity,
            'completed_quantity' => $completedQuantity,
            'completion_rate' => $completionRate,
        ];
    }

    /**
     * 大屏按订单统计表
     */
    private function getDashboardOrderStats(int $tenantId): array
    {
        $orderT = Db::name('mes_order')->getTable();
        $planT = Db::name('mes_production_plan')->getTable();
        $allocT = Db::name('mes_allocation')->getTable();
        $reportT = Db::name('mes_report')->getTable();
        $planQ = Db::table($planT . ' t')->join($orderT . ' o', 't.order_id = o.id')
            ->field('o.id as order_id, o.order_name, o.order_no, COUNT(t.id) as total_plans, SUM(t.total_quantity) as total_quantity')
            ->group('t.order_id');
        if ($tenantId > 0) {
            $planQ->where('t.tenant_id', $tenantId);
        }
        $byOrder = $planQ->select()->toArray();
        $completedQ = Db::table($reportT . ' r')
            ->join($allocT . ' a', 'r.allocation_id = a.id')
            ->leftJoin($planT . ' p', 'a.plan_id = p.id')
            ->where('r.status', 1)
            ->field('COALESCE(p.order_id, a.order_id) as order_id, SUM(r.quantity) as completed_quantity')
            ->group('COALESCE(p.order_id, a.order_id)');
        if ($tenantId > 0) {
            $completedQ->where('r.tenant_id', $tenantId);
        }
        $completedByOrder = [];
        foreach ($completedQ->select()->toArray() as $row) {
            $oid = (int) ($row['order_id'] ?? 0);
            if ($oid > 0) {
                $completedByOrder[$oid] = (int) ($row['completed_quantity'] ?? 0);
            }
        }
        $out = [];
        foreach ($byOrder as $r) {
            $oid = (int) $r['order_id'];
            $totalQty = (int) ($r['total_quantity'] ?? 0);
            $completed = $completedByOrder[$oid] ?? 0;
            $out[] = [
                'order_name' => $r['order_name'] ?? $r['order_no'] ?? '',
                'order_no' => $r['order_no'] ?? '',
                'total_plans' => (int) ($r['total_plans'] ?? 0),
                'total_quantity' => $totalQty,
                'completed_quantity' => $completed,
                'completion_rate' => $totalQty > 0 ? round($completed / $totalQty * 100, 1) : 0,
            ];
        }
        return $out;
    }

    /**
     * 大屏按产品统计表（按型号/产品汇总）
     */
    private function getDashboardProductStats(int $tenantId): array
    {
        $planT = Db::name('mes_production_plan')->getTable();
        $allocT = Db::name('mes_allocation')->getTable();
        $reportT = Db::name('mes_report')->getTable();
        $modelT = Db::name('mes_product_model')->getTable();
        $productT = Db::name('mes_product')->getTable();
        $byModel = Db::table($planT . ' t')
            ->join($modelT . ' pm', 't.model_id = pm.id')
            ->leftJoin($productT . ' p', 'pm.product_id = p.id')
            ->field('t.model_id, COALESCE(p.name, pm.name) as product_name, COUNT(t.id) as total_plans, SUM(t.total_quantity) as total_quantity')
            ->group('t.model_id')
            ->when($tenantId > 0, fn ($q) => $q->where('t.tenant_id', $tenantId))
            ->select()->toArray();
        $completedQ = Db::table($reportT . ' r')->join($allocT . ' a', 'r.allocation_id = a.id')
            ->where('r.status', 1)->field('a.model_id, SUM(r.quantity) as completed_quantity')->group('a.model_id');
        if ($tenantId > 0) {
            $completedQ->where('r.tenant_id', $tenantId);
        }
        $completedByModel = [];
        foreach ($completedQ->select()->toArray() as $row) {
            $mid = (int) ($row['model_id'] ?? 0);
            if ($mid > 0) {
                $completedByModel[$mid] = (int) ($row['completed_quantity'] ?? 0);
            }
        }
        $out = [];
        foreach ($byModel as $r) {
            $mid = (int) ($r['model_id'] ?? 0);
            $total = (int) ($r['total_quantity'] ?? 0);
            $completed = $completedByModel[$mid] ?? 0;
            $out[] = [
                'product_name' => $r['product_name'] ?? '',
                'total_plans' => (int) ($r['total_plans'] ?? 0),
                'total_quantity' => $total,
                'completed_quantity' => $completed,
                'completion_rate' => $total > 0 ? round($completed / $total * 100, 1) : 0,
            ];
        }
        return $out;
    }

    /**
     * 大屏按工序统计表
     */
    private function getDashboardProcessStats(int $tenantId): array
    {
        $allocT = Db::name('mes_allocation')->getTable();
        $processT = Db::name('mes_process')->getTable();
        $reportT = Db::name('mes_report')->getTable();
        $rows = Db::table($allocT . ' a')
            ->join($processT . ' p', 'a.process_id = p.id')
            ->leftJoin($reportT . ' r', 'r.allocation_id = a.id AND r.status = 1')
            ->field('p.name as process_name, COUNT(a.id) as total_allocations, SUM(a.quantity) as total_quantity, COALESCE(SUM(r.quantity), 0) as completed_quantity')
            ->group('a.process_id')
            ->order('p.sort', 'asc')
            ->when($tenantId > 0, fn ($q) => $q->where('a.tenant_id', $tenantId))
            ->select()->toArray();
        $out = [];
        foreach ($rows as $r) {
            $total = (int) ($r['total_quantity'] ?? 0);
            $completed = (int) ($r['completed_quantity'] ?? 0);
            $out[] = [
                'process_name' => $r['process_name'] ?? '',
                'total_allocations' => (int) ($r['total_allocations'] ?? 0),
                'total_quantity' => $total,
                'completed_quantity' => $completed,
                'completion_rate' => $total > 0 ? round($completed / $total * 100, 1) : 0,
            ];
        }
        return $out;
    }

    /**
     * 大屏按员工统计表
     */
    private function getDashboardEmployeeStats(int $tenantId): array
    {
        $userT = (new UserModel())->getTable();
        $allocT = Db::name('mes_allocation')->getTable();
        $reportT = Db::name('mes_report')->getTable();
        $rows = Db::table($allocT . ' a')
            ->leftJoin($userT . ' u', 'a.user_id = u.id')
            ->leftJoin($reportT . ' r', 'r.allocation_id = a.id AND r.status = 1')
            ->field('a.user_id, COALESCE(u.nickname, CONCAT("用户#", a.user_id)) as user_name, COUNT(a.id) as total_allocations, SUM(a.quantity) as total_quantity, COALESCE(SUM(r.quantity), 0) as completed_quantity')
            ->group('a.user_id')
            ->when($tenantId > 0, fn ($q) => $q->where('a.tenant_id', $tenantId))
            ->select()->toArray();
        $out = [];
        foreach ($rows as $r) {
            $total = (int) ($r['total_quantity'] ?? 0);
            $completed = (int) ($r['completed_quantity'] ?? 0);
            $out[] = [
                'user_name' => $r['user_name'] ?? '用户#' . ($r['user_id'] ?? 0),
                'total_allocations' => (int) ($r['total_allocations'] ?? 0),
                'total_quantity' => $total,
                'completed_quantity' => $completed,
                'completion_rate' => $total > 0 ? round($completed / $total * 100, 1) : 0,
            ];
        }
        return $out;
    }

    /**
     * 大屏异常列表：待审核、今日不良等
     */
    private function buildDashboardExceptions(int $pendingReports, int $todayBad): array
    {
        $list = [];
        if ($pendingReports > 0) {
            $list[] = ['type' => '待审核报工', 'count' => $pendingReports, 'level' => 'warning'];
        }
        if ($todayBad > 0) {
            $list[] = ['type' => '今日不良', 'count' => $todayBad, 'level' => 'danger'];
        }
        return $list;
    }

    /**
     * 生产效率报表
     */
    public function productionEfficiency(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('default_start', date('Y-m-01'));
            View::assign('default_end', date('Y-m-d'));
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
        if ($tenantId > 0) {
            $query->where('r.tenant_id', $tenantId);
        } else {
            $tp = (int) $this->request->get('tenant_id', 0);
            if ($tp > 0) {
                $query->where('r.tenant_id', $tp);
            }
        }
        
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
            View::assign('default_start', date('Y-m-01'));
            View::assign('default_end', date('Y-m-d'));
            View::assign('title', '质量分析报表');
            return $this->fetchWithLayout('mes/bi/quality_analysis');
        }
        
        $tenantId = $this->getTenantId();
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-d'));
        
        $startTime = strtotime($startDate . ' 00:00:00');
        $endTime = strtotime($endDate . ' 23:59:59');
        
        // 按日期统计质量数据
        $query = ReportModel::where(function($q) use ($tenantId) {
                if ($tenantId > 0) { $q->where('tenant_id', $tenantId); } 
                else { $tp = (int) request()->get('tenant_id', 0); if ($tp > 0) { $q->where('tenant_id', $tp); } }
            })
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
            View::assign('default_start', date('Y-m-01'));
            View::assign('default_end', date('Y-m-d'));
            View::assign('title', '成本分析报表');
            return $this->fetchWithLayout('mes/bi/cost_analysis');
        }
        
        $tenantId = $this->getTenantId();
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-d'));
        
        $startTime = strtotime($startDate . ' 00:00:00');
        $endTime = strtotime($endDate . ' 23:59:59');
        
        // 按订单统计成本（mes_wage 无 order_id，通过 report->allocation 关联订单）
        $wageTable = (new WageModel())->getTable();
        $reportTable = (new ReportModel())->getTable();
        $allocationTable = (new AllocationModel())->getTable();
        $query = OrderModel::alias('o')
            ->leftJoin('mes_order_material om', 'o.id = om.order_id')
            ->where('o.create_time', 'between', [$startTime, $endTime])
            ->field('o.id, o.order_no, o.order_name,
                     SUM(om.estimated_amount) as material_cost,
                     (SELECT COALESCE(SUM(w.total_wage),0) FROM ' . $wageTable . ' w 
                      INNER JOIN ' . $reportTable . ' r ON w.report_id = r.id 
                      INNER JOIN ' . $allocationTable . ' a ON r.allocation_id = a.id 
                      WHERE a.order_id = o.id) as labor_cost')
            ->group('o.id')
            ->order('o.id', 'desc');
        if ($tenantId > 0) {
            $query->where('o.tenant_id', $tenantId);
        } else {
            $tp = (int) $this->request->get('tenant_id', 0);
            if ($tp > 0) {
                $query->where('o.tenant_id', $tp);
            }
        }
        
        $total = $query->count();
        $list = $query->select()->toArray();
        
        // 计算总成本
        foreach ($list as &$row) {
            $row['total_cost'] = (float) ($row['material_cost'] ?? 0) + (float) ($row['labor_cost'] ?? 0);
        }
        
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 生产进度同步：根据已审核报工汇总更新分配/计划的已完成数量与进度
     */
    public function syncProgress(): Response
    {
        $tenantId = $this->getTenantId();
        $tenantWhere = $tenantId > 0 ? ['tenant_id' => $tenantId] : [];

        $reportSums = ReportModel::where('status', 1)
            ->when(count($tenantWhere) > 0, fn ($q) => $q->where($tenantWhere))
            ->field('allocation_id, SUM(quantity) as total')
            ->group('allocation_id')
            ->select();

        $updatedAllocations = 0;
        foreach ($reportSums as $row) {
            $allocation = AllocationModel::where('id', $row->allocation_id)->find();
            if (!$allocation) {
                continue;
            }
            if ($tenantId > 0 && (int) $allocation->tenant_id !== $tenantId) {
                continue;
            }
            $sum = (int) $row->total;
            $allocation->completed_quantity = $sum;
            $allocation->status = $sum >= (int) $allocation->quantity ? 2 : ($sum > 0 ? 1 : $allocation->status);
            $allocation->save();
            $updatedAllocations++;
        }

        // 计划维度：按 plan_id 汇总分配的 completed/total，更新 plan.completed_quantity 与 progress
        $planIds = ProductionPlanModel::when(count($tenantWhere) > 0, fn ($q) => $q->where($tenantWhere))->column('id');
        foreach ($planIds as $planId) {
            $allocations = AllocationModel::where('plan_id', $planId)->select();
            $totalQty = 0;
            $completedQty = 0;
            foreach ($allocations as $a) {
                $totalQty += (int) $a->quantity;
                $completedQty += (int) $a->completed_quantity;
            }
            $plan = ProductionPlanModel::find($planId);
            if ($plan && $totalQty > 0) {
                $plan->completed_quantity = $completedQty;
                $plan->progress = round($completedQty / $totalQty * 100, 2);
                $plan->status = $completedQty >= $totalQty ? 2 : ($completedQty > 0 ? 1 : $plan->status);
                $plan->save();
            }
        }

        return $this->success('同步成功', ['updated_allocations' => $updatedAllocations]);
    }
}
