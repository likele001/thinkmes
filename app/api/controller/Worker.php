<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\ProcessPriceModel;
use app\admin\model\mes\WageModel;
use app\admin\model\mes\TraceCodeModel;
use think\facade\Db;
use think\Response;

class Worker extends BaseController
{
    protected function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    protected function getUserId(): int
    {
        return (int) ($this->request->userId ?? 0);
    }

    public function dashboard(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0 || $userId <= 0) {
            return $this->error('未识别租户或用户');
        }

        $todayStart = strtotime(date('Y-m-d 00:00:00'));
        $todayEnd = time();
        $todayDate = date('Y-m-d');

        $todayReportQuantity = (int) Db::name('mes_report')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('create_time', '>=', $todayStart)
            ->where('create_time', '<=', $todayEnd)
            ->sum('quantity');

        $todayWage = (float) Db::name('mes_wage')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('work_date', $todayDate)
            ->sum('total_wage');

        $pendingReports = (int) Db::name('mes_report')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 0)
            ->count();

        $todayTaskCount = (int) AllocationModel::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('create_time', '>=', $todayStart)
            ->where('create_time', '<=', $todayEnd)
            ->count();

        $allocations = AllocationModel::with(['order', 'model.product', 'process'])
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->order('status', 'asc')
            ->order('id', 'desc')
            ->select();

        $taskList = [];
        if (!$allocations->isEmpty()) {
            $allocationIds = [];
            foreach ($allocations as $allocation) {
                $allocationIds[] = (int) $allocation->id;
            }
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
            foreach ($allocations as $allocation) {
                $aid = (int) $allocation->id;
                $assignQty = (int) $allocation->quantity;
                $reportedQty = (int) ($reportedMap[$aid] ?? 0);
                $pendingQty = $assignQty - $reportedQty;
                if ($pendingQty < 0) {
                    $pendingQty = 0;
                }
                $order = $allocation->order;
                $model = $allocation->model;
                $product = $model ? $model->product : null;
                $process = $allocation->process;

                $taskList[] = [
                    'allocation_id' => $aid,
                    'order_no' => $order->order_no ?? '',
                    'order_name' => $order->order_name ?? '',
                    'product_name' => $product->name ?? '',
                    'model_name' => $model->name ?? '',
                    'process_name' => $process->name ?? '',
                    'assign_qty' => $assignQty,
                    'reported_qty' => $reportedQty,
                    'pending_qty' => $pendingQty,
                    'status' => (int) $allocation->status,
                ];
            }
        }

        return $this->success('ok', [
            'metrics' => [
                'today_task_count' => $todayTaskCount,
                'today_report_quantity' => $todayReportQuantity,
                'today_wage' => $todayWage,
                'pending_reports' => $pendingReports,
            ],
            'tasks' => $taskList,
        ]);
    }

    public function taskInfo(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        $allocationId = (int) $this->request->get('allocation_id', 0);
        if ($tenantId <= 0 || $userId <= 0 || $allocationId <= 0) {
            return $this->error('参数错误');
        }

        $allocation = AllocationModel::with(['order', 'model.product', 'process'])
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->find($allocationId);
        if (!$allocation) {
            return $this->error('任务不存在');
        }

        $reportedQty = (int) Db::name('mes_report')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('allocation_id', $allocationId)
            ->sum('quantity');
        $assignQty = (int) $allocation->quantity;
        $pendingQty = $assignQty - $reportedQty;
        if ($pendingQty < 0) {
            $pendingQty = 0;
        }

        $order = $allocation->order;
        $model = $allocation->model;
        $product = $model ? $model->product : null;
        $process = $allocation->process;

        $itemNos = TraceCodeModel::where('tenant_id', $tenantId)
            ->where('allocation_id', $allocationId)
            ->where('status', 1)
            ->where('report_id', 0)
            ->order('id', 'asc')
            ->column('item_no');

        return $this->success('ok', [
            'allocation_id' => (int) $allocation->id,
            'order_no' => $order->order_no ?? '',
            'order_name' => $order->order_name ?? '',
            'product_name' => $product->name ?? '',
            'model_name' => $model->name ?? '',
            'process_name' => $process->name ?? '',
            'assign_qty' => $assignQty,
            'reported_qty' => $reportedQty,
            'pending_qty' => $pendingQty,
            'status' => (int) $allocation->status,
            'item_nos' => $itemNos,
        ]);
    }

    public function report(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0 || $userId <= 0) {
            return $this->error('未识别租户或用户');
        }

        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $allocationId = (int) $this->request->post('allocation_id', 0);
        if ($allocationId <= 0) {
            return $this->error('分工任务不能为空');
        }

        $allocation = AllocationModel::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->find($allocationId);
        if (!$allocation) {
            return $this->error('任务不存在或不属于当前用户');
        }

        $workType = (string) $this->request->post('work_type', 'piece');
        if (!in_array($workType, ['piece', 'hour'], true)) {
            return $this->error('工时类型错误');
        }

        $quantity = (int) $this->request->post('quantity', 0);
        $workHours = (float) $this->request->post('work_hours', 0);

        $itemNos = $this->request->post('item_nos');
        if (is_string($itemNos) && $itemNos !== '') {
            $decoded = json_decode($itemNos, true);
            if (is_array($decoded)) {
                $itemNos = $decoded;
            } else {
                // 兼容逗号分隔的字符串形式
                $parts = array_filter(array_map('trim', explode(',', $itemNos)));
                if ($parts) {
                    $itemNos = array_values($parts);
                }
            }
        }
        if (!is_array($itemNos)) {
            // 兼容 item_nos[] 这样提交的数组
            $itemNos = (array) $itemNos;
        }

        $rawImages = $this->request->post('images');
        $images = $rawImages;
        if (is_string($images) && $images !== '') {
            $decoded = json_decode($images, true);
            if (is_array($decoded)) {
                $images = $decoded;
            }
        }
        if (!is_array($images)) {
            $images = [];
        }

        if ($workType === 'piece') {
            if (empty($itemNos)) {
                return $this->error('请选择要报工的产品编号');
            }
        } else {
            if ($workHours <= 0) {
                return $this->error('工时必须大于0');
            }
        }

        $increaseQty = $workType === 'piece' ? count($itemNos) : (int) ceil($workHours);

        if ($increaseQty <= 0) {
            return $this->error('报工数量必须大于0');
        }

        $reportedQty = (int) Db::name('mes_report')
            ->where('tenant_id', $tenantId)
            ->where('allocation_id', $allocationId)
            ->sum('quantity');

        $remainingQty = (int) $allocation->quantity - $reportedQty;
        if ($remainingQty < 0) {
            $remainingQty = 0;
        }

        if ($increaseQty > $remainingQty) {
            return $this->error(
                '报工数量不能超过待报数量，已报：' . $reportedQty .
                '，分配：' . (int) $allocation->quantity .
                '，本次报工：' . $increaseQty
            );
        }

        $processPrice = ProcessPriceModel::where('tenant_id', $tenantId)
            ->where('model_id', $allocation->model_id)
            ->where('process_id', $allocation->process_id)
            ->find();
        if (!$processPrice) {
            return $this->error('工序工资未设置');
        }

        $baseData = [
            'tenant_id'      => $tenantId,
            'allocation_id'  => $allocationId,
            'user_id'        => $userId,
            'work_type'      => $workType,
            'status'         => 0,
            'quality_status' => 0,
            'create_time'    => time(),
            'update_time'    => time(),
        ];

        if ($workType === 'hour') {
            $baseData['work_hours'] = $workHours;
            $baseData['quantity'] = (int) ceil($workHours);
            $baseData['wage'] = $workHours * (float) $processPrice->time_price;
        }

        Db::startTrans();
        try {
            $totalQty = 0;
            $lastReportId = 0;

            if ($workType === 'piece') {
                $price = (float) $processPrice->price;
                foreach ((array) $itemNos as $no) {
                    if ($no === '' || $no === null || $no === false) {
                        continue;
                    }

                    $rowData = $baseData;
                    $rowData['quantity'] = 1;
                    $rowData['work_hours'] = 0;
                    $rowData['wage'] = $price;
                    $rowData['item_nos'] = json_encode([(string) $no], JSON_UNESCAPED_UNICODE);

                    $imagesForItem = [];
                    if (is_array($images)) {
                        if (isset($images[$no]) && is_array($images[$no])) {
                            $imagesForItem = $images[$no];
                        } elseif (isset($images['images']) && is_array($images['images'])) {
                            $imagesForItem = $images['images'];
                        }
                    }
                    if ($imagesForItem) {
                        $rowData['remark'] = json_encode(['images' => $imagesForItem], JSON_UNESCAPED_UNICODE);
                    } elseif (is_string($rawImages) && $rawImages !== '') {
                        $rowData['remark'] = json_encode(['images_raw' => $rawImages], JSON_UNESCAPED_UNICODE);
                    }

                    $report = ReportModel::create($rowData);
                    $lastReportId = (int) $report->id;

                    TraceCodeModel::where('tenant_id', $tenantId)
                        ->where('allocation_id', $allocationId)
                        ->where('status', 1)
                        ->where('report_id', 0)
                        ->whereIn('item_no', [(string) $no])
                        ->update([
                            'report_id'   => $report->id,
                            'user_id'     => $userId,
                            'update_time' => time(),
                        ]);

                    WageModel::create([
                        'tenant_id'     => $tenantId,
                        'user_id'       => $userId,
                        'report_id'     => $report->id,
                        'allocation_id' => $allocation->id,
                        'work_type'     => $workType,
                        'quantity'      => 1,
                        'work_hours'    => 0,
                        'unit_price'    => $price,
                        'total_wage'    => $price,
                        'work_date'     => date('Y-m-d'),
                        'create_time'   => time(),
                        'status'        => 0,
                    ]);

                    $totalQty += 1;
                }
            } else {
                $rowData = $baseData;
                $rowData['wage'] = $baseData['wage'] ?? ($workHours * (float) $processPrice->time_price);
                $report = ReportModel::create($rowData);
                $lastReportId = (int) $report->id;

                WageModel::create([
                    'tenant_id'     => $tenantId,
                    'user_id'       => $userId,
                    'report_id'     => $report->id,
                    'allocation_id' => $allocation->id,
                    'work_type'     => $workType,
                    'quantity'      => $baseData['quantity'] ?? (int) ceil($workHours),
                    'work_hours'    => $baseData['work_hours'] ?? $workHours,
                    'unit_price'    => (float) $processPrice->time_price,
                    'total_wage'    => $rowData['wage'],
                    'work_date'     => date('Y-m-d'),
                    'create_time'   => time(),
                    'status'        => 0,
                ]);

                $totalQty = $baseData['quantity'] ?? (int) ceil($workHours);
            }

            $allocation->completed_quantity += $totalQty;
            if ($allocation->completed_quantity < 0) {
                $allocation->completed_quantity = 0;
            }
            if ($allocation->completed_quantity >= $allocation->quantity) {
                $allocation->status = 2;
            } else {
                $allocation->status = 1;
            }
            $allocation->save();

            Db::commit();
            return $this->success('报工成功', ['id' => $lastReportId]);
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('报工失败');
        }
    }

    public function reports(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0 || $userId <= 0) {
            return $this->error('未识别租户或用户');
        }

        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));

        $query = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process', 'media'])
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->order('id', 'desc');

        $status = $this->request->get('status');
        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $rows = $query->page($page, $limit)->select();
        $list = [];
        foreach ($rows as $r) {
            $arr = $r->toArray();
            $allocation = $r->allocation;
            $order = $allocation && $allocation->order ? $allocation->order : null;
            $model = $allocation && $allocation->model ? $allocation->model : null;
            $product = $model && $model->product ? $model->product : null;
            $process = $allocation && $allocation->process ? $allocation->process : null;
            $arr['order_no'] = $order ? ($order->order_no ?? '') : '';
            $arr['product_name'] = $product ? ($product->name ?? '') : '';
            $arr['model_name'] = $model ? ($model->name ?? '') : '';
            $arr['process_name'] = $process ? ($process->name ?? '') : '';
            $arr['images'] = [];
            if ($r->media && !$r->media->isEmpty()) {
                foreach ($r->media as $m) {
                    $arr['images'][] = $m->url ?? '';
                }
            }
            $list[] = $arr;
        }

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /** 员工端：报工详情（仅能查看自己的报工，含审核结果、报工/审核图片视频） */
    public function reportDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        $reportId = (int) $this->request->get('report_id', 0) ?: (int) $this->request->get('id', 0);
        if ($reportId <= 0) {
            return $this->error('参数错误');
        }
        $report = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process', 'media'])
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->find($reportId);
        if (!$report) {
            return $this->error('报工记录不存在或无权查看');
        }
        $out = $report->toArray();
        $out['order_no'] = $report->allocation && $report->allocation->order ? $report->allocation->order->order_no : '';
        $out['product_name'] = $report->allocation && $report->allocation->model && $report->allocation->model->product ? $report->allocation->model->product->name : '';
        $out['model_name'] = $report->allocation && $report->allocation->model ? $report->allocation->model->name : '';
        $out['process_name'] = $report->allocation && $report->allocation->process ? $report->allocation->process->name : '';
        $out['images'] = [];
        $out['audit_images'] = [];
        $out['audit_videos'] = [];
        if ($report->media && !$report->media->isEmpty()) {
            foreach ($report->media as $m) {
                $url = $m->url ?? '';
                if ($url === '') {
                    continue;
                }
                $type = $m->type ?? 'image';
                $scene = $m->scene ?? '';
                if ($type === 'video' && ($scene === 'audit' || $scene === '')) {
                    $out['audit_videos'][] = $url;
                } elseif ($type === 'image' && $scene === 'audit') {
                    $out['audit_images'][] = $url;
                } else {
                    $out['images'][] = $url;
                }
            }
        }
        return $this->success('', $out);
    }

    public function wages(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0 || $userId <= 0) {
            return $this->error('未识别租户或用户');
        }

        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $month = $this->request->get('month', '');

        $query = WageModel::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->order('work_date', 'desc')
            ->order('id', 'desc');

        $workDate = $this->request->get('work_date');
        if ($workDate) {
            $query->where('work_date', $workDate);
        }
        if ($month !== '' && $month !== null) {
            $start = $month . '-01';
            $end = date('Y-m-t', strtotime($start));
            $query->where('work_date', 'between', [$start, $end]);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        $totalWage = 0.0;
        if ($month !== '' && $month !== null) {
            $totalWage = (float) WageModel::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('work_date', '>=', $month . '-01')
                ->where('work_date', '<=', date('Y-m-t', strtotime($month . '-01')))
                ->sum('total_wage');
        } else {
            foreach ($list as $row) {
                $totalWage += (float) ($row['total_wage'] ?? 0);
            }
        }

        return $this->success('', [
            'total' => $total,
            'list' => $list,
            'totalWage' => round($totalWage, 2),
            'month' => $month ?: date('Y-m'),
        ]);
    }

    /**
     * 上传报工图片（前端报工小程序用，参考 Userex uploadImage）
     * POST file 或 image，返回 { url }
     */
    public function uploadImage(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0 || $userId <= 0) {
            return $this->error('未识别租户或用户');
        }

        $file = $this->request->file('file') ?? $this->request->file('image');
        if (!$file || !$file->isValid()) {
            return $this->error('请选择图片');
        }
        $ext = strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return $this->error('仅支持 jpg/png/gif/webp');
        }
        $relDir = 'uploads/baogong/' . date('Y-m-d') . '/';
        $root = app()->getRootPath();
        $fullDir = $root . 'public/' . $relDir;
        if (!is_dir($fullDir)) {
            @mkdir($fullDir, 0755, true);
        }
        $filename = uniqid() . '_' . $userId . '.' . $ext;
        $info = $file->move($fullDir, $filename);
        if (!$info) {
            return $this->error('上传失败');
        }
        $url = '/' . $relDir . $filename;
        return $this->success('上传成功', ['url' => $url]);
    }
}
