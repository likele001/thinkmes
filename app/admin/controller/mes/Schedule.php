<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ScheduleTaskModel;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\AllocationQrcodeModel;
use app\admin\model\mes\ProductionPlanModel;
use app\common\lib\mes\PieceScheduler;
use think\facade\Db;
use think\facade\View;
use think\Response;

class Schedule extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '智能排产(计件)');
            return $this->fetchWithLayout('mes/schedule/index');
        }

        $limit = max(1, min(200, (int) $this->request->get('limit', 50)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = ScheduleTaskModel::with(['plan', 'order', 'model', 'process', 'user'])->order('work_date', 'asc')->order('id', 'asc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) $query->where('tenant_id', $tenantParam);
        }

        $batchId = trim((string) $this->request->get('batch_id', ''));
        if ($batchId !== '') $query->where('batch_id', $batchId);
        $date = trim((string) $this->request->get('work_date', ''));
        if ($date !== '') $query->where('work_date', $date);
        $startDate = trim((string) $this->request->get('start_date', ''));
        $days = (int) $this->request->get('days', 0);
        $filterDate = (int) $this->request->get('filter_date', 0);
        if ($batchId === '' && $date === '' && $filterDate === 1 && $startDate !== '' && $days > 0) {
            $startTs = strtotime($startDate . ' 00:00:00');
            if ($startTs) {
                $days = max(1, min(60, $days));
                $endDate = date('Y-m-d', $startTs + ($days - 1) * 86400);
                $query->where('work_date', '>=', $startDate)->where('work_date', '<=', $endDate);
            }
        }
        $status = $this->request->get('status');
        if ($status !== null && $status !== '') $query->where('status', (int) $status);

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['order_no'] = $row['order']['order_no'] ?? '-';
            $row['plan_code'] = $row['plan']['plan_code'] ?? '-';
            $row['model_name'] = $row['model']['name'] ?? '-';
            $row['process_name'] = $row['process']['name'] ?? '-';
            $row['user_name'] = $row['user']['nickname'] ?? ($row['user']['username'] ?? '-');
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function generate(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) $tenantId = (int) $this->request->post('tenant_id', 0);
        $startDate = trim((string) $this->request->post('start_date', date('Y-m-d')));
        $days = (int) $this->request->post('days', 7);
        $reset = (int) $this->request->post('reset', 1) ? true : false;
        $enforceUpstream = (int) $this->request->post('enforce_upstream', 0) ? true : false;

        $planIds = [];
        $planIdsRaw = $this->request->post('plan_ids', '');
        if (is_array($planIdsRaw)) {
            $planIds = array_values(array_unique(array_filter(array_map('intval', $planIdsRaw), fn($id) => $id > 0)));
        } elseif (is_string($planIdsRaw) && $planIdsRaw !== '') {
            $planIds = array_values(array_unique(array_filter(array_map('intval', explode(',', str_replace('，', ',', $planIdsRaw))), fn($id) => $id > 0)));
        }

        $r = PieceScheduler::generate($tenantId, $startDate, $days, $reset, $enforceUpstream, $planIds);
        if (!($r['ok'] ?? false)) return $this->error((string) ($r['error'] ?? '生成失败'));
        $batchId = (string) ($r['batch_id'] ?? '');
        if ($batchId !== '') {
            try {
                $planRows = Db::name('mes_schedule_task')
                    ->where('tenant_id', $tenantId)
                    ->where('batch_id', $batchId)
                    ->field('plan_id, COUNT(*) as task_count, SUM(quantity) as qty_sum')
                    ->group('plan_id')
                    ->select()
                    ->toArray();
                $planIds = array_values(array_unique(array_filter(array_map(fn ($x) => (int) ($x['plan_id'] ?? 0), $planRows))));
                $planCodeMap = [];
                if ($planIds) {
                    $codes = Db::name('mes_production_plan')
                        ->where('tenant_id', $tenantId)
                        ->whereIn('id', $planIds)
                        ->column('plan_code', 'id');
                    foreach ($codes as $pid => $code) {
                        $planCodeMap[(int) $pid] = (string) $code;
                    }
                }
                $planSummary = [];
                foreach ($planRows as $row) {
                    $pid = (int) ($row['plan_id'] ?? 0);
                    if ($pid <= 0) continue;
                    $planSummary[] = [
                        'plan_id' => $pid,
                        'plan_code' => $planCodeMap[$pid] ?? ('计划#' . $pid),
                        'task_count' => (int) ($row['task_count'] ?? 0),
                        'quantity' => (int) ($row['qty_sum'] ?? 0),
                    ];
                }
                $r['plan_summary'] = $planSummary;

                $uns = $r['unscheduled'] ?? [];
                if (is_array($uns) && $uns) {
                    $unsPlanIds = array_values(array_unique(array_filter(array_map(fn ($x) => (int) (($x['plan_id'] ?? 0)), $uns))));
                    if ($unsPlanIds) {
                        $codes = Db::name('mes_production_plan')
                            ->where('tenant_id', $tenantId)
                            ->whereIn('id', $unsPlanIds)
                            ->column('plan_code', 'id');
                        foreach ($uns as &$it) {
                            $pid = (int) ($it['plan_id'] ?? 0);
                            if ($pid > 0 && isset($codes[$pid])) $it['plan_code'] = (string) $codes[$pid];
                        }
                        unset($it);
                        $r['unscheduled'] = $uns;
                    }
                }
            } catch (\Throwable $e) {
            }
        }
        return $this->success('已生成', $r);
    }

    public function ganttData(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) $tenantId = (int) $this->request->get('tenant_id', 0);
        if ($tenantId <= 0) return $this->error('tenant_id required');
        $batchId = trim((string) $this->request->get('batch_id', ''));
        if ($batchId === '') return $this->error('batch_id required');

        $startDate = trim((string) $this->request->get('start_date', ''));
        $days = (int) $this->request->get('days', 0);
        $dates = [];
        if ($startDate !== '' && $days > 0) {
            $startTs = strtotime($startDate . ' 00:00:00');
            if ($startTs) {
                $days = max(1, min(60, $days));
                for ($i = 0; $i < $days; $i++) $dates[] = date('Y-m-d', $startTs + $i * 86400);
            }
        }

        $tasks = Db::name('mes_schedule_task')
            ->where('tenant_id', $tenantId)
            ->where('batch_id', $batchId)
            ->whereIn('status', [0, 1])
            ->order('work_date', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        if (!$dates) {
            $min = null;
            $max = null;
            foreach ($tasks as $t) {
                $d = (string) ($t['work_date'] ?? '');
                if ($d === '') continue;
                if ($min === null || $d < $min) $min = $d;
                if ($max === null || $d > $max) $max = $d;
            }
            if ($min && $max) {
                $startTs = strtotime($min . ' 00:00:00');
                $endTs = strtotime($max . ' 00:00:00');
                if ($startTs && $endTs && $endTs >= $startTs) {
                    $span = (int) floor(($endTs - $startTs) / 86400);
                    $span = max(0, min(60, $span));
                    for ($i = 0; $i <= $span; $i++) $dates[] = date('Y-m-d', $startTs + $i * 86400);
                }
            }
        }

        $userIds = [];
        $processIds = [];
        $planIds = [];
        $orderIds = [];
        $modelIds = [];
        foreach ($tasks as $t) {
            $userIds[] = (int) ($t['user_id'] ?? 0);
            $processIds[] = (int) ($t['process_id'] ?? 0);
            $planIds[] = (int) ($t['plan_id'] ?? 0);
            $orderIds[] = (int) ($t['order_id'] ?? 0);
            $modelIds[] = (int) ($t['model_id'] ?? 0);
        }
        $userIds = array_values(array_unique(array_filter($userIds)));
        $processIds = array_values(array_unique(array_filter($processIds)));
        $planIds = array_values(array_unique(array_filter($planIds)));
        $orderIds = array_values(array_unique(array_filter($orderIds)));
        $modelIds = array_values(array_unique(array_filter($modelIds)));

        $userMap = [];
        if ($userIds) {
            $rows = Db::name('user')->whereIn('id', $userIds)->field('id,username,nickname')->select()->toArray();
            foreach ($rows as $r) {
                $id = (int) ($r['id'] ?? 0);
                if ($id <= 0) continue;
                $userMap[$id] = (string) ($r['nickname'] ?? ($r['username'] ?? ''));
            }
        }
        $processMap = [];
        if ($processIds) {
            $rows = Db::name('mes_process')->where('tenant_id', $tenantId)->whereIn('id', $processIds)->field('id,name')->select()->toArray();
            foreach ($rows as $r) {
                $id = (int) ($r['id'] ?? 0);
                if ($id <= 0) continue;
                $processMap[$id] = (string) ($r['name'] ?? '');
            }
        }
        $planMap = [];
        if ($planIds) {
            $rows = Db::name('mes_production_plan')->where('tenant_id', $tenantId)->whereIn('id', $planIds)->field('id,plan_code')->select()->toArray();
            foreach ($rows as $r) {
                $id = (int) ($r['id'] ?? 0);
                if ($id <= 0) continue;
                $planMap[$id] = (string) ($r['plan_code'] ?? '');
            }
        }
        $orderMap = [];
        if ($orderIds) {
            $rows = Db::name('mes_order')->where('tenant_id', $tenantId)->whereIn('id', $orderIds)->field('id,order_no')->select()->toArray();
            foreach ($rows as $r) {
                $id = (int) ($r['id'] ?? 0);
                if ($id <= 0) continue;
                $orderMap[$id] = (string) ($r['order_no'] ?? '');
            }
        }
        $modelMap = [];
        if ($modelIds) {
            $rows = Db::name('mes_product_model')->where('tenant_id', $tenantId)->whereIn('id', $modelIds)->field('id,name')->select()->toArray();
            foreach ($rows as $r) {
                $id = (int) ($r['id'] ?? 0);
                if ($id <= 0) continue;
                $modelMap[$id] = (string) ($r['name'] ?? '');
            }
        }

        $dateSet = [];
        foreach ($dates as $d) $dateSet[$d] = true;

        $byUser = [];
        $byProcess = [];
        foreach ($tasks as $t) {
            $d = (string) ($t['work_date'] ?? '');
            if ($d === '' || ($dateSet && !isset($dateSet[$d]))) continue;
            $uid = (int) ($t['user_id'] ?? 0);
            $pid = (int) ($t['process_id'] ?? 0);
            $qty = (int) ($t['quantity'] ?? 0);
            if ($uid <= 0 || $pid <= 0 || $qty <= 0) continue;
            $planId = (int) ($t['plan_id'] ?? 0);
            $orderId = (int) ($t['order_id'] ?? 0);
            $modelId = (int) ($t['model_id'] ?? 0);

            $item = [
                'plan_id' => $planId,
                'plan_code' => $planMap[$planId] ?? '',
                'order_id' => $orderId,
                'order_no' => $orderMap[$orderId] ?? '',
                'model_id' => $modelId,
                'model_name' => $modelMap[$modelId] ?? '',
                'process_id' => $pid,
                'process_name' => $processMap[$pid] ?? '',
                'user_id' => $uid,
                'user_name' => $userMap[$uid] ?? '',
                'quantity' => $qty,
            ];

            if (!isset($byUser[$uid])) $byUser[$uid] = ['id' => $uid, 'name' => $userMap[$uid] ?? ('#' . $uid), 'cells' => []];
            if (!isset($byUser[$uid]['cells'][$d])) $byUser[$uid]['cells'][$d] = ['total' => 0, 'items' => []];
            $byUser[$uid]['cells'][$d]['total'] += $qty;
            $byUser[$uid]['cells'][$d]['items'][] = $item;

            if (!isset($byProcess[$pid])) $byProcess[$pid] = ['id' => $pid, 'name' => $processMap[$pid] ?? ('#' . $pid), 'cells' => []];
            if (!isset($byProcess[$pid]['cells'][$d])) $byProcess[$pid]['cells'][$d] = ['total' => 0, 'items' => []];
            $byProcess[$pid]['cells'][$d]['total'] += $qty;
            $byProcess[$pid]['cells'][$d]['items'][] = $item;
        }

        $byUser = array_values($byUser);
        usort($byUser, fn ($a, $b) => strcmp((string) $a['name'], (string) $b['name']));
        $byProcess = array_values($byProcess);
        usort($byProcess, fn ($a, $b) => strcmp((string) $a['name'], (string) $b['name']));

        return $this->success('', [
            'batch_id' => $batchId,
            'dates' => $dates,
            'by_user' => $byUser,
            'by_process' => $byProcess,
        ]);
    }

    public function publish(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) $tenantId = (int) $this->request->post('tenant_id', 0);
        $batchId = trim((string) $this->request->post('batch_id', ''));
        if ($tenantId <= 0) return $this->error('tenant_id required');
        if ($batchId === '') return $this->error('batch_id required');

        $tasks = Db::name('mes_schedule_task')
            ->where('tenant_id', $tenantId)
            ->where('batch_id', $batchId)
            ->where('status', 0)
            ->select()
            ->toArray();
        if (!$tasks) return $this->error('没有可下发的排产任务');

        $planIds = [];
        foreach ($tasks as $t) {
            $pid = (int) ($t['plan_id'] ?? 0);
            if ($pid > 0) {
                $planIds[] = $pid;
            }
        }
        $planIds = array_values(array_unique($planIds));

        $groups = [];
        foreach ($tasks as $t) {
            $orderId = (int) ($t['order_id'] ?? 0);
            $planId = (int) ($t['plan_id'] ?? 0);
            $modelId = (int) ($t['model_id'] ?? 0);
            $processId = (int) ($t['process_id'] ?? 0);
            $userId = (int) ($t['user_id'] ?? 0);
            $qty = (int) ($t['quantity'] ?? 0);
            if ($orderId <= 0 || $modelId <= 0 || $processId <= 0 || $userId <= 0 || $qty <= 0) continue;
            $k = $orderId . ':' . $planId . ':' . $modelId . ':' . $processId . ':' . $userId;
            if (!isset($groups[$k])) {
                $groups[$k] = ['qty' => 0, 'min' => null, 'max' => null];
            }
            $groups[$k]['qty'] += $qty;
            $d = (string) ($t['work_date'] ?? '');
            if ($d !== '') {
                if ($groups[$k]['min'] === null || $d < $groups[$k]['min']) $groups[$k]['min'] = $d;
                if ($groups[$k]['max'] === null || $d > $groups[$k]['max']) $groups[$k]['max'] = $d;
            }
        }
        if (!$groups) return $this->error('排产任务数据异常');

        $now = time();
        $hasPlannedStart = $this->hasTableColumn('mes_allocation', 'planned_start_time');
        $hasPlannedEnd = $this->hasTableColumn('mes_allocation', 'planned_end_time');
        $hasRemark = $this->hasTableColumn('mes_allocation', 'remark');
        $hasAllocationCode = $this->hasTableColumn('mes_allocation', 'allocation_code');
        $allocationCodes = [];
        $allocRows = [];
        foreach ($groups as $k => $g) {
            [$orderId, $planId, $modelId, $processId, $userId] = array_map('intval', explode(':', $k));
            $minD = $g['min'] ?? null;
            $maxD = $g['max'] ?? null;
            $pst = $minD ? strtotime($minD . ' 00:00:00') : null;
            $pet = $maxD ? strtotime($maxD . ' 23:59:59') : null;
            $row = [
                'tenant_id' => $tenantId,
                'plan_id' => $planId ?: null,
                'order_id' => $orderId,
                'model_id' => $modelId,
                'process_id' => $processId,
                'user_id' => $userId,
                'quantity' => (int) ($g['qty'] ?? 0),
                'completed_quantity' => 0,
                'status' => 0,
                'create_time' => $now,
                'update_time' => $now,
            ];
            if ($hasAllocationCode) {
                $code = AllocationModel::generateAllocationCode();
                $row['allocation_code'] = $code;
                $allocationCodes[] = $code;
            }
            if ($hasPlannedStart) $row['planned_start_time'] = $pst ?: null;
            if ($hasPlannedEnd) $row['planned_end_time'] = $pet ?: null;
            if ($hasRemark) $row['remark'] = 'batch:' . $batchId;
            $allocRows[] = $row;
        }

        Db::startTrans();
        try {
            // 先插入分工记录（insertAll 不返回ID，需要后续查询）
            Db::name('mes_allocation')->insertAll($allocRows);

            $insertQuery = Db::name('mes_allocation')
                ->where('tenant_id', $tenantId)
                ->where('create_time', $now);
            if ($hasAllocationCode && $allocationCodes) {
                $insertQuery->whereIn('allocation_code', $allocationCodes);
            } elseif ($hasRemark) {
                $insertQuery->where('remark', 'batch:' . $batchId);
            } else {
                $orderIds = array_values(array_unique(array_filter(array_map(fn ($r) => (int) ($r['order_id'] ?? 0), $allocRows))));
                $modelIds = array_values(array_unique(array_filter(array_map(fn ($r) => (int) ($r['model_id'] ?? 0), $allocRows))));
                $processIds = array_values(array_unique(array_filter(array_map(fn ($r) => (int) ($r['process_id'] ?? 0), $allocRows))));
                $userIds = array_values(array_unique(array_filter(array_map(fn ($r) => (int) ($r['user_id'] ?? 0), $allocRows))));
                if ($orderIds) $insertQuery->whereIn('order_id', $orderIds);
                if ($modelIds) $insertQuery->whereIn('model_id', $modelIds);
                if ($processIds) $insertQuery->whereIn('process_id', $processIds);
                if ($userIds) $insertQuery->whereIn('user_id', $userIds);
            }
            $insertedAllocations = $insertQuery->select()->toArray();

            $qrcodeCount = 0;
            // 为每条分工记录生成二维码和追溯条目
            $allocationCtrl = $this->app->make(Allocation::class);
            foreach ($insertedAllocations as $alloc) {
                $allocId = (int) ($alloc['id'] ?? 0);
                if ($allocId > 0) {
                    try {
                        $allocationCtrl->doGenerateQrcode($allocId, $tenantId);
                        $qrcodeCount++;
                    } catch (\Throwable $qrErr) {
                        // 二维码生成失败不影响整体下发流程
                        // 可以考虑记录日志但不中断事务
                    }
                }
            }

            // 更新排产任务状态为已下发
            Db::name('mes_schedule_task')
                ->where('tenant_id', $tenantId)
                ->where('batch_id', $batchId)
                ->where('status', 0)
                ->update(['status' => 1]);

            // 同步生产计划状态：待开始/暂停 -> 进行中
            if (!empty($planIds)) {
                $plans = ProductionPlanModel::where('tenant_id', $tenantId)
                    ->whereIn('id', $planIds)
                    ->whereIn('status', [0, 3])
                    ->select();
                foreach ($plans as $plan) {
                    if (!$plan) continue;
                    $plan->status = 1;
                    if (!(int) $plan->actual_start_time) {
                        $plan->actual_start_time = $now;
                    }
                    $plan->update_time = $now;
                    $plan->save();
                }
            }

            Db::commit();

            $this->pushAdminNotification(
                '排产下发成功',
                '批次：' . $batchId . '，分工：' . count($allocRows) . '，二维码：' . $qrcodeCount,
                'info',
                0,
                $tenantId
            );

            try {
                $allocIds = array_values(array_unique(array_filter(array_map(fn ($a) => (int) ($a['id'] ?? 0), $insertedAllocations))));
                if ($allocIds) {
                    $infoRows = Db::name('mes_allocation')->alias('a')
                        ->leftJoin('mes_order o', 'o.id = a.order_id AND o.tenant_id = a.tenant_id')
                        ->leftJoin('mes_product_model m', 'm.id = a.model_id AND m.tenant_id = a.tenant_id')
                        ->leftJoin('mes_product p', 'p.id = m.product_id AND p.tenant_id = m.tenant_id')
                        ->leftJoin('mes_process pr', 'pr.id = a.process_id AND pr.tenant_id = a.tenant_id')
                        ->where('a.tenant_id', $tenantId)
                        ->whereIn('a.id', $allocIds)
                        ->field('a.id,a.user_id,a.quantity,o.order_no,p.name as product_name,m.name as model_name,pr.name as process_name')
                        ->select()
                        ->toArray();
                    foreach ($infoRows as $x) {
                        $uid = (int) ($x['user_id'] ?? 0);
                        if ($uid <= 0) continue;
                        $content = '订单：' . (string) ($x['order_no'] ?? '')
                            . '，产品：' . (string) ($x['product_name'] ?? '')
                            . '，型号：' . (string) ($x['model_name'] ?? '')
                            . '，工序：' . (string) ($x['process_name'] ?? '')
                            . '，数量：' . (string) ((int) ($x['quantity'] ?? 0));
                        $this->pushUserNotification($uid, '新分工下发', $content, 'info', $tenantId);
                    }
                }
            } catch (\Throwable $e) {
            }

            return $this->success('已下发', [
                'allocations' => count($allocRows),
                'qrcodes' => $qrcodeCount,
            ]);
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('下发失败：' . $e->getMessage());
        }
    }

    public function revoke(): Response
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) $tenantId = (int) $this->request->post('tenant_id', 0);
        $batchId = trim((string) $this->request->post('batch_id', ''));
        if ($tenantId <= 0) return $this->error('tenant_id required');
        if ($batchId === '') return $this->error('batch_id required');

        $hasRemark = $this->hasTableColumn('mes_allocation', 'remark');
        if (!$hasRemark) return $this->error('当前库缺少 mes_allocation.remark 字段，无法定位批次分工进行撤销');

        $allocIds = Db::name('mes_allocation')
            ->where('tenant_id', $tenantId)
            ->where('remark', 'batch:' . $batchId)
            ->column('id');
        $allocIds = array_values(array_unique(array_filter(array_map('intval', (array) $allocIds))));
        if (!$allocIds) return $this->error('未找到该批次生成的分工记录');

        $reportCount = (int) Db::name('mes_report')
            ->where('tenant_id', $tenantId)
            ->whereIn('allocation_id', $allocIds)
            ->count();
        if ($reportCount > 0) return $this->error('存在 ' . $reportCount . ' 条关联报工记录，无法撤销');

        $busyCount = (int) Db::name('mes_allocation')
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $allocIds)
            ->where(function ($q) {
                $q->where('status', '<>', 0)->whereOr('completed_quantity', '>', 0);
            })
            ->count();
        if ($busyCount > 0) return $this->error('存在已开始/已完成的分工记录，无法撤销');

        Db::startTrans();
        try {
            AllocationQrcodeModel::where('tenant_id', $tenantId)->whereIn('allocation_id', $allocIds)->delete();
            AllocationModel::where('tenant_id', $tenantId)->whereIn('id', $allocIds)->delete();
            Db::name('mes_schedule_task')
                ->where('tenant_id', $tenantId)
                ->where('batch_id', $batchId)
                ->where('status', 1)
                ->update(['status' => 3]);
            Db::commit();

            $this->pushAdminNotification('排产撤销下发', '批次：' . $batchId . '，撤销分工：' . count($allocIds), 'warning', 0, $tenantId);
            return $this->success('已撤销', ['allocations' => count($allocIds)]);
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('撤销失败：' . $e->getMessage());
        }
    }

    public function del(): Response
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) $tenantId = (int) $this->request->post('tenant_id', 0);
        $ids = $this->request->post('ids');
        if (empty($ids)) return $this->error('请选择记录');
        $ids = is_array($ids) ? $ids : explode(',', (string) $ids);
        $query = Db::name('mes_schedule_task')->where('tenant_id', $tenantId)->whereIn('id', $ids);
        $count = (int) $query->count();
        $query->delete();
        return $this->success('删除成功', ['count' => $count]);
    }
}
