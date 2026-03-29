<?php
declare(strict_types=1);

namespace app\common\lib\mes;

use app\admin\model\mes\ProcessRouteModel;
use think\facade\Db;

class PieceScheduler
{
    public static function generate(int $tenantId, string $startDate, int $days, bool $reset = true): array
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

        $plans = Db::name('mes_production_plan')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [0, 1, 3])
            ->orderRaw('IFNULL(planned_end_time, 2147483647) asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        if ($reset) {
            Db::name('mes_schedule_task')
                ->where('tenant_id', $tenantId)
                ->where('status', 0)
                ->whereBetween('work_date', [$startYmd, $endYmd])
                ->delete();
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

            foreach ($steps as $step) {
                $processId = (int) $step['process_id'];
                $processRemain = $remain;
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
