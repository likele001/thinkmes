<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\model\UserModel;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\ReportMediaModel;
use app\admin\model\mes\WageModel;
use app\admin\model\mes\ProcessPriceModel;
use app\admin\model\mes\TraceCodeModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 员工端 API（工人小程序）
 * 
 * 所有接口需 UserAuth 中间件
 * 按 tenant_id 隔离数据
 */
class Mesuser extends BaseController
{
    /**
     * 获取当前用户ID
     */
    protected function getUserId(): int
    {
        return (int) ($this->request->userId ?? 0);
    }

    /**
     * 获取当前租户ID
     */
    protected function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    /**
     * 仪表盘数据：今日任务数、报工数、工资、待审核、未读通知
     * GET /api/worker/dashboard
     */
    public function dashboard(): Response
    {
        $userId = $this->getUserId();
        $tenantId = $this->getTenantId();

        $todayStart = strtotime(date('Y-m-d 00:00:00'));
        $todayEnd = time();
        $todayDate = date('Y-m-d');

        // 今日任务数（今日分配的）
        $todayTaskCount = (int) AllocationModel::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('create_time', '>=', $todayStart)
            ->where('create_time', '<=', $todayEnd)
            ->count();

        // 今日报工数量
        $todayReportQuantity = (int) Db::name('mes_report')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('create_time', '>=', $todayStart)
            ->where('create_time', '<=', $todayEnd)
            ->sum('quantity');

        // 今日工资
        $todayWage = (float) Db::name('mes_wage')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('work_date', $todayDate)
            ->sum('total_wage');

        // 待审核数
        $pendingReports = (int) Db::name('mes_report')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 0)
            ->count();

        // 未读通知（使用系统消息或简单实现）
        $unreadNotices = 0;

        // 最近任务（进行中的）
        $recentTasks = AllocationModel::with(['order', 'model.product', 'process'])
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', '<>', 2)
            ->order('id', 'desc')
            ->limit(5)
            ->select();

        $allocationIds = $recentTasks->column('id');
        $reportedMap = [];
        if ($allocationIds) {
            $rows = Db::name('mes_report')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->whereIn('allocation_id', $allocationIds)
                ->group('allocation_id')
                ->column('SUM(quantity) as qty', 'allocation_id');
            foreach ($rows as $aid => $qty) {
                $reportedMap[(int) $aid] = (int) $qty;
            }
        }

        $tasks = [];
        foreach ($recentTasks as $a) {
            $order = $a->order;
            $model = $a->model;
            $product = $model ? $model->product : null;
            $process = $a->process;
            $assignQty = (int) $a->quantity;
            $reportedQty = (int) ($reportedMap[(int) $a->id] ?? 0);
            $tasks[] = [
                'id'                => (int) $a->id,
                'order_no'          => $order ? (string) ($order->order_no ?? '') : '',
                'product_name'      => $product ? (string) ($product->name ?? '') : '',
                'model_name'        => $model ? (string) ($model->name ?? '') : '',
                'process_name'      => $process ? (string) ($process->name ?? '') : '',
                'quantity'          => $assignQty,
                'reported_quantity' => $reportedQty,
                'remaining_quantity'=> max(0, $assignQty - $reportedQty),
                'status'            => (int) $a->status,
            ];
        }

