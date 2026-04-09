<?php
declare(strict_types=1);

namespace app\common\lib\mes;

use app\admin\model\mes\ProcessRouteModel;
use think\facade\Db;

class PieceScheduler
{
    public static function generate(int $tenantId, string $startDate, int $days, bool $reset = true, bool $enforceUpstream = false, array $selectedPlanIds = []): array
    {
        $tenantId = (int) $tenantId;
        if ($tenantId <= 0) return ['ok' => false, 'error' => 'tenant_id required'];
        $days = max(1, min(60, (int) $days));
        $startTs = strtotime($startDate . ' 00:00:00');
        if (!$startTs) return ['ok' => false, 'error' => 'start_date invalid'];
        $endTs = $startTs + ($days - 1) * 86400;
        $startYmd = date('Y-m-d', $startTs);
        $endYmd = date('Y-m-d', $endTs);

        $batchId = 'SCH' . date('YmdHis') . random_int(1000, 9999);

        $capacities = Db::name('mes_user_process_capacity')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->where('capacity_per_day', '>', 0)
            ->select()
            ->toArray();
        $capMap = [];
        foreach ($capacities as $c) {
            $pid = (int) ($c['process_id'] ?? 0);
            $uid = (int) ($c['user_id'] ?? 0);
            $cap = (int) ($c['capacity_per_day'] ?? 0);
            if ($pid <= 0 || $uid <= 0 || $cap <= 0) continue;
            if (!isset($capMap[$pid])) $capMap[$pid] = [];
            $capMap[$pid][$uid] = $cap;
        }

        $selectedPlanIds = array_values(array_unique(array_filter(array_map('intval', $selectedPlanIds), fn($id) => $id > 0)));

        $planQuery = Db::name('mes_production_plan')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [0, 1, 3]);
        if ($selectedPlanIds) {
            $planQuery->whereIn('id', $selectedPlanIds);
        }
        $plans = $planQuery
            ->orderRaw('IFNULL(planned_end_time, 2147483647) asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        $planIds = array_values(array_unique(array_filter(array_map(fn ($p) => (int) ($p['id'] ?? 0), $plans))));
        $completedByPlanProcess = [];
        if ($planIds) {
            $rows = Db::name('mes_report')
                ->alias('r')
                ->leftJoin('mes_allocation a', 'a.id = r.allocation_id AND a.tenant_id = r.tenant_id')
                ->where('r.tenant_id', $tenantId)
                ->where('r.status', 1)
                ->whereIn('a.plan_id', $planIds)
                ->field('a.plan_id,a.process_id,SUM(r.quantity) as qty')
                ->group('a.plan_id,a.process_id')
                ->select()
                ->toArray();
            foreach ($rows as $row) {
                $pid = (int) ($row['plan_id'] ?? 0);
                $pr = (int) ($row['process_id'] ?? 0);
                $qty = (int) ($row['qty'] ?? 0);
                if ($pid <= 0 || $pr <= 0 || $qty <= 0) continue;
                if (!isset($completedByPlanProcess[$pid])) $completedByPlanProcess[$pid] = [];
                $completedByPlanProcess[$pid][$pr] = ($completedByPlanProcess[$pid][$pr] ?? 0) + $qty;
            }
        }

        $allocatedPendingByPlanProcess = [];
        if ($planIds) {
            $rows = Db::name('mes_allocation')
                ->where('tenant_id', $tenantId)
                ->whereIn('plan_id', $planIds)
                ->field('plan_id,process_id,SUM(quantity) as qty,SUM(completed_quantity) as done')
                ->group('plan_id,process_id')
                ->select()
                ->toArray();
            foreach ($rows as $row) {
                $pid = (int) ($row['plan_id'] ?? 0);
                $pr = (int) ($row['process_id'] ?? 0);
                $qty = (int) ($row['qty'] ?? 0);
                $done = (int) ($row['done'] ?? 0);
                if ($pid <= 0 || $pr <= 0 || $qty <= 0) continue;
                $pending = max(0, $qty - $done);
                if ($pending <= 0) continue;
                if (!isset($allocatedPendingByPlanProcess[$pid])) $allocatedPendingByPlanProcess[$pid] = [];
                $allocatedPendingByPlanProcess[$pid][$pr] = ($allocatedPendingByPlanProcess[$pid][$pr] ?? 0) + $pending;
            }
        }

        if ($reset) {
            Db::name('mes_schedule_task')
                ->where('tenant_id', $tenantId)
                ->where('status', 0)
                ->whereBetween('work_date', [$startYmd, $endYmd])
                ->delete();
        }

        $scheduledByPlanProcess = [];
        if ($planIds) {
            $rows = Db::name('mes_schedule_task')
                ->where('tenant_id', $tenantId)
                ->whereIn('plan_id', $planIds)
                ->where('status', 0)
                ->whereBetween('work_date', [$startYmd, $endYmd])
                ->field('plan_id,process_id,SUM(quantity) as qty')
                ->group('plan_id,process_id')
                ->select()
                ->toArray();
            foreach ($rows as $row) {
                $pid = (int) ($row['plan_id'] ?? 0);
                $pr = (int) ($row['process_id'] ?? 0);
                $qty = (int) ($row['qty'] ?? 0);
                if ($pid <= 0 || $pr <= 0 || $qty <= 0) continue;
                if (!isset($scheduledByPlanProcess[$pid])) $scheduledByPlanProcess[$pid] = [];
                $scheduledByPlanProcess[$pid][$pr] = ($scheduledByPlanProcess[$pid][$pr] ?? 0) + $qty;
            }
        }

        $existing = Db::name('mes_schedule_task')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [0, 1])
            ->whereBetween('work_date', [$startYmd, $endYmd])
            ->select()
            ->toArray();
        $used = [];
        foreach ($existing as $t) {
            $pid = (int) ($t['process_id'] ?? 0);
            $uid = (int) ($t['user_id'] ?? 0);
            $d = (string) ($t['work_date'] ?? '');
            $q = (int) ($t['quantity'] ?? 0);
            if ($pid <= 0 || $uid <= 0 || $d === '' || $q <= 0) continue;
            $key = $pid . ':' . $uid . ':' . $d;
            $used[$key] = ($used[$key] ?? 0) + $q;
        }

