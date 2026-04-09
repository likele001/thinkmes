<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use think\facade\Db;
use think\Response;

class Mesdashboard extends BaseController
{
    protected function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    public function data(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) {
            return $this->error('请先登录');
        }

        $now = time();
        $today = date('Y-m-d', $now);
        $todayStart = strtotime($today . ' 00:00:00');
        $todayEnd = strtotime($today . ' 23:59:59');

        $overall = $this->getOverallStats($tenantId);
        $orderStats = $this->getOrderStats($tenantId);
        $productStats = $this->getProductStats($tenantId);
        $processStats = $this->getProcessStats($tenantId);
        $employeeStats = $this->getEmployeeStats($tenantId);
        $workorders = $this->getWorkorders($tenantId, 24);
        $capacityTop = $this->getCapacityTop($tenantId, $todayStart, $todayEnd, 10);
        $outputTrend = $this->getOutputTrend($tenantId, 14);
        $stock = $this->getStockOverview($tenantId, $now);
        $purchase = $this->getPurchaseOverview($tenantId);
        $quality = $this->getQualityOverview($tenantId, $now, $todayStart, $todayEnd);

        $alerts = $this->buildAlerts([
            'pending_reports' => (int) ($quality['pending_reports'] ?? 0),
            'today_bad' => (int) ($quality['today_bad'] ?? 0),
            'stock_warning' => (int) ($stock['warning_count'] ?? 0),
            'mrp_shortage' => (int) ($purchase['mrp_shortage_count'] ?? 0),
        ]);