        return $this->success('', [
            'metrics' => [
                'today_task_count'     => $todayTaskCount,
                'today_report_quantity'=> $todayReportQuantity,
                'today_wage'           => number_format($todayWage, 2, '.', ''),
                'pending_reports'      => $pendingReports,
                'unread_notices'       => $unreadNotices,
            ],
            'tasks' => $tasks,
        ]);
    }

    /**
     * 获取任务列表（当前工人的分工分配）
     * GET /api/worker/taskInfo
     */
    public function taskInfo(): Response
    {
        $userId = $this->getUserId();
        $tenantId = $this->getTenantId();

        $status = $this->request->get('status');
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));

        $query = AllocationModel::with(['order', 'model.product', 'process'])
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId);

        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->order('status', 'asc')->order('id', 'desc')
            ->page($page, $limit)
            ->select();

        // 批量获取已报工数
        $allocationIds = $list->column('id');
        $reportedMap = [];
        if ($allocationIds) {
            $rows = Db::name('mes_report')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->whereIn('allocation_id', $allocationIds)
                ->group('allocation_id')
                ->column('SUM(quantity) as qty', 'allocation_id');
            foreach ($rows as $aid => $qty) {
                $reportedMap[(int) $aid] = (int) $qty;
            }
        }

        // 单条查询模式
        $allocationId = $this->request->get('allocation_id');
        if ($allocationId !== null && $allocationId !== '') {
            $single = $query->where('id', (int) $allocationId)->find();
            if ($single) {
                $a = $single;
                $order = $a->order;
                $model = $a->model;
                $product = $model ? $model->product : null;
                $process = $a->process;
                $assignQty = (int) $a->quantity;
                $reportedQty = (int) ($reportedMap[(int) $a->id] ?? 0);

                // 获取追溯码
                $traceCodes = TraceCodeModel::where('tenant_id', $tenantId)
                    ->where('allocation_id', (int) $allocationId)
                    ->where('status', 1)
                    ->where('report_id', 0)
                    ->order('id', 'asc')
                    ->column('item_no');

                return $this->success('', [
                    'id'                 => (int) $a->id,
                    'order_no'           => $order ? (string) ($order->order_no ?? '') : '',
                    'product_name'       => $product ? (string) ($product->name ?? '') : '',
                    'model_name'         => $model ? (string) ($model->name ?? '') : '',
                    'process_name'       => $process ? (string) ($process->name ?? '') : '',
                    'quantity'           => $assignQty,
                    'reported_quantity'  => $reportedQty,
                    'remaining_quantity' => max(0, $assignQty - $reportedQty),
                    'status'             => (int) $a->status,
                    'trace_codes'        => $traceCodes,
                ]);
            }
            return $this->error('任务不存在');
        }

        $items = [];
        foreach ($list as $a) {
            $order = $a->order;
            $model = $a->model;
            $product = $model ? $model->product : null;
            $process = $a->process;
            $assignQty = (int) $a->quantity;
            $reportedQty = (int) ($reportedMap[(int) $a->id] ?? 0);
            $items[] = [
                'id'                 => (int) $a->id,
                'order_no'           => $order ? (string) ($order->order_no ?? '') : '',
                'product_name'       => $product ? (string) ($product->name ?? '') : '',
                'model_name'         => $model ? (string) ($model->name ?? '') : '',
                'process_name'       => $process ? (string) ($process->name ?? '') : '',
                'quantity'           => $assignQty,
                'reported_quantity'  => $reportedQty,
                'remaining_quantity' => max(0, $assignQty - $reportedQty),
                'status'             => (int) $a->status,
                'create_time'        => (int) $a->create_time,
            ];
        }

        return $this->success('', ['total' => $total, 'list' => $items]);
    }

    /**
     * 提交报工
     * POST /api/worker/report
     */
    public function report(): Response
    {
        $userId = $this->getUserId();
        $tenantId = $this->getTenantId();

        $allocationId = (int) $this->request->post('allocation_id', 0);
        $quantity = (int) $this->request->post('quantity', 0);
        $workHours = (float) $this->request->post('work_hours', 0);
        $workType = trim((string) $this->request->post('work_type', 'piece'));
        $remark = trim((string) $this->request->post('remark', ''));
        $images = $this->request->post('images', []);
        $traceCodeIds = $this->request->post('trace_code_ids', []);
        $itemNos = $this->request->post('item_nos', []);

        if ($allocationId <= 0) {
            return $this->error('请选择任务');
        }
        if ($workType === 'hour') $workType = 'time';
        if ($workType !== 'piece' && $workType !== 'time') $workType = 'piece';

        // 查询分配
        $allocation = AllocationModel::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->find($allocationId);
        if (!$allocation) {
            return $this->error('任务不存在');
        }

        if (is_string($itemNos) && $itemNos !== '') {
            $decoded = json_decode($itemNos, true);
            if (is_array($decoded)) $itemNos = $decoded;
        }
        if (!is_array($itemNos)) $itemNos = [];
        $itemNos = array_values(array_unique(array_filter(array_map(function ($v) {
            return trim((string) $v);
        }, $itemNos), function ($v) {
            return $v !== '';
        })));

        if (is_string($traceCodeIds) && $traceCodeIds !== '') {
            $decoded = json_decode($traceCodeIds, true);
            if (is_array($decoded)) $traceCodeIds = $decoded;
        }
        if (!is_array($traceCodeIds)) $traceCodeIds = [];
        $traceCodeIds = array_values(array_filter(array_map(function ($v) {
            return (int) $v;
        }, $traceCodeIds), function ($v) {
            return $v > 0;
        }));

        if (empty($itemNos) && !empty($traceCodeIds)) {
            $itemNos = TraceCodeModel::where('tenant_id', $tenantId)
                ->where('allocation_id', $allocationId)
                ->where('status', 1)
                ->where('report_id', 0)
                ->whereIn('id', $traceCodeIds)
                ->order('id', 'asc')
                ->column('item_no');
            $itemNos = array_values(array_unique(array_filter(array_map(function ($v) {
                return trim((string) $v);
            }, $itemNos), function ($v) {
                return $v !== '';
            })));
        }

        $traceEnabled = (int) TraceCodeModel::where('tenant_id', $tenantId)
            ->where('allocation_id', $allocationId)
            ->where('status', 1)
            ->count() > 0;

        if ($workType === 'piece') {
            if (!empty($itemNos)) {
                $quantity = count($itemNos);
            } else {
                if ($traceEnabled) {
                    return $this->error('请选择产品编号');
                }
                if ($quantity <= 0) {
                    return $this->error('报工数量必须大于0');
                }
            }
            if ($quantity <= 0) {
                return $this->error('报工数量必须大于0');
            }
        } else {
            if ($workHours <= 0) {
                return $this->error('工时必须大于0');
            }
            $quantity = 0;
        }

        // 检查剩余数量
        $reportedQty = (int) Db::name('mes_report')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('allocation_id', $allocationId)
            ->sum('quantity');

        $remaining = (int) $allocation->quantity - $reportedQty;
        if ($workType === 'piece' && $quantity > $remaining) {
            return $this->error("超出剩余数量，最多可报 {$remaining} 件");
        }

        // 获取工序单价
        $processId = (int) $allocation->process_id;
        $modelId = (int) $allocation->model_id;
        $priceRow = ProcessPriceModel::where('tenant_id', $tenantId)
            ->where('model_id', $modelId)
            ->where('process_id', $processId)
            ->find();
        if (!$priceRow) {
            return $this->error('未设置该型号工序工价');
        }
        $unitPrice = $workType === 'piece' ? (float) ($priceRow->price ?? 0) : (float) ($priceRow->time_price ?? 0);
        if ($unitPrice <= 0) {
            return $this->error('工价未设置或为0');
        }

        $totalWage = $workType === 'piece' ? round($quantity * $unitPrice, 2) : round($workHours * $unitPrice, 2);
        $now = time();
        $workDate = date('Y-m-d');

        Db::startTrans();
        try {
            $reportIds = [];

            $saveMedia = function (int $reportId, array $urls) use ($tenantId, $now) {
                foreach ($urls as $url) {
                    $u = trim((string) $url);
                    if ($u === '') continue;
                    $media = new ReportMediaModel();
                    $media->tenant_id = $tenantId;
                    $media->report_id = $reportId;
                    $media->type = 'image';
                    $media->url = $u;
                    $media->create_time = $now;
                    $media->save();
                }
            };

            $createWage = function (int $reportId, int $qty, float $hours, float $unitPrice, float $total, string $workType) use ($tenantId, $userId, $allocationId, $workDate, $now) {
                $wage = new WageModel();
                $wage->tenant_id = $tenantId;
                $wage->user_id = $userId;
                $wage->report_id = $reportId;
                $wage->allocation_id = $allocationId;
                $wage->work_type = $workType;
                $wage->quantity = $qty;
                $wage->work_hours = $hours;
                $wage->unit_price = $unitPrice;
                $wage->total_wage = $total;
                $wage->work_date = $workDate;
                $wage->create_time = $now;
                $wage->save();
            };

            if ($workType === 'piece' && !empty($itemNos)) {
                $isListImages = is_array($images) && array_keys($images) === range(0, count($images) - 1);
                foreach ($itemNos as $itemNo) {
                    $report = new ReportModel();
                    $report->tenant_id = $tenantId;
                    $report->allocation_id = $allocationId;
                    $report->user_id = $userId;
                    $report->quantity = 1;
                    $report->work_hours = 0;
                    $report->work_type = 'piece';
                    $report->item_nos = json_encode([$itemNo], JSON_UNESCAPED_UNICODE);
                    $report->unit_price = $unitPrice;
                    $report->wage = $unitPrice;
                    $report->remark = $remark;
                    $report->status = 0;
                    $report->create_time = $now;
                    $report->update_time = $now;
                    $report->save();

                    $reportId = (int) $report->id;
                    $reportIds[] = $reportId;

                    TraceCodeModel::where('tenant_id', $tenantId)
                        ->where('allocation_id', $allocationId)
                        ->where('item_no', $itemNo)
                        ->where('report_id', 0)
                        ->update(['report_id' => $reportId, 'update_time' => $now]);

                    $urls = [];
                    if ($isListImages) {
                        $urls = $images;
                    } elseif (is_array($images) && isset($images[$itemNo]) && is_array($images[$itemNo])) {
                        $urls = $images[$itemNo];
                    }
                    $saveMedia($reportId, is_array($urls) ? $urls : []);
                    $createWage($reportId, 1, 0, $unitPrice, $unitPrice, 'piece');
                }
            } else {
                $report = new ReportModel();
                $report->tenant_id = $tenantId;
                $report->allocation_id = $allocationId;
                $report->user_id = $userId;
                $report->quantity = $quantity;
                $report->work_hours = $workType === 'time' ? $workHours : 0;
                $report->work_type = $workType;
                $report->unit_price = $unitPrice;
                $report->wage = $totalWage;
                $report->remark = $remark;
                $report->status = 0;
                $report->create_time = $now;
                $report->update_time = $now;
                $report->save();

                $reportId = (int) $report->id;
                $reportIds[] = $reportId;

                if (is_array($images) && array_keys($images) === range(0, count($images) - 1)) {
                    $saveMedia($reportId, $images);
                }

                if ($workType === 'piece' && !empty($traceCodeIds)) {
                    TraceCodeModel::where('tenant_id', $tenantId)
                        ->where('allocation_id', $allocationId)
                        ->whereIn('id', $traceCodeIds)
                        ->where('report_id', 0)
                        ->update(['report_id' => $reportId, 'update_time' => $now]);
                }

                $createWage($reportId, $quantity, $workType === 'time' ? $workHours : 0, $unitPrice, $totalWage, $workType);
            }

            if ($workType === 'piece') {
                $newCompleted = $reportedQty + $quantity;
                $allocUpdate = ['completed_quantity' => $newCompleted, 'update_time' => $now];
                if ($newCompleted >= (int) $allocation->quantity) {
                    $allocUpdate['status'] = 2;
                    $allocUpdate['actual_end_time'] = $now;
                } elseif ((int) $allocation->status === 0) {
                    $allocUpdate['status'] = 1;
                    $allocUpdate['actual_start_time'] = $now;
                }
                AllocationModel::where('id', $allocationId)->update($allocUpdate);
            }

            Db::commit();
            return $this->success('报工成功', ['ids' => $reportIds, 'wage' => number_format((float) $totalWage, 2, '.', '')]);
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('报工失败：' . $e->getMessage());
        }
    }

    /**
     * 获取报工记录列表
     * GET /api/worker/reports
     */
    public function reports(): Response
    {
        $userId = $this->getUserId();
        $tenantId = $this->getTenantId();

        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $startDate = $this->request->get('start_date', '');
        $endDate = $this->request->get('end_date', '');
        $status = $this->request->get('status');

        $query = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process', 'media'])
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId);

        if ($startDate !== '' && $startDate !== null) {
            $ts = strtotime($startDate . ' 00:00:00');
            if ($ts) $query->where('create_time', '>=', $ts);
        }
        if ($endDate !== '' && $endDate !== null) {
            $ts = strtotime($endDate . ' 23:59:59');
            if ($ts) $query->where('create_time', '<=', $ts);
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->order('id', 'desc')->page($page, $limit)->select();

        $statusMap = [0 => '待审核', 1 => '已通过', 2 => '已拒绝'];
        $items = [];
        foreach ($list as $r) {
            $allocation = $r->allocation;
            $order = $allocation && $allocation->order ? $allocation->order : null;
            $model = $allocation && $allocation->model ? $allocation->model : null;
            $product = $model && $model->product ? $model->product : null;
            $process = $allocation && $allocation->process ? $allocation->process : null;

            $images = [];
            if ($r->media && !$r->media->isEmpty()) {
                foreach ($r->media as $m) {
                    $images[] = (string) ($m->url ?? '');
                }
            }

            $items[] = [
                'id'           => (int) $r->id,
                'create_time'  => (int) $r->create_time,
                'order_no'     => $order ? (string) ($order->order_no ?? '') : '',
                'product_name' => $product ? (string) ($product->name ?? '') : '',
                'model_name'   => $model ? (string) ($model->name ?? '') : '',
                'process_name' => $process ? (string) ($process->name ?? '') : '',
                'quantity'     => (int) $r->quantity,
                'work_hours'   => (float) $r->work_hours,
                'wage'         => number_format((float) $r->wage, 2, '.', ''),
                'status'       => (int) $r->status,
                'status_text'  => $statusMap[(int) $r->status] ?? '未知',
                'remark'       => (string) ($r->remark ?? ''),
                'images'       => $images,
            ];
        }

        return $this->success('', ['total' => $total, 'list' => $items]);
    }

    /**
     * 报工详情
     * GET /api/worker/reportDetail
     */
    public function reportDetail(): Response
    {
        $userId = $this->getUserId();
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);

        if ($id <= 0) return $this->error('参数错误');

        $report = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process', 'user', 'media'])
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->find();

        if (!$report) return $this->error('记录不存在');

        $allocation = $report->allocation;
        $order = $allocation && $allocation->order ? $allocation->order : null;
        $model = $allocation && $allocation->model ? $allocation->model : null;
        $product = $model && $model->product ? $model->product : null;
        $process = $allocation && $allocation->process ? $allocation->process : null;

        $images = [];
        if ($report->media && !$report->media->isEmpty()) {
            foreach ($report->media as $m) {
                $images[] = (string) ($m->url ?? '');
            }
        }

        $statusMap = [0 => '待审核', 1 => '已通过', 2 => '已拒绝'];

        return $this->success('', [
            'id'              => (int) $report->id,
            'create_time'     => (int) $report->create_time,
            'order_no'        => $order ? (string) ($order->order_no ?? '') : '',
            'product_name'    => $product ? (string) ($product->name ?? '') : '',
            'model_name'      => $model ? (string) ($model->name ?? '') : '',
            'process_name'    => $process ? (string) ($process->name ?? '') : '',
            'quantity'        => (int) $report->quantity,
            'work_hours'      => (float) $report->work_hours,
            'unit_price'      => (float) $report->unit_price,
            'wage'            => number_format((float) $report->wage, 2, '.', ''),
            'status'          => (int) $report->status,
            'status_text'     => $statusMap[(int) $report->status] ?? '未知',
            'remark'          => (string) ($report->remark ?? ''),
            'audit_time'      => (int) ($report->audit_time ?? 0),
            'operator_name'   => $report->user ? (string) ($report->user->nickname ?? '') : '',
            'images'          => $images,
        ]);
    }

    /**
     * 获取工资记录
     * GET /api/worker/wages
     */
    public function wages(): Response
    {
        $userId = $this->getUserId();
        $tenantId = $this->getTenantId();

        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $startDate = $this->request->get('start_date', '');
        $endDate = $this->request->get('end_date', '');

        $query = Db::name('mes_wage')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId);

        if ($startDate !== '' && $startDate !== null) {
            $query->where('work_date', '>=', $startDate);
        }
        if ($endDate !== '' && $endDate !== null) {
            $query->where('work_date', '<=', $endDate);
        }

        $total = $query->count();
        $list = $query->order('work_date', 'desc')->order('id', 'desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $items = [];
        foreach ($list as $row) {
            $items[] = [
                'work_date'   => (string) ($row['work_date'] ?? ''),
                'quantity'    => (int) ($row['quantity'] ?? 0),
                'work_hours'  => (float) ($row['work_hours'] ?? 0),
                'unit_price'  => (float) ($row['unit_price'] ?? 0),
                'total_wage'  => number_format((float) ($row['total_wage'] ?? 0), 2, '.', ''),
            ];
        }

        // 图表数据
        $chartData = [];
        $action = $this->request->get('action', '');
        if ($action === 'chart') {
            $chartQuery = Db::name('mes_wage')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $userId);
            if ($startDate !== '' && $startDate !== null) {
                $chartQuery->where('work_date', '>=', $startDate);
            }
            if ($endDate !== '' && $endDate !== null) {
                $chartQuery->where('work_date', '<=', $endDate);
            }

            // 按日汇总
            $daily = (clone $chartQuery)->group('work_date')
                ->field("work_date, SUM(quantity) as quantity, SUM(total_wage) as wage")
                ->order('work_date', 'asc')
                ->select()
                ->toArray();

            // 按工序汇总
            $prefix = config('database.connections.mysql.prefix', 'fa_');
            $byProcess = Db::name('mes_wage')->alias('w')
                ->join($prefix . 'mes_allocation a', 'w.allocation_id = a.id')
                ->join($prefix . 'mes_process p', 'a.process_id = p.id')
                ->where('w.tenant_id', $tenantId)
                ->where('w.user_id', $userId);
            if ($startDate !== '' && $startDate !== null) {
                $byProcess->where('w.work_date', '>=', $startDate);
            }
            if ($endDate !== '' && $endDate !== null) {
                $byProcess->where('w.work_date', '<=', $endDate);
            }
            $byProcess = $byProcess->field('p.name, SUM(w.total_wage) as wage')
                ->group('a.process_id, p.name')
                ->order('wage', 'desc')
                ->select()
                ->toArray();

            $chartData = [
                'daily'              => $daily,
                'by_process_names'   => array_column($byProcess, 'name'),
                'by_process_wages'   => array_map(function ($v) {
                    return number_format((float) $v, 2, '.', '');
                }, array_column($byProcess, 'wage')),
            ];
        }

        return $this->success('', array_merge(
            ['total' => $total, 'list' => $items],
            $chartData ? ['chart_data' => $chartData] : []
        ));
    }

    /**
     * 上传报工图片（复用 common/upload，增加报工场景）
     * POST /api/worker/uploadImage
     */
    public function uploadImage(): Response
    {
        // 直接复用 common/upload 逻辑
        $userId = $this->getUserId();

        $file = $this->request->file('file');
        $image = $this->request->file('image');
        $uploadFile = $file ?: $image;

        if (!$uploadFile) {
            return $this->error('请选择文件');
        }

        try {
            $upload = new \app\common\lib\Upload();
            $result = $upload->handle($this->request, $userId);
            if (is_array($result) && isset($result['url'])) {
                return $this->success('上传成功', $result);
            }
            return $this->error(is_string($result) ? $result : '上传失败');
        } catch (\Throwable $e) {
            return $this->error('上传失败：' . $e->getMessage());
        }
    }

    /**
     * 获取消息通知列表
     * GET /api/worker/notifications
     */
    public function notifications(): Response
    {
        $userId = $this->getUserId();
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(50, (int) $this->request->get('limit', 20)));

        // 使用系统日志表作为通知源（适配 fa_log 实际字段）
        $query = Db::name('log')
            ->where('tenant_id', $tenantId)
            ->where('admin_id', $userId)
            ->order('id', 'desc');

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        $items = [];
        foreach ($list as $row) {
            $items[] = [
                'id'          => (int) $row['id'],
                'title'       => (string) ($row['type'] ?? '系统通知'),
                'content'     => (string) ($row['content'] ?? ''),
                'level'       => 'info',
                'is_read'     => 1,
                'create_time' => (int) ($row['create_time'] ?? 0),
            ];
        }

        return $this->success('', ['total' => $total, 'list' => $items]);
    }

    /**
     * 标记通知已读
     * POST /api/worker/readNotifications
     */
    public function readNotifications(): Response
    {
        // fa_log 表无 status 字段，标记已读暂不操作
        return $this->success('操作成功');
    }
}