        $tasks = [];
        $unscheduled = [];
        foreach ($plans as $p) {
            $planId = (int) ($p['id'] ?? 0);
            $orderId = (int) ($p['order_id'] ?? 0);
            $modelId = (int) ($p['model_id'] ?? 0);
            $total = (int) ($p['total_quantity'] ?? 0);
            $done = (int) ($p['completed_quantity'] ?? 0);
            $remain = max(0, $total - $done);
            if ($planId <= 0 || $orderId <= 0 || $modelId <= 0 || $remain <= 0) continue;

            $route = ProcessRouteModel::getRouteByModel($tenantId, $modelId);
            $steps = [];
            if ($route) {
                $text = trim((string) ($route->steps_json ?? ''));
                for ($di = 0; $di < 3; $di++) {
                    $prev = $text;
                    $text = html_entity_decode($text, ENT_QUOTES);
                    if ($text === $prev) break;
                }
                $raw = json_decode($text, true);
                if (is_array($raw)) {
                    foreach ($raw as $st) {
                        if (!is_array($st)) continue;
                        $pid = (int) ($st['process_id'] ?? 0);
                        if ($pid <= 0) continue;
                        $steps[] = [
                            'process_id' => $pid,
                            'step_no' => (int) ($st['step_no'] ?? 0),
                        ];
                    }
                }
            }
            if (!$steps) {
                $unscheduled[] = ['plan_id' => $planId, 'order_id' => $orderId, 'model_id' => $modelId, 'process_id' => 0, 'remain' => $remain, 'reason' => 'no_process_route'];
                continue;
            }
            usort($steps, fn ($a, $b) => ($a['step_no'] <=> $b['step_no']) ?: ($a['process_id'] <=> $b['process_id']));

            $stepProcessIds = array_map(fn ($s) => (int) $s['process_id'], $steps);
            foreach ($steps as $idx => $step) {
                $processId = (int) $step['process_id'];
                $completedCur = (int) (($completedByPlanProcess[$planId][$processId] ?? 0));
                $processNeedBase = max(0, $remain - $completedCur);
                $availableFromUpstream = $processNeedBase;
                if ($enforceUpstream && $idx > 0) {
                    $prevProcessId = (int) ($stepProcessIds[$idx - 1] ?? 0);
                    $completedPrev = (int) (($completedByPlanProcess[$planId][$prevProcessId] ?? 0));
                    $availableFromUpstream = max(0, $completedPrev - $completedCur);
                }
                $target = $enforceUpstream ? min($processNeedBase, $availableFromUpstream) : $processNeedBase;
                $allocatedPending = (int) (($allocatedPendingByPlanProcess[$planId][$processId] ?? 0));
                $target = max(0, $target - $allocatedPending);
                $alreadyScheduled = (int) (($scheduledByPlanProcess[$planId][$processId] ?? 0));
                $processRemain = max(0, $target - $alreadyScheduled);
                if ($processRemain <= 0) {
                    if ($enforceUpstream && $idx > 0 && $processNeedBase > 0 && $availableFromUpstream <= 0) {
                        $unscheduled[] = ['plan_id' => $planId, 'order_id' => $orderId, 'model_id' => $modelId, 'process_id' => $processId, 'remain' => $processNeedBase, 'reason' => 'waiting_upstream'];
                    }
                    continue;
                }
                $users = $capMap[$processId] ?? [];
                if (!$users) {
                    $unscheduled[] = ['plan_id' => $planId, 'order_id' => $orderId, 'model_id' => $modelId, 'process_id' => $processId, 'remain' => $processRemain, 'reason' => 'no_capacity'];
                    continue;
                }
                for ($i = 0; $i < $days && $processRemain > 0; $i++) {
                    $d = date('Y-m-d', $startTs + $i * 86400);
                    foreach ($users as $uid => $cap) {
                        if ($processRemain <= 0) break;
                        $key = $processId . ':' . $uid . ':' . $d;
                        $usedQty = (int) ($used[$key] ?? 0);
                        $avail = max(0, (int) $cap - $usedQty);
                        if ($avail <= 0) continue;
                        $assign = min($processRemain, $avail);
                        $tasks[] = [
                            'tenant_id' => $tenantId,
                            'batch_id' => $batchId,
                            'plan_id' => $planId,
                            'order_id' => $orderId,
                            'model_id' => $modelId,
                            'process_id' => $processId,
                            'user_id' => (int) $uid,
                            'work_date' => $d,
                            'quantity' => (int) $assign,
                            'status' => 0,
                            'create_time' => time(),
                        ];
                        $used[$key] = $usedQty + $assign;
                        $processRemain -= $assign;
                    }
                }
                if ($processRemain > 0) {
                    $unscheduled[] = ['plan_id' => $planId, 'order_id' => $orderId, 'model_id' => $modelId, 'process_id' => $processId, 'remain' => $processRemain, 'reason' => 'capacity_insufficient'];
                }
            }
        }

        if ($tasks) {
            Db::name('mes_schedule_task')->insertAll($tasks);
        }

        return [
            'ok' => true,
            'batch_id' => $batchId,
            'start_date' => $startYmd,
            'end_date' => $endYmd,
            'tasks' => count($tasks),
            'unscheduled' => $unscheduled,
        ];
    }
}