        return $this->success('', [
            'server_time' => $now,
            'tenant_id' => $tenantId,
            'overall_stats' => $overall,
            'alerts' => $alerts,
            'workorders' => $workorders,
            'order_stats' => $orderStats,
            'product_stats' => $productStats,
            'process_stats' => $processStats,
            'employee_stats' => $employeeStats,
            'production' => [
                'capacity_top' => $capacityTop,
                'output_trend' => $outputTrend,
            ],
            'stock' => $stock,
            'purchase' => $purchase,
            'quality' => $quality,
        ]);
    }

    public function gantt(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) {
            return $this->error('请先登录');
        }

        $days = (int) $this->request->get('days', 7);
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
            ->whereRaw(
                '(
                    (a.planned_start_time BETWEEN :s1 AND :e1)
                    OR (a.planned_end_time BETWEEN :s2 AND :e2)
                    OR (a.planned_start_time < :s3 AND a.planned_end_time > :e3)
                    OR (a.create_time BETWEEN :s4 AND :e4)
                )',
                [
                    's1' => $startTs,
                    'e1' => $endTs,
                    's2' => $startTs,
                    'e2' => $endTs,
                    's3' => $startTs,
                    'e3' => $endTs,
                    's4' => $startTs,
                    'e4' => $endTs,
                ]
            )
            ->field('a.id,a.status,a.quantity,a.completed_quantity,a.planned_start_time,a.planned_end_time,a.actual_start_time,a.actual_end_time,a.create_time,o.order_no,m.name as model_name,p.name as process_name,COALESCE(u.nickname,u.username,CONCAT("用户#",a.user_id)) as user_name')
            ->order('a.planned_start_time', 'asc')
            ->order('a.id', 'asc')
            ->limit(200)
            ->select()
            ->toArray();

        if (!$rows) {
            $rows = Db::table($allocT . ' a')
                ->leftJoin($orderT . ' o', 'a.order_id = o.id')
                ->leftJoin($modelT . ' m', 'a.model_id = m.id')
                ->leftJoin($processT . ' p', 'a.process_id = p.id')
                ->leftJoin($userT . ' u', 'a.user_id = u.id')
                ->where('a.tenant_id', $tenantId)
                ->field('a.id,a.status,a.quantity,a.completed_quantity,a.planned_start_time,a.planned_end_time,a.actual_start_time,a.actual_end_time,a.create_time,o.order_no,m.name as model_name,p.name as process_name,COALESCE(u.nickname,u.username,CONCAT("用户#",a.user_id)) as user_name')
                ->order('a.id', 'desc')
                ->limit(120)
                ->select()
                ->toArray();
            $rows = array_reverse($rows);
        }

        $items = [];
        $i = 0;
        foreach ($rows as $r) {
            $plannedStart = (int) ($r['planned_start_time'] ?? 0);
            $plannedEnd = (int) ($r['planned_end_time'] ?? 0);
            $actualStart = (int) ($r['actual_start_time'] ?? 0);
            $actualEnd = (int) ($r['actual_end_time'] ?? 0);
            $createTime = (int) ($r['create_time'] ?? 0);

            if ($plannedStart <= 0) {
                $plannedStart = $createTime > 0 ? $createTime : ($startTs + $i * 1800);
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
            $i++;
        }

        return $this->success('', [
            'start_time' => $startTs,
            'end_time' => $endTs,
            'items' => $items,
        ]);
    }

    private function getOverallStats(int $tenantId): array
    {
        $planT = Db::name('mes_production_plan')->getTable();
        $allocT = Db::name('mes_allocation')->getTable();
        $reportT = Db::name('mes_report')->getTable();

        $totalPlans = (int) Db::table($planT)->where('tenant_id', $tenantId)->count();
        $totalAllocations = (int) Db::table($allocT)->where('tenant_id', $tenantId)->count();
        $totalQuantity = (int) Db::table($allocT)->where('tenant_id', $tenantId)->sum('quantity');
        $completedQuantity = (int) Db::table($reportT)->where('tenant_id', $tenantId)->where('status', 1)->sum('quantity');

        $orderIds = Db::table($planT)->where('tenant_id', $tenantId)->group('order_id')->column('order_id');
        $totalOrders = count(array_unique(array_filter($orderIds)));
        if ($totalPlans <= 0) {
            $orderT = Db::name('mes_order')->getTable();
            $totalOrders = (int) Db::table($orderT)->where('tenant_id', $tenantId)->whereIn('status', [0, 1])->count();
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

    private function getOrderStats(int $tenantId): array
    {
        $orderT = Db::name('mes_order')->getTable();
        $planT = Db::name('mes_production_plan')->getTable();
        $allocT = Db::name('mes_allocation')->getTable();
        $reportT = Db::name('mes_report')->getTable();

        $byOrder = Db::table($planT . ' t')->join($orderT . ' o', 't.order_id = o.id')
            ->where('t.tenant_id', $tenantId)
            ->field('o.id as order_id, o.order_name, o.order_no, COUNT(t.id) as total_plans, SUM(t.total_quantity) as total_quantity')
            ->group('t.order_id')
            ->select()
            ->toArray();

        $completedQ = Db::table($reportT . ' r')
            ->join($allocT . ' a', 'r.allocation_id = a.id')
            ->leftJoin($planT . ' p', 'a.plan_id = p.id')
            ->where('r.tenant_id', $tenantId)
            ->where('r.status', 1)
            ->field('COALESCE(p.order_id, a.order_id) as order_id, SUM(r.quantity) as completed_quantity')
            ->group('COALESCE(p.order_id, a.order_id)')
            ->select()
            ->toArray();
        $completedByOrder = [];
        foreach ($completedQ as $row) {
            $oid = (int) ($row['order_id'] ?? 0);
            if ($oid > 0) {
                $completedByOrder[$oid] = (int) ($row['completed_quantity'] ?? 0);
            }
        }

        $out = [];
        foreach ($byOrder as $r) {
            $oid = (int) ($r['order_id'] ?? 0);
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

    private function getProductStats(int $tenantId): array
    {
        $planT = Db::name('mes_production_plan')->getTable();
        $allocT = Db::name('mes_allocation')->getTable();
        $reportT = Db::name('mes_report')->getTable();
        $modelT = Db::name('mes_product_model')->getTable();
        $productT = Db::name('mes_product')->getTable();

        $byModel = Db::table($planT . ' t')
            ->join($modelT . ' pm', 't.model_id = pm.id')
            ->leftJoin($productT . ' p', 'pm.product_id = p.id')
            ->where('t.tenant_id', $tenantId)
            ->field('t.model_id, COALESCE(p.name, pm.name) as product_name, COUNT(t.id) as total_plans, SUM(t.total_quantity) as total_quantity')
            ->group('t.model_id')
            ->select()
            ->toArray();

        $completedQ = Db::table($reportT . ' r')->join($allocT . ' a', 'r.allocation_id = a.id')
            ->where('r.tenant_id', $tenantId)
            ->where('r.status', 1)
            ->field('a.model_id, SUM(r.quantity) as completed_quantity')
            ->group('a.model_id')
            ->select()
            ->toArray();
        $completedByModel = [];
        foreach ($completedQ as $row) {
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

    private function getProcessStats(int $tenantId): array
    {
        $allocT = Db::name('mes_allocation')->getTable();
        $processT = Db::name('mes_process')->getTable();
        $reportT = Db::name('mes_report')->getTable();

        $rows = Db::table($allocT . ' a')
            ->join($processT . ' p', 'a.process_id = p.id')
            ->leftJoin($reportT . ' r', 'r.allocation_id = a.id AND r.status = 1')
            ->where('a.tenant_id', $tenantId)
            ->field('p.name as process_name, COUNT(a.id) as total_allocations, SUM(a.quantity) as total_quantity, COALESCE(SUM(r.quantity), 0) as completed_quantity')
            ->group('a.process_id')
            ->order('p.sort', 'asc')
            ->select()
            ->toArray();

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

    private function getEmployeeStats(int $tenantId): array
    {
        $userT = Db::name('user')->getTable();
        $allocT = Db::name('mes_allocation')->getTable();
        $reportT = Db::name('mes_report')->getTable();

        $rows = Db::table($allocT . ' a')
            ->leftJoin($userT . ' u', 'a.user_id = u.id')
            ->leftJoin($reportT . ' r', 'r.allocation_id = a.id AND r.status = 1')
            ->where('a.tenant_id', $tenantId)
            ->field('a.user_id, COALESCE(u.nickname, CONCAT("用户#", a.user_id)) as user_name, COUNT(a.id) as total_allocations, SUM(a.quantity) as total_quantity, COALESCE(SUM(r.quantity), 0) as completed_quantity')
            ->group('a.user_id')
            ->select()
            ->toArray();

        $out = [];
        foreach ($rows as $r) {
            $total = (int) ($r['total_quantity'] ?? 0);
            $completed = (int) ($r['completed_quantity'] ?? 0);
            $out[] = [
                'user_name' => $r['user_name'] ?? '',
                'total_allocations' => (int) ($r['total_allocations'] ?? 0),
                'total_quantity' => $total,
                'completed_quantity' => $completed,
                'completion_rate' => $total > 0 ? round($completed / $total * 100, 1) : 0,
            ];
        }
        return $out;
    }

    private function getWorkorders(int $tenantId, int $limit): array
    {
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
            ->field('a.id,a.status,a.quantity,a.completed_quantity,o.order_no,m.name as model_name,p.name as process_name,COALESCE(u.nickname,u.username,CONCAT("用户#",a.user_id)) as user_name')
            ->order('a.status', 'asc')
            ->order('a.id', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();

        $statusMap = [0 => '待开始', 1 => '进行中', 2 => '已完成'];
        foreach ($rows as &$r) {
            $qty = (int) ($r['quantity'] ?? 0);
            $done = (int) ($r['completed_quantity'] ?? 0);
            $r['status_text'] = $statusMap[(int) ($r['status'] ?? 0)] ?? '';
            $r['progress'] = $qty > 0 ? round($done / $qty * 100, 1) : 0.0;
        }
        unset($r);
        return $rows;
    }

    private function getCapacityTop(int $tenantId, int $start, int $end, int $limit): array
    {
        $limit = max(5, min(50, $limit));
        $userT = Db::name('user')->getTable();
        $reportT = Db::name('mes_report')->getTable();

        $rows = Db::table($reportT . ' r')
            ->leftJoin($userT . ' u', 'r.user_id = u.id')
            ->where('r.tenant_id', $tenantId)
            ->where('r.status', 1)
            ->where('r.create_time', 'between', [$start, $end])
            ->field('r.user_id, COALESCE(u.nickname,u.username,CONCAT("用户#",r.user_id)) as user_name, SUM(r.quantity) as quantity, SUM(r.work_hours) as hours')
            ->group('r.user_id')
            ->order('quantity', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();

        foreach ($rows as &$r) {
            $r['quantity'] = (float) ($r['quantity'] ?? 0);
            $r['hours'] = (float) ($r['hours'] ?? 0);
        }
        unset($r);

        return $rows;
    }

    private function getOutputTrend(int $tenantId, int $days): array
    {
        $days = max(7, min(31, $days));
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $start = strtotime($date . ' 00:00:00');
            $end = strtotime($date . ' 23:59:59');
            $sum = (float) Db::name('mes_report')->where('tenant_id', $tenantId)->where('status', 1)->where('create_time', 'between', [$start, $end])->sum('quantity');
            $out[] = ['date' => $date, 'quantity' => $sum];
        }
        return $out;
    }

    private function getStockOverview(int $tenantId, int $now): array
    {
        $materialStock = (float) Db::name('mes_material')->where('tenant_id', $tenantId)->sum('stock');
        $productStock = (float) Db::name('mes_product_model')->where('tenant_id', $tenantId)->sum('stock');
        $wipRow = Db::name('mes_allocation')->where('tenant_id', $tenantId)->whereIn('status', [0, 1])->fieldRaw('SUM(GREATEST(quantity - completed_quantity,0)) as s')->find();
        $wip = (float) (($wipRow['s'] ?? 0));
        $warnQ = Db::name('mes_material')->where('tenant_id', $tenantId)->whereColumn('stock', '<', 'min_stock')->where('min_stock', '>', 0);
        $warningCount = (int) $warnQ->count();
        $warningList = $warnQ->field('id,name,code,stock,min_stock')->orderRaw('(min_stock - stock) DESC')->limit(8)->select()->toArray();
        foreach ($warningList as &$w) {
            $w['shortage'] = max(0, (float) ($w['min_stock'] ?? 0) - (float) ($w['stock'] ?? 0));
        }
        unset($w);

        $stockLogQ = Db::name('mes_stock_log')
            ->where('tenant_id', $tenantId)
            ->whereIn('business_type', ['shipment_out', 'production_out'])
            ->where('create_time', '>=', $now - 30 * 86400)
            ->where('change_quantity', '<', 0);
        $outbound30 = abs((float) $stockLogQ->sum('change_quantity'));
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

    private function getPurchaseOverview(int $tenantId): array
    {
        $mrp = $this->getMrpShortageList($tenantId, 8);
        $pendingCount = (int) Db::name('mes_purchase_request')->where('tenant_id', $tenantId)->where('status', 0)->count();
        return [
            'pending_count' => $pendingCount,
            'mrp_shortage_count' => count($mrp),
            'mrp_shortage_list' => $mrp,
        ];
    }

    private function getMrpShortageList(int $tenantId, int $limit): array
    {
        $limit = max(5, min(50, $limit));
        $orderIds = Db::name('mes_order')->where('tenant_id', $tenantId)->whereIn('status', [0, 1])->column('id');
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
        $materials = Db::name('mes_material')->where('tenant_id', $tenantId)->whereIn('id', $materialIds)->field('id,name,code,unit,stock')->select()->toArray();
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
            ];
        }
        usort($list, fn ($a, $b) => ($b['shortage'] <=> $a['shortage']));
        return array_slice($list, 0, $limit);
    }

    private function getQualityOverview(int $tenantId, int $now, int $todayStart, int $todayEnd): array
    {
        $start = $now - 30 * 86400;
        $pendingReports = (int) Db::name('mes_report')->where('tenant_id', $tenantId)->where('status', 0)->count();
        $todayBad = (int) Db::name('mes_report')->where('tenant_id', $tenantId)->where('status', 1)->where('quality_status', 2)->where('create_time', 'between', [$todayStart, $todayEnd])->sum('quantity');
        $totalQty = (float) Db::name('mes_report')->where('tenant_id', $tenantId)->where('status', 1)->where('create_time', '>=', $start)->sum('quantity');
        $qualifiedQty = (float) Db::name('mes_report')->where('tenant_id', $tenantId)->where('status', 1)->where('quality_status', 1)->where('create_time', '>=', $start)->sum('quantity');
        $firstPassRate = $totalQty > 0 ? round($qualifiedQty / $totalQty * 100, 1) : 0.0;
        $pendingCheckCount = (int) Db::name('mes_quality_check')->where('tenant_id', $tenantId)->where('status', 0)->count();

        $allocT = Db::name('mes_allocation')->getTable();
        $processT = Db::name('mes_process')->getTable();
        $reportT = Db::name('mes_report')->getTable();
        $defect = Db::table($reportT . ' r')
            ->join($allocT . ' a', 'r.allocation_id = a.id')
            ->leftJoin($processT . ' p', 'a.process_id = p.id')
            ->where('r.tenant_id', $tenantId)
            ->where('r.status', 1)
            ->where('r.quality_status', 2)
            ->where('r.create_time', '>=', $start)
            ->field('a.process_id, COALESCE(p.name, CONCAT("工序#",a.process_id)) as process_name, SUM(r.quantity) as bad_quantity')
            ->group('a.process_id')
            ->order('bad_quantity', 'desc')
            ->limit(8)
            ->select()
            ->toArray();

        foreach ($defect as &$d) {
            $d['bad_quantity'] = (float) ($d['bad_quantity'] ?? 0);
        }
        unset($d);

        return [
            'pending_reports' => $pendingReports,
            'today_bad' => $todayBad,
            'first_pass_rate' => $firstPassRate,
            'pending_check_count' => $pendingCheckCount,
            'defect_by_process' => $defect,
        ];
    }

    private function buildAlerts(array $counts): array
    {
        $list = [];
        $total = 0;
        $map = [
            ['key' => 'pending_reports', 'title' => '待审核报工', 'level' => 'warning'],
            ['key' => 'today_bad', 'title' => '今日不良', 'level' => 'danger'],
            ['key' => 'stock_warning', 'title' => '库存预警', 'level' => 'warning'],
            ['key' => 'mrp_shortage', 'title' => 'MRP缺料', 'level' => 'danger'],
        ];
        foreach ($map as $it) {
            $cnt = (int) ($counts[$it['key']] ?? 0);
            if ($cnt <= 0) {
                continue;
            }
            $total += $cnt;
            $list[] = ['type' => $it['title'], 'count' => $cnt, 'level' => $it['level']];
        }
        return ['total' => $total, 'list' => $list];
    }
}

