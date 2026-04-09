<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\AfterSalesModel;
use app\admin\model\mes\CustomerModel;
use app\admin\model\mes\MaterialModel;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\ProcessModel;
use app\admin\model\mes\ProcessRouteModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\ProductionPlanModel;
use app\admin\model\mes\PurchaseInboundItemModel;
use app\admin\model\mes\PurchaseInboundModel;
use app\admin\model\mes\PurchaseRequestModel;
use app\admin\model\mes\QualityCheckModel;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\ShipmentModel;
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
        View::assign('title', 'MES全链路数据监管大屏');
        return $this->fetchWithLayout('mes/bi/dashboard');
    }

    /**
     * 获取大屏数据
     */
    public function getDashboardData(): Response
    {
        $tenantId = $this->resolveDashboardTenantId();
        $now = time();
        $today = date('Y-m-d', $now);
        $todayStart = strtotime($today . ' 00:00:00');
        $todayEnd = strtotime($today . ' 23:59:59');
        $mode = (string) $this->request->get('mode', 'full');
        $coreOnly = $mode === 'core';

        $overallStats = $this->getDashboardOverallStats($tenantId);
        $routeStats = $this->getProcessRouteCompliance($tenantId);
        $stockStats = $this->getStockOverview($tenantId, $now);
        $purchaseStats = $this->getPurchaseOverview($tenantId, $now);
        $shipmentStats = $this->getShipmentOverview($tenantId, $now);
        $qualityStats = $this->getQualityOverview($tenantId, $now, $todayStart, $todayEnd);
        $planStats = $this->getPlanOverview($tenantId, $now);
        $customerStats = $this->getCustomerOverview($tenantId, $now);

        $kpis = [
            [
                'key' => 'production_completion_rate',
                'title' => '今日工单完工率',
                'value' => (float) ($overallStats['completion_rate'] ?? 0),
                'unit' => '%',
                'warn' => $this->warnByThreshold((float) ($overallStats['completion_rate'] ?? 0), 90, 95),
                'link' => $this->adminUrl('/mes/allocation'),
            ],
            [
                'key' => 'process_compliance_rate',
                'title' => '工艺合规率',
                'value' => (float) ($routeStats['rate'] ?? 0),
                'unit' => '%',
                'warn' => $this->warnByThreshold((float) ($routeStats['rate'] ?? 0), 98, 99),
                'link' => $this->adminUrl('/mes/process_route'),
            ],
            [
                'key' => 'stock_turnover_days',
                'title' => '库存周转天数',
                'value' => (float) ($stockStats['turnover_days'] ?? 0),
                'unit' => '天',
                'warn' => $this->warnByUpper((float) ($stockStats['turnover_days'] ?? 0), 30, 45),
                'link' => $this->adminUrl('/mes/stock'),
            ],
            [
                'key' => 'purchase_timely_rate',
                'title' => '采购及时率',
                'value' => (float) ($purchaseStats['timely_rate'] ?? 0),
                'unit' => '%',
                'warn' => $this->warnByThreshold((float) ($purchaseStats['timely_rate'] ?? 0), 95, 98),
                'link' => $this->adminUrl('/mes/purchase/request'),
            ],
            [
                'key' => 'shipment_ontime_rate',
                'title' => '发货准时率',
                'value' => (float) ($shipmentStats['ontime_rate'] ?? 0),
                'unit' => '%',
                'warn' => $this->warnByThreshold((float) ($shipmentStats['ontime_rate'] ?? 0), 98, 99),
                'link' => $this->adminUrl('/mes/shipment'),
            ],
            [
                'key' => 'first_pass_rate',
                'title' => '产品一次合格率',
                'value' => (float) ($qualityStats['first_pass_rate'] ?? 0),
                'unit' => '%',
                'warn' => $this->warnByThreshold((float) ($qualityStats['first_pass_rate'] ?? 0), 97, 99),
                'link' => $this->adminUrl('/mes/quality/check'),
            ],
            [
                'key' => 'plan_achieve_rate',
                'title' => '生产计划达成率',
                'value' => (float) ($planStats['achieve_rate'] ?? 0),
                'unit' => '%',
                'warn' => $this->warnByThreshold((float) ($planStats['achieve_rate'] ?? 0), 90, 95),
                'link' => $this->adminUrl('/mes/production_plan'),
            ],
            [
                'key' => 'customer_satisfaction',
                'title' => '客户满意度',
                'value' => (float) ($customerStats['satisfaction'] ?? 0),
                'unit' => '星',
                'warn' => $this->warnByThreshold((float) ($customerStats['satisfaction'] ?? 0), 4.5, 4.8),
                'link' => $this->adminUrl('/mes/customer'),
            ],
        ];

        $workOrders = $this->getWorkOrderRealtimeList($tenantId, $now, 18);
        $flow = $this->getOrderFlowStats($tenantId, $now);
        $alerts = $this->buildFullChainAlerts([
            'pending_reports' => (int) ($qualityStats['pending_reports'] ?? 0),
            'today_bad' => (int) ($qualityStats['today_bad'] ?? 0),
            'stock_warning' => (int) ($stockStats['warning_count'] ?? 0),
            'mrp_shortage' => (int) ($purchaseStats['mrp_shortage_count'] ?? 0),
            'shipment_overdue' => (int) ($shipmentStats['overdue_count'] ?? 0),
            'purchase_pending' => (int) ($purchaseStats['pending_count'] ?? 0),
            'quality_pending' => (int) ($qualityStats['pending_check_count'] ?? 0),
        ]);

        $data = [
            'server_time' => $now,
            'tenant_id' => $tenantId,
            'kpis' => $kpis,
            'alerts' => $alerts,
            'workorders' => $workOrders,
            'flow' => $flow,
            'overall_stats' => $overallStats,
        ];

        if (!$coreOnly) {
            $data['order_stats'] = $this->getDashboardOrderStats($tenantId);
            $data['product_stats'] = $this->getDashboardProductStats($tenantId);
            $data['process_stats'] = $this->getDashboardProcessStats($tenantId);
            $data['employee_stats'] = $this->getDashboardEmployeeStats($tenantId);
            $data['production'] = [
                'allocation_status' => $this->getAllocationStatusStats($tenantId),
                'output_trend' => $this->getOutputTrend($tenantId, 14),
                'capacity_top' => $this->getCapacityTop($tenantId, $todayStart, $todayEnd, 10),
                'route' => $routeStats,
            ];
            $data['stock'] = $stockStats;
            $data['purchase'] = $purchaseStats;
            $data['shipment'] = $shipmentStats;
            $data['quality'] = $qualityStats;
            $data['plan'] = $planStats;
            $data['customer'] = $customerStats;
        }

        return $this->success('', $data);
    }

    public function getAllocationGanttData(): Response
    {
        $tenantId = $this->resolveDashboardTenantId();
        if ($tenantId <= 0) {
            return $this->error('tenant_id required');
        }

        $days = (int) $this->request->get('days', 3);
        $days = max(1, min(14, $days));
        $startDate = trim((string) $this->request->get('start_date', ''));
        $startTs = $startDate !== '' ? strtotime($startDate . ' 00:00:00') : 0;
        if ($startTs <= 0) {
            $startTs = strtotime(date('Y-m-d', time()) . ' 00:00:00');
        }
        $endTs = $startTs + $days * 86400;

        $allocT = Db::name('mes_allocation')->getTable();
        $orderT = Db::name('mes_order')->getTable();
        $modelT = Db::name('mes_product_model')->getTable();
        $processT = Db::name('mes_process')->getTable();
        $userT = Db::name('user')->getTable();

        $rows = Db::table($allocT . ' a')
            ->leftJoin($orderT . ' o', 'a.order_id = o.id')
            ->leftJoin($modelT . ' m', 'a.model_id = m.id')
            ->leftJoin($processT . ' p', 'a.process_id = p.id')
            ->leftJoin($userT . ' u', 'a.user_id = u.id')
            ->where('a.tenant_id', $tenantId)
            ->where(function ($q) use ($startTs, $endTs) {
                $q->whereBetween('a.planned_start_time', [$startTs, $endTs])
                    ->whereOrBetween('a.planned_end_time', [$startTs, $endTs])
                    ->whereOr(function ($q2) use ($startTs, $endTs) {
                        $q2->where('a.planned_start_time', '<', $startTs)->where('a.planned_end_time', '>', $endTs);
                    })
                    ->whereOrBetween('a.create_time', [$startTs, $endTs]);
            })
            ->field('a.id,a.status,a.quantity,a.completed_quantity,a.planned_start_time,a.planned_end_time,a.actual_start_time,a.actual_end_time,o.order_no,m.name as model_name,p.name as process_name,COALESCE(u.nickname,u.username,CONCAT("用户#",a.user_id)) as user_name')
            ->order('a.planned_start_time', 'asc')
            ->order('a.id', 'asc')
            ->limit(200)
            ->select()
            ->toArray();

        $items = [];
        foreach ($rows as $r) {
            $plannedStart = (int) ($r['planned_start_time'] ?? 0);
            $plannedEnd = (int) ($r['planned_end_time'] ?? 0);
            $actualStart = (int) ($r['actual_start_time'] ?? 0);
            $actualEnd = (int) ($r['actual_end_time'] ?? 0);
            $createTime = time();
            try {
                $createTime = (int) ($r['create_time'] ?? 0);
            } catch (\Throwable $e) {
            }
            if ($plannedStart <= 0) {
                $plannedStart = $createTime > 0 ? $createTime : $startTs;
            }
            if ($plannedEnd <= 0 || $plannedEnd <= $plannedStart) {
                $plannedEnd = min($endTs, $plannedStart + 4 * 3600);
            }
            if ($actualStart > 0 && ($actualEnd <= 0 || $actualEnd <= $actualStart)) {
                $actualEnd = min($endTs, $actualStart + 2 * 3600);
            }

            $orderNo = (string) ($r['order_no'] ?? '');
            $modelName = (string) ($r['model_name'] ?? '');
            $processName = (string) ($r['process_name'] ?? '');
            $userName = (string) ($r['user_name'] ?? '');
            $label = trim(($processName !== '' ? $processName : '工序') . ' / ' . ($userName !== '' ? $userName : '') . ' / ' . ($orderNo !== '' ? $orderNo : '') . ($modelName !== '' ? (' ' . $modelName) : '') . ' #' . (int) ($r['id'] ?? 0));

            $qty = (int) ($r['quantity'] ?? 0);
            $done = (int) ($r['completed_quantity'] ?? 0);
            $progress = $qty > 0 ? round($done / $qty * 100, 1) : 0.0;

            $items[] = [
                'id' => (int) ($r['id'] ?? 0),
                'label' => $label,
                'status' => (int) ($r['status'] ?? 0),
                'quantity' => $qty,
                'completed_quantity' => $done,
                'progress' => $progress,
                'planned_start' => $plannedStart,
                'planned_end' => $plannedEnd,
                'actual_start' => $actualStart,
                'actual_end' => $actualEnd,
            ];
        }

        return $this->success('', [
            'start_time' => $startTs,
            'end_time' => $endTs,
            'items' => $items,
        ]);
    }

    private function getOrderFlowStats(int $tenantId, int $now): array
    {
        $orderT = Db::name('mes_order')->getTable();
        $planT = Db::name('mes_production_plan')->getTable();

        $orderQ = Db::table($orderT . ' o')->whereIn('o.status', [0, 1]);
        if ($tenantId > 0) {
            $orderQ->where('o.tenant_id', $tenantId);
        }
        $activeOrders = (int) $orderQ->count();

        $pendingPlanQ = Db::table($orderT . ' o')
            ->leftJoin($planT . ' p', 'p.order_id = o.id')
            ->whereIn('o.status', [0, 1])
            ->whereNull('p.id')
            ->field('COUNT(*) as c');
        if ($tenantId > 0) {
            $pendingPlanQ->where('o.tenant_id', $tenantId);
        }
        $pendingPlan = (int) (($pendingPlanQ->find())['c'] ?? 0);

        $allocInProdQ = AllocationModel::where('status', 1);
        if ($tenantId > 0) {
            $allocInProdQ->where('tenant_id', $tenantId);
        }
        $inProduction = (int) $allocInProdQ->count();

        $pendingShipmentQ = ShipmentModel::where('status', 0);
        if ($tenantId > 0) {
            $pendingShipmentQ->where('tenant_id', $tenantId);
        }
        $pendingShipment = (int) $pendingShipmentQ->count();

        $signed30dQ = ShipmentModel::where('status', '>=', 2)->where('sign_time', '>=', $now - 30 * 86400)->where('sign_time', '>', 0);
        if ($tenantId > 0) {
            $signed30dQ->where('tenant_id', $tenantId);
        }
        $signed30d = (int) $signed30dQ->count();

        return [
            [
                'key' => 'orders',
                'title' => '在途订单',
                'value' => $activeOrders,
                'link' => $this->adminUrl('/mes/order'),
            ],
            [
                'key' => 'pending_plan',
                'title' => '待排产',
                'value' => $pendingPlan,
                'link' => $this->adminUrl('/mes/production_plan'),
            ],
            [
                'key' => 'in_production',
                'title' => '生产中',
                'value' => $inProduction,
                'link' => $this->adminUrl('/mes/allocation'),
            ],
            [
                'key' => 'pending_audit',
                'title' => '待审核',
                'value' => (int) ReportModel::when($tenantId > 0, fn ($q) => $q->where('tenant_id', $tenantId))->where('status', 0)->count(),
                'link' => $this->adminUrl('/mes/report'),
            ],
            [
                'key' => 'pending_quality',
                'title' => '待质检',
                'value' => (int) QualityCheckModel::when($tenantId > 0, fn ($q) => $q->where('tenant_id', $tenantId))->where('status', 0)->count(),
                'link' => $this->adminUrl('/mes/quality/check'),
            ],
            [
                'key' => 'pending_shipment',
                'title' => '待发货',
                'value' => $pendingShipment,
                'link' => $this->adminUrl('/mes/shipment'),
            ],
            [
                'key' => 'signed_30d',
                'title' => '已签收(30天)',
                'value' => $signed30d,
                'link' => $this->adminUrl('/mes/shipment'),
            ],
        ];
    }

    private function resolveDashboardTenantId(): int
    {
        $tenantId = (int) $this->getTenantId();
        if ($tenantId > 0) {
            return $tenantId;
        }
        $tp = (int) $this->request->get('tenant_id', 0);
        return $tp > 0 ? $tp : 0;
    }

    private function adminUrl(string $path): string
    {
        $base = (string) ($this->request->root() ?: '');
        $base = rtrim($base, '/');
        if ($base === '') {
            $base = '/admin';
        }
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }
        return $base . $path;
    }

    private function warnByThreshold(float $value, float $redBelow, float $greenAbove): string
    {
        if ($value < $redBelow) {
            return 'danger';
        }
        if ($value < $greenAbove) {
            return 'warning';
        }
        return 'success';
    }

    private function warnByUpper(float $value, float $warningAbove, float $dangerAbove): string
    {
        if ($value >= $dangerAbove) {
            return 'danger';
        }
        if ($value >= $warningAbove) {
            return 'warning';
        }
        return 'success';
    }

    private function buildFullChainAlerts(array $counts): array
    {
        $list = [];
        $total = 0;

        $map = [
            [
                'key' => 'pending_reports',
                'title' => '待审核报工',
                'level' => 'warning',
                'link' => $this->adminUrl('/mes/report'),
            ],
            [
                'key' => 'today_bad',
                'title' => '今日不良',
                'level' => 'danger',
                'link' => $this->adminUrl('/mes/quality/check'),
            ],
            [
                'key' => 'stock_warning',
                'title' => '库存预警',
                'level' => 'warning',
                'link' => $this->adminUrl('/mes/stock/alert'),
            ],
            [
                'key' => 'mrp_shortage',
                'title' => 'MRP缺料',
                'level' => 'danger',
                'link' => $this->adminUrl('/mes/mrp'),
            ],
            [
                'key' => 'shipment_overdue',
                'title' => '发货逾期',
                'level' => 'danger',
                'link' => $this->adminUrl('/mes/shipment'),
            ],
            [
                'key' => 'purchase_pending',
                'title' => '采购待审',
                'level' => 'warning',
                'link' => $this->adminUrl('/mes/purchase/request'),
            ],
            [
                'key' => 'quality_pending',
                'title' => '质检待处理',
                'level' => 'warning',
                'link' => $this->adminUrl('/mes/quality/check'),
            ],
        ];

        foreach ($map as $it) {
            $cnt = (int) ($counts[$it['key']] ?? 0);
            if ($cnt <= 0) {
                continue;
            }
            $total += $cnt;
            $list[] = [
                'type' => $it['title'],
                'count' => $cnt,
                'level' => $it['level'],
                'link' => $it['link'],
            ];
        }

        return ['total' => $total, 'list' => $list];
    }

    private function getWorkOrderRealtimeList(int $tenantId, int $now, int $limit): array
    {
        $allocT = Db::name('mes_allocation')->getTable();
        $orderT = Db::name('mes_order')->getTable();
        $modelT = Db::name('mes_product_model')->getTable();
        $processT = Db::name('mes_process')->getTable();
        $userT = Db::name('user')->getTable();
        $q = Db::table($allocT . ' a')
            ->leftJoin($orderT . ' o', 'a.order_id = o.id')
            ->leftJoin($modelT . ' m', 'a.model_id = m.id')
            ->leftJoin($processT . ' p', 'a.process_id = p.id')
            ->leftJoin($userT . ' u', 'a.user_id = u.id')
            ->field('a.id,a.status,a.quantity,a.completed_quantity,a.planned_end_time,o.order_no,o.order_name,m.name as model_name,p.name as process_name,COALESCE(u.nickname,u.username,CONCAT("用户#",a.user_id)) as user_name')
            ->order('a.status', 'asc')
            ->order('a.planned_end_time', 'asc')
            ->order('a.id', 'desc')
            ->limit($limit);
        if ($tenantId > 0) {
            $q->where('a.tenant_id', $tenantId);
        }
        $rows = $q->select()->toArray();
        $statusMap = AllocationModel::getStatusList();
        foreach ($rows as &$r) {
            $qty = (int) ($r['quantity'] ?? 0);
            $done = (int) ($r['completed_quantity'] ?? 0);
            $progress = $qty > 0 ? round($done / $qty * 100, 1) : 0;
            $pe = (int) ($r['planned_end_time'] ?? 0);
            $remainingHours = $pe > 0 ? round(max(0, $pe - $now) / 3600, 1) : 0;
            $r['status_text'] = $statusMap[(int) ($r['status'] ?? 0)] ?? '';
            $r['progress'] = $progress;
            $r['remaining_hours'] = $remainingHours;
            $r['planned_end_time_text'] = $pe > 0 ? date('m-d H:i', $pe) : '';
            $r['link'] = $this->adminUrl('/mes/allocation');
        }
        unset($r);
        return $rows;
    }

    private function getAllocationStatusStats(int $tenantId): array
    {
        $q = AllocationModel::field('status, COUNT(*) as count')->group('status');
        if ($tenantId > 0) {
            $q->where('tenant_id', $tenantId);
        }
        $rows = $q->select()->toArray();
        $map = [0 => 0, 1 => 0, 2 => 0];
        foreach ($rows as $r) {
            $map[(int) ($r['status'] ?? 0)] = (int) ($r['count'] ?? 0);
        }
        return [
            ['name' => '待开始', 'value' => $map[0]],
            ['name' => '进行中', 'value' => $map[1]],
            ['name' => '已完成', 'value' => $map[2]],
        ];
    }

    private function getOutputTrend(int $tenantId, int $days): array
    {
        $days = max(7, min(31, $days));
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $start = strtotime($date . ' 00:00:00');
            $end = strtotime($date . ' 23:59:59');
            $q = ReportModel::where('status', 1)->where('create_time', 'between', [$start, $end]);
            if ($tenantId > 0) {
                $q->where('tenant_id', $tenantId);
            }
            $sum = (float) $q->sum('quantity');
            $out[] = ['date' => $date, 'quantity' => $sum];
        }
        return $out;
    }

    private function getCapacityTop(int $tenantId, int $start, int $end, int $limit): array
    {
        $limit = max(5, min(50, $limit));
        $userT = Db::name('user')->getTable();
        $reportT = Db::name('mes_report')->getTable();
        $q = Db::table($reportT . ' r')
            ->leftJoin($userT . ' u', 'r.user_id = u.id')
            ->where('r.status', 1)
            ->where('r.create_time', 'between', [$start, $end])
            ->field('r.user_id, COALESCE(u.nickname,u.username,CONCAT("用户#",r.user_id)) as user_name, SUM(r.quantity) as quantity, SUM(r.work_hours) as hours')
            ->group('r.user_id')
            ->order('quantity', 'desc')
            ->limit($limit);
        if ($tenantId > 0) {
            $q->where('r.tenant_id', $tenantId);
        }
        $rows = $q->select()->toArray();
        foreach ($rows as &$r) {
            $r['quantity'] = (float) ($r['quantity'] ?? 0);
            $r['hours'] = (float) ($r['hours'] ?? 0);
        }
        unset($r);
        return $rows;
    }

    private function getProcessRouteCompliance(int $tenantId): array
    {
        $modelT = Db::name('mes_product_model')->getTable();
        $productT = Db::name('mes_product')->getTable();
        $routeQ = ProcessRouteModel::where('status', 2);
        if ($tenantId > 0) {
            $routeQ->where('tenant_id', $tenantId);
        }
        $routeModelIds = $routeQ->column('model_id');
        $routeModelIds = array_values(array_unique(array_filter(array_map('intval', $routeModelIds))));

        $totalModelsQ = ProductModelModel::where('status', 1);
        if ($tenantId > 0) {
            $totalModelsQ->where('tenant_id', $tenantId);
        }
        $totalModels = (int) $totalModelsQ->count();
        $withRoute = count($routeModelIds);
        $rate = $totalModels > 0 ? round($withRoute / $totalModels * 100, 1) : 0;

        $missing = [];
        if ($totalModels > 0) {
            $missingQ = Db::table($modelT . ' m')
                ->leftJoin($productT . ' p', 'm.product_id = p.id')
                ->where('m.status', 1)
                ->field('m.id,m.name as model_name,COALESCE(p.name,"") as product_name')
                ->order('m.id', 'desc')
                ->limit(8);
            if ($tenantId > 0) {
                $missingQ->where('m.tenant_id', $tenantId);
            }
            if ($routeModelIds) {
                $missingQ->whereNotIn('m.id', $routeModelIds);
            }
            $missing = $missingQ->select()->toArray();
            foreach ($missing as &$m) {
                $label = trim(($m['product_name'] ?? '') . ' ' . ($m['model_name'] ?? ''));
                $m['label'] = $label !== '' ? $label : ('型号#' . (int) ($m['id'] ?? 0));
                $m['link'] = $this->adminUrl('/mes/process_route');
            }
            unset($m);
        }

        return [
            'total_models' => $totalModels,
            'with_route' => $withRoute,
            'rate' => $rate,
            'missing' => $missing,
        ];
    }

    private function getStockOverview(int $tenantId, int $now): array
    {
        $materialQ = MaterialModel::where('id', '>', 0);
        $productQ = ProductModelModel::where('id', '>', 0);
        $wipQ = AllocationModel::whereIn('status', [0, 1]);
        $warnQ = MaterialModel::whereColumn('stock', '<', 'min_stock')->where('min_stock', '>', 0);

        if ($tenantId > 0) {
            $materialQ->where('tenant_id', $tenantId);
            $productQ->where('tenant_id', $tenantId);
            $wipQ->where('tenant_id', $tenantId);
            $warnQ->where('tenant_id', $tenantId);
        }

        $materialStock = (float) $materialQ->sum('stock');
        $productStock = (float) $productQ->sum('stock');
        $wipRow = $wipQ->fieldRaw('SUM(GREATEST(quantity - completed_quantity,0)) as s')->find();
        $wip = (float) (($wipRow['s'] ?? 0));
        $warningCount = (int) $warnQ->count();
        $warningList = $warnQ->field('id,name,code,stock,min_stock')->orderRaw('(min_stock - stock) DESC')->limit(8)->select()->toArray();
        foreach ($warningList as &$w) {
            $w['shortage'] = max(0, (float) ($w['min_stock'] ?? 0) - (float) ($w['stock'] ?? 0));
            $w['link'] = $this->adminUrl('/mes/stock/alert');
        }
        unset($w);

        $stockLogQ = Db::name('mes_stock_log')
            ->whereIn('business_type', ['shipment_out', 'production_out'])
            ->where('create_time', '>=', $now - 30 * 86400)
            ->where('change_quantity', '<', 0);
        if ($tenantId > 0) {
            $stockLogQ->where('tenant_id', $tenantId);
        }
        $outbound30 = (float) $stockLogQ->sum('change_quantity');
        $outbound30 = abs($outbound30);
        $totalStock = $materialStock + $productStock;
        $turnoverDays = 0.0;
        if ($outbound30 > 0) {
            $daily = $outbound30 / 30;
            $turnoverDays = $daily > 0 ? round($totalStock / $daily, 1) : 0.0;
        }

        return [
            'material_stock' => $materialStock,
            'product_stock' => $productStock,
            'wip_stock' => $wip,
            'warning_count' => $warningCount,
            'warning_list' => $warningList,
            'turnover_days' => $turnoverDays,
        ];
    }

    private function getPurchaseOverview(int $tenantId, int $now): array
    {
        $start = $now - 30 * 86400;

        $reqQ = PurchaseRequestModel::where('create_time', '>=', $start);
        if ($tenantId > 0) {
            $reqQ->where('tenant_id', $tenantId);
        }
        $totalReq = (int) $reqQ->count();
        $pending = (int) (clone $reqQ)->where('status', 0)->count();

        $inbounded = 0;
        try {
            $itemT = (new PurchaseInboundItemModel())->getTable();
            $inboundT = (new PurchaseInboundModel())->getTable();
            $q = Db::table($itemT . ' i')
                ->join($inboundT . ' b', 'i.inbound_id = b.id')
                ->where('b.status', 2)
                ->where('i.purchase_request_id', '>', 0)
                ->where('i.create_time', '>=', $start);
            if ($tenantId > 0) {
                $q->where('i.tenant_id', $tenantId);
            }
            $inbounded = (int) $q->group('i.purchase_request_id')->count();
        } catch (\Throwable $e) {
            $inbounded = 0;
        }

        $timelyRate = $totalReq > 0 ? round($inbounded / $totalReq * 100, 1) : 0.0;

        $mrp = $this->getMrpShortageList($tenantId, 8);

        $supplierTop = [];
        try {
            $inboundT = (new PurchaseInboundModel())->getTable();
            $supplierT = Db::name('mes_supplier')->getTable();
            $q = Db::table($inboundT . ' b')
                ->leftJoin($supplierT . ' s', 'b.supplier_id = s.id')
                ->where('b.status', 2)
                ->where('b.create_time', '>=', $start)
                ->field('b.supplier_id, COALESCE(s.name, CONCAT("供应商#",b.supplier_id)) as supplier_name, SUM(b.total_amount) as amount')
                ->group('b.supplier_id')
                ->order('amount', 'desc')
                ->limit(8);
            if ($tenantId > 0) {
                $q->where('b.tenant_id', $tenantId);
            }
            $supplierTop = $q->select()->toArray();
            foreach ($supplierTop as &$s) {
                $s['amount'] = (float) ($s['amount'] ?? 0);
                $s['link'] = $this->adminUrl('/mes/supplier');
            }
            unset($s);
        } catch (\Throwable $e) {
            $supplierTop = [];
        }

        return [
            'total_count' => $totalReq,
            'pending_count' => $pending,
            'inbounded_count' => $inbounded,
            'timely_rate' => $timelyRate,
            'mrp_shortage_count' => count($mrp),
            'mrp_shortage_list' => $mrp,
            'supplier_top' => $supplierTop,
        ];
    }

    private function getMrpShortageList(int $tenantId, int $limit): array
    {
        $limit = max(5, min(50, $limit));
        $orderIdsQ = OrderModel::whereIn('status', [0, 1]);
        if ($tenantId > 0) {
            $orderIdsQ->where('tenant_id', $tenantId);
        }
        $orderIds = $orderIdsQ->column('id');
        if (!$orderIds) {
            return [];
        }

        $rows = Db::name('mes_order_material')->whereIn('order_id', $orderIds)->field('material_id, SUM(required_quantity) as required')->group('material_id')->select()->toArray();
        if (!$rows) {
            return [];
        }

        $materialIds = array_values(array_unique(array_filter(array_map(fn ($r) => (int) ($r['material_id'] ?? 0), $rows))));
        if (!$materialIds) {
            return [];
        }
        $materialsQ = MaterialModel::whereIn('id', $materialIds);
        if ($tenantId > 0) {
            $materialsQ->where('tenant_id', $tenantId);
        }
        $materials = $materialsQ->field('id,name,code,unit,stock')->select()->toArray();
        $matMap = [];
        foreach ($materials as $m) {
            $matMap[(int) ($m['id'] ?? 0)] = $m;
        }

        $list = [];
        foreach ($rows as $r) {
            $mid = (int) ($r['material_id'] ?? 0);
            if ($mid <= 0 || !isset($matMap[$mid])) {
                continue;
            }
            $req = (float) ($r['required'] ?? 0);
            $stock = (float) ($matMap[$mid]['stock'] ?? 0);
            $shortage = max(0, $req - $stock);
            if ($shortage <= 0) {
                continue;
            }
            $list[] = [
                'material_id' => $mid,
                'material_name' => (string) ($matMap[$mid]['name'] ?? ''),
                'material_code' => (string) ($matMap[$mid]['code'] ?? ''),
                'unit' => (string) ($matMap[$mid]['unit'] ?? ''),
                'required' => $req,
                'stock' => $stock,
                'shortage' => $shortage,
                'link' => $this->adminUrl('/mes/mrp'),
            ];
        }

        usort($list, fn ($a, $b) => ($b['shortage'] <=> $a['shortage']));
        return array_slice($list, 0, $limit);
    }

    private function getShipmentOverview(int $tenantId, int $now): array
    {
        $start = $now - 30 * 86400;

        $pendingQ = ShipmentModel::where('status', 0);
        if ($tenantId > 0) {
            $pendingQ->where('tenant_id', $tenantId);
        }
        $pendingCount = (int) $pendingQ->count();

        $shipmentT = (new ShipmentModel())->getTable();
        $orderT = (new OrderModel())->getTable();
        $customerT = (new CustomerModel())->getTable();
        $overdueQ = Db::table($shipmentT . ' s')
            ->leftJoin($orderT . ' o', 's.order_id = o.id')
            ->leftJoin($customerT . ' c', 's.customer_id = c.id')
            ->where('s.status', '<', 2)
            ->where('o.delivery_time', '>', 0)
            ->where('o.delivery_time', '<', $now)
            ->field('s.id,s.shipment_no,s.status,o.order_no,COALESCE(c.customer_name,"") as customer_name,o.delivery_time')
            ->order('o.delivery_time', 'asc')
            ->limit(8);
        if ($tenantId > 0) {
            $overdueQ->where('s.tenant_id', $tenantId);
        }
        $overdueList = $overdueQ->select()->toArray();
        foreach ($overdueList as &$r) {
            $r['delivery_time_text'] = (int) ($r['delivery_time'] ?? 0) > 0 ? date('m-d H:i', (int) $r['delivery_time']) : '';
            $r['link'] = $this->adminUrl('/mes/shipment');
        }
        unset($r);

        $overdueCount = (int) Db::table($shipmentT . ' s')->leftJoin($orderT . ' o', 's.order_id = o.id')
            ->when($tenantId > 0, fn ($q) => $q->where('s.tenant_id', $tenantId))
            ->where('s.status', '<', 2)
            ->where('o.delivery_time', '>', 0)
            ->where('o.delivery_time', '<', $now)
            ->count();

        $signedQ = Db::table($shipmentT . ' s')->leftJoin($orderT . ' o', 's.order_id = o.id')
            ->where('s.status', '>=', 2)
            ->where('s.sign_time', '>=', $start)
            ->where('s.sign_time', '>', 0)
            ->field('COUNT(*) as total, SUM(CASE WHEN o.delivery_time=0 OR s.sign_time<=o.delivery_time THEN 1 ELSE 0 END) as ontime');
        if ($tenantId > 0) {
            $signedQ->where('s.tenant_id', $tenantId);
        }
        $signedRow = $signedQ->find();
        $signedTotal = (int) ($signedRow['total'] ?? 0);
        $signedOnTime = (int) ($signedRow['ontime'] ?? 0);
        $onTimeRate = $signedTotal > 0 ? round($signedOnTime / $signedTotal * 100, 1) : 0.0;

        return [
            'pending_count' => $pendingCount,
            'overdue_count' => $overdueCount,
            'overdue_list' => $overdueList,
            'ontime_rate' => $onTimeRate,
        ];
    }

    private function getQualityOverview(int $tenantId, int $now, int $todayStart, int $todayEnd): array
    {
        $start = $now - 30 * 86400;

        $pendingReportsQ = ReportModel::where('status', 0);
        if ($tenantId > 0) {
            $pendingReportsQ->where('tenant_id', $tenantId);
        }
        $pendingReports = (int) $pendingReportsQ->count();

        $todayBadQ = ReportModel::where('status', 1)->where('quality_status', 2)->where('create_time', 'between', [$todayStart, $todayEnd]);
        if ($tenantId > 0) {
            $todayBadQ->where('tenant_id', $tenantId);
        }
        $todayBad = (int) $todayBadQ->sum('quantity');

        $qtyQ = ReportModel::where('status', 1)->where('create_time', '>=', $start);
        if ($tenantId > 0) {
            $qtyQ->where('tenant_id', $tenantId);
        }
        $totalQty = (float) $qtyQ->sum('quantity');
        $qualifiedQty = (float) (clone $qtyQ)->where('quality_status', 1)->sum('quantity');
        $firstPassRate = $totalQty > 0 ? round($qualifiedQty / $totalQty * 100, 1) : 0.0;

        $pendingCheckQ = QualityCheckModel::where('status', 0);
        if ($tenantId > 0) {
            $pendingCheckQ->where('tenant_id', $tenantId);
        }
        $pendingCheckCount = (int) $pendingCheckQ->count();

        $allocT = Db::name('mes_allocation')->getTable();
        $processT = Db::name('mes_process')->getTable();
        $reportT = Db::name('mes_report')->getTable();
        $defectByProcessQ = Db::table($reportT . ' r')
            ->join($allocT . ' a', 'r.allocation_id = a.id')
            ->leftJoin($processT . ' p', 'a.process_id = p.id')
            ->where('r.status', 1)
            ->where('r.quality_status', 2)
            ->where('r.create_time', '>=', $start)
            ->field('a.process_id, COALESCE(p.name, CONCAT("工序#",a.process_id)) as process_name, SUM(r.quantity) as bad_quantity')
            ->group('a.process_id')
            ->order('bad_quantity', 'desc')
            ->limit(8);
        if ($tenantId > 0) {
            $defectByProcessQ->where('r.tenant_id', $tenantId);
        }
        $defectByProcess = $defectByProcessQ->select()->toArray();
        foreach ($defectByProcess as &$d) {
            $d['bad_quantity'] = (float) ($d['bad_quantity'] ?? 0);
            $d['link'] = $this->adminUrl('/mes/quality/check');
        }
        unset($d);

        return [
            'pending_reports' => $pendingReports,
            'today_bad' => $todayBad,
            'first_pass_rate' => $firstPassRate,
            'pending_check_count' => $pendingCheckCount,
            'defect_by_process' => $defectByProcess,
        ];
    }

    private function getPlanOverview(int $tenantId, int $now): array
    {
        $start = $now - 30 * 86400;
        $q = ProductionPlanModel::where('status', 2)->where('planned_end_time', '>', 0)->where('actual_end_time', '>', 0)->where('actual_end_time', '>=', $start);
        if ($tenantId > 0) {
            $q->where('tenant_id', $tenantId);
        }
        $total = (int) $q->count();
        $ontime = (int) (clone $q)->whereColumn('actual_end_time', '<=', 'planned_end_time')->count();
        $rate = $total > 0 ? round($ontime / $total * 100, 1) : 0.0;
        return [
            'total_completed_30d' => $total,
            'ontime_completed_30d' => $ontime,
            'achieve_rate' => $rate,
        ];
    }

    private function getCustomerOverview(int $tenantId, int $now): array
    {
        $start = $now - 30 * 86400;

        $shipmentT = (new ShipmentModel())->getTable();
        $customerT = (new CustomerModel())->getTable();
        $topQ = Db::table($shipmentT . ' s')
            ->leftJoin($customerT . ' c', 's.customer_id = c.id')
            ->where('s.create_time', '>=', $start)
            ->field('s.customer_id, COALESCE(c.customer_name, CONCAT("客户#",s.customer_id)) as customer_name, SUM(s.shipment_quantity) as quantity')
            ->group('s.customer_id')
            ->order('quantity', 'desc')
            ->limit(10);
        if ($tenantId > 0) {
            $topQ->where('s.tenant_id', $tenantId);
        }
        $top = $topQ->select()->toArray();
        foreach ($top as &$t) {
            $t['quantity'] = (float) ($t['quantity'] ?? 0);
            $t['link'] = $this->adminUrl('/mes/customer');
        }
        unset($t);

        $aftQ = AfterSalesModel::where('create_time', '>=', $start);
        if ($tenantId > 0) {
            $aftQ->where('tenant_id', $tenantId);
        }
        $total = (int) $aftQ->count();
        $open = (int) (clone $aftQ)->whereIn('status', [0, 1])->count();
        $satisfaction = $total > 0 ? round((1 - $open / $total) * 5, 1) : 5.0;
        if ($satisfaction < 0) {
            $satisfaction = 0.0;
        }
        if ($satisfaction > 5) {
            $satisfaction = 5.0;
        }

        return [
            'satisfaction' => $satisfaction,
            'top_customers' => $top,
        ];
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
