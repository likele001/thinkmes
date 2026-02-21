<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\ProcessPriceModel;
use app\admin\model\mes\TraceCodeModel;
use app\admin\model\mes\WageModel;
use app\admin\model\mes\StockLogModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\ReportMediaModel;
use think\facade\Db;
use think\facade\View;
use think\Response;
use think\exception\ValidateException;

/**
 * 报工管理
 * 
 * @icon fa fa-clipboard
 */
class Report extends Backend
{
    /**
     * 报工列表
     */
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '报工管理');
            return $this->fetchWithLayout('mes/report/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $status = $this->request->get('status');

        $tenantId = $this->getTenantId();
        $query = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process', 'media'])
            ->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }

        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        $rows = [];
        foreach ($list as $report) {
            $row = $report->toArray();

            $orderNo = '';
            $productName = '';
            $modelName = '';
            $itemNosText = '';

            $allocation = $report->allocation;
            if ($allocation) {
                $order = $allocation->order;
                if ($order) {
                    $orderNo = (string) ($order['order_no'] ?? '');
                }
                $model = $allocation->model;
                if ($model) {
                    $modelName = (string) ($model['name'] ?? '');
                    $product = $model->product;
                    if ($product) {
                        $productName = (string) ($product['name'] ?? '');
                    }
                }
            }

            $rawNos = $report['item_nos'] ?? '';
            if ($rawNos) {
                $tmpNos = json_decode((string) $rawNos, true);
                if (is_array($tmpNos)) {
                    $safeNos = [];
                    foreach ($tmpNos as $n) {
                        if ($n === '' || $n === null || $n === false) {
                            continue;
                        }
                        $safeNos[] = (string) $n;
                    }
                    $itemNosText = implode(', ', $safeNos);
                } else {
                    $itemNosText = (string) $rawNos;
                }
            }

            $images = [];
            $videos = [];
            $mediaList = $report->media ?? [];
            foreach ($mediaList as $m) {
                $url = $this->normalizeMediaUrl($m['url'] ?? '');
                if (!$url) {
                    continue;
                }
                if (($m['type'] ?? '') === 'image') {
                    $images[] = $url;
                } elseif (($m['type'] ?? '') === 'video') {
                    $videos[] = $url;
                }
            }
            $images = array_values(array_unique($images));
            $videos = array_values(array_unique($videos));

            $row['order_no'] = $orderNo;
            $row['product_name'] = $productName;
            $row['model_name'] = $modelName;
            $row['item_nos_text'] = $itemNosText;
            $row['image_cover'] = $images ? $images[0] : '';
            $row['image_count'] = count($images);
            $row['video_count'] = count($videos);

            $rows[] = $row;
        }

        return $this->success('', ['total' => $total, 'list' => $rows]);
    }

    /**
     * 添加报工
     */
    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a', []);
            
            // 参数基础校验
            if (empty($params) || empty($params['allocation_id']) || !is_numeric($params['allocation_id'])) {
                return $this->error('参数不能为空或格式错误');
            }

            $tenantId = $this->getTenantId();
            $allocation = AllocationModel::where('tenant_id', $tenantId)
                ->find((int)$params['allocation_id']);
            if (!$allocation) {
                return $this->error('分配不存在');
            }

            $processPrice = ProcessPriceModel::where('tenant_id', $tenantId)
                ->where('model_id', $allocation->model_id)
                ->where('process_id', $allocation->process_id)
                ->find();
            if (!$processPrice) {
                return $this->error('工序工资未设置');
            }

            $workType = $params['work_type'] ?? 'piece';
            // 校验工时类型合法性
            if (!in_array($workType, ['piece', 'hour'])) {
                return $this->error('工时类型错误，仅支持计件(piece)和计时(hour)');
            }
            
            $itemNos = $params['item_nos'] ?? [];
            if (is_array($itemNos)) {
                $itemNos = array_filter($itemNos, function ($item) {
                    return $item !== '' && $item !== null && $item !== false;
                });
                $itemNos = array_values($itemNos);
            }

            $baseData = [
                'tenant_id'     => $tenantId,
                'allocation_id' => $allocation->id,
                'user_id'       => $allocation->user_id,
                'work_type'     => $workType,
                'remark'        => trim($params['remark'] ?? ''),
                'status'        => 0,
            ];

            $needQty = 0;
            $workHours = 0.0;

            if ($workType === 'piece') {
                $qty = is_array($itemNos) ? count($itemNos) : max(0, (int) $itemNos);
                if ($qty <= 0) {
                    return $this->error('计件数量必须大于0');
                }
                $needQty = $qty;
            } else {
                $workHours = isset($params['work_hours']) ? (float) $params['work_hours'] : 0;
                if ($workHours <= 0) {
                    return $this->error('计时工时必须大于0');
                }
                $needQty = (int) ceil($workHours / 1);
                $baseData['work_hours'] = $workHours;
                $baseData['wage'] = $workHours * (float) $processPrice->time_price;
                $baseData['quantity'] = $needQty;
            }

            $reportedQty = (int) Db::name('mes_report')
                ->where('tenant_id', $tenantId)
                ->where('allocation_id', $allocation->id)
                ->sum('quantity');
            $remainingQty = (int) $allocation->quantity - $reportedQty;
            if ($remainingQty < 0) {
                $remainingQty = 0;
            }
            if ($needQty > $remainingQty) {
                return $this->error(
                    '报工数量不能超过待报数量，已报：' . $reportedQty .
                    '，分配：' . (int) $allocation->quantity .
                    '，本次报工：' . (int) $needQty
                );
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
                        $rowData['wage'] = $price;
                        $rowData['item_nos'] = json_encode([(string) $no], JSON_UNESCAPED_UNICODE);
                        $report = ReportModel::create($rowData);
                        $lastReportId = (int) $report->id;

                        TraceCodeModel::where('tenant_id', $tenantId)
                            ->where('allocation_id', $allocation->id)
                            ->whereIn('item_no', [(string) $no])
                            ->update([
                                'report_id'   => $report->id,
                                'user_id'     => $allocation->user_id,
                                'update_time' => time(),
                            ]);

                        WageModel::create([
                            'tenant_id'     => $tenantId,
                            'user_id'       => $allocation->user_id,
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
                        'user_id'       => $allocation->user_id,
                        'report_id'     => $report->id,
                        'allocation_id' => $allocation->id,
                        'work_type'     => $workType,
                        'quantity'      => $needQty,
                        'work_hours'    => $workHours,
                        'unit_price'    => (float) $processPrice->time_price,
                        'total_wage'    => $rowData['wage'],
                        'work_date'     => date('Y-m-d'),
                        'create_time'   => time(),
                        'status'        => 0,
                    ]);

                    $totalQty = $needQty;
                }

                $allocation->completed_quantity += $totalQty;
                $allocation->completed_quantity = max(0, $allocation->completed_quantity); // 确保非负
                if ($allocation->completed_quantity >= $allocation->quantity) {
                    $allocation->status = 2; // 已完成
                } else {
                    $allocation->status = 1; // 进行中
                }
                $allocation->save();

                Db::commit();
                return $this->success('报工成功', ['id' => $lastReportId]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('报工失败');
            }
        }

        $allocationId = $this->request->get('allocation_id', 0);
        View::assign('allocation_id', $allocationId);
        View::assign('title', '添加报工');
        return $this->fetchWithLayout('mes/report/add');
    }

    /**
     * 编辑报工
     */
    public function edit(): string|Response
    {
        $ids = $this->request->param('ids');
        // 校验ids格式
        if (empty($ids) || !is_numeric($ids)) {
            return $this->error('参数错误，ID必须为数字');
        }
        $ids = (int) $ids;

        $tenantId = $this->getTenantId();
        $row = ReportModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('报工记录不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a', []);
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            // 只允许编辑指定字段，防止批量赋值漏洞
            $allowFields = ['remark', 'status'];
            $updateData = [];
            foreach ($allowFields as $field) {
                if (isset($params[$field])) {
                    $updateData[$field] = $params[$field];
                }
            }
            
            // 已审核的记录不允许编辑
            if ($row->status != 0) {
                return $this->error('已审核的报工记录不允许编辑');
            }

            try {
                $row->save($updateData);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败');
            }
        }

        View::assign('row', $row);
        View::assign('title', '编辑报工');
        return $this->fetchWithLayout('mes/report/edit');
    }

    /**
     * 删除报工
     */
    public function del(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : explode(',', $ids);
        // 过滤非数字ID
        $idsArr = array_filter($idsArr, 'is_numeric');
        if (empty($idsArr)) {
            return $this->error('参数错误，ID必须为数字');
        }

        Db::startTrans();
        try {
            foreach ($idsArr as $id) {
                $id = (int) $id;
                /** @var ReportModel $report */
                $report = ReportModel::where('tenant_id', $tenantId)->find($id);
                if (!$report) {
                    continue;
                }

                // 已审核的记录不允许删除
                if ($report->status != 0) {
                    throw new ValidateException("报工记录ID:{$id}已审核，不允许删除");
                }

                // 回滚分配完成数量
                $allocation = AllocationModel::where('tenant_id', $tenantId)->find($report->allocation_id);
                if ($allocation) {
                    $allocation->completed_quantity = max(0, $allocation->completed_quantity - $report->quantity);
                    // 重新计算分配状态
                    if ($allocation->completed_quantity <= 0) {
                        $allocation->status = 0; // 未开始
                    } elseif ($allocation->completed_quantity < $allocation->quantity) {
                        $allocation->status = 1; // 进行中
                    } else {
                        $allocation->status = 2; // 已完成
                    }
                    $allocation->save();
                }

                // 删除关联工资记录
                WageModel::where('report_id', $id)->where('tenant_id', $tenantId)->delete();

                // 删除报工记录
                $report->delete();
            }
            
            Db::commit();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败');
        }
    }

    /**
     * 审核页面
     */
    public function audit_page(): string|Response
    {
        $ids = $this->request->get('ids');
        $status = $this->request->get('status', '1');

        if (empty($ids)) {
            return $this->error('请选择要审核的记录');
        }

        $rawIds = $ids;
        $idsArr = [];
        if (is_array($rawIds)) {
            $idsArr = $rawIds;
        } elseif (is_string($rawIds)) {
            $rawIds = trim($rawIds);
            if ($rawIds !== '') {
                if ($rawIds[0] === '[') {
                    $tmp = json_decode($rawIds, true);
                    if (is_array($tmp)) {
                        $idsArr = $tmp;
                    }
                }
                if (!$idsArr) {
                    $idsArr = preg_split('/[^\d]+/', $rawIds, -1, PREG_SPLIT_NO_EMPTY);
                }
            }
        }
        $idsArr = array_values(array_unique(array_filter(array_map('intval', (array)$idsArr), function ($v) {
            return $v > 0;
        })));
        if (empty($idsArr)) {
            return $this->error('请选择要审核的记录');
        }

        $tenantId = $this->getTenantId();

        $reports = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process', 'user', 'media'])
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $idsArr)
            ->select();

        foreach ($reports as $report) {
            $workerName = '';
            $user = $report->user;
            if ($user) {
                if (!empty($user['nickname'])) {
                    $workerName = (string)$user['nickname'];
                } elseif (!empty($user['username'])) {
                    $workerName = (string)$user['username'];
                } elseif (!empty($user['mobile'])) {
                    $workerName = (string)$user['mobile'];
                }
            }
            $report->setAttr('worker_name', $workerName);

            $remark = (string)($report['remark'] ?? '');
            $imageUrls = $this->extractReportImages($remark);
            $videoUrls = $this->extractReportVideos($remark);
            $mediaList = $report->media ?? [];
            foreach ($mediaList as $m) {
                $url = $this->normalizeMediaUrl($m['url'] ?? '');
                if (!$url) {
                    continue;
                }
                if (($m['type'] ?? '') === 'image') {
                    $imageUrls[] = $url;
                } elseif (($m['type'] ?? '') === 'video' && (($m['scene'] ?? '') === 'audit' || ($m['scene'] ?? '') === '')) {
                    $videoUrls[] = $url;
                }
            }
            $imageUrls = array_values(array_unique($imageUrls));
            $videoUrls = array_values(array_unique($videoUrls));
            $report->setAttr('image_urls', $imageUrls);
            $report->setAttr('audit_videos', $videoUrls);

            $ct = $report['create_time'] ?? null;
            if ($ct) {
                if (is_numeric($ct)) {
                    $report->setAttr('create_time_text', date('Y-m-d H:i:s', (int)$ct));
                } else {
                    $report->setAttr('create_time_text', (string)$ct);
                }
            } else {
                $report->setAttr('create_time_text', '');
            }
            $report->setAttr('item_nos_display', $this->formatItemNosForDisplay($report['item_nos'] ?? ''));
        }

        View::assign('reports', $reports);
        View::assign('ids', implode(',', $idsArr));
        View::assign('status', $status);
        View::assign('title', '审核报工');
        return $this->fetchWithLayout('mes/report/audit');
    }

    /**
     * 报工审核详情（只读）
     */
    public function detail(): string|Response
    {
        $ids = $this->request->get('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $rawIds = $ids;
        $idsArr = [];
        if (is_array($rawIds)) {
            $idsArr = $rawIds;
        } elseif (is_string($rawIds)) {
            $rawIds = trim($rawIds);
            if ($rawIds !== '') {
                if ($rawIds[0] === '[') {
                    $tmp = json_decode($rawIds, true);
                    if (is_array($tmp)) {
                        $idsArr = $tmp;
                    }
                }
                if (!$idsArr) {
                    $idsArr = preg_split('/[^\d]+/', $rawIds, -1, PREG_SPLIT_NO_EMPTY);
                }
            }
        }
        $idsArr = array_values(array_unique(array_filter(array_map('intval', (array)$idsArr), function ($v) {
            return $v > 0;
        })));
        if (empty($idsArr)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $reports = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process', 'user', 'media'])
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $idsArr)
            ->select();

        $statusMap = [0 => '待审核', 1 => '已通过', 2 => '已拒绝'];
        $qualityMap = [1 => '合格', 2 => '不合格', 0 => '不合格'];

        foreach ($reports as $report) {
            $workerName = '';
            $user = $report->user;
            if ($user) {
                if (!empty($user['nickname'])) {
                    $workerName = (string)$user['nickname'];
                } elseif (!empty($user['username'])) {
                    $workerName = (string)$user['username'];
                } elseif (!empty($user['mobile'])) {
                    $workerName = (string)$user['mobile'];
                }
            }
            $report->setAttr('worker_name', $workerName);

            $remark = (string)($report['remark'] ?? '');
            $imageUrls = $this->extractReportImages($remark);
            $videoUrls = $this->extractReportVideos($remark);
            $mediaList = $report->media ?? [];
            foreach ($mediaList as $m) {
                $url = $this->normalizeMediaUrl($m['url'] ?? '');
                if (!$url) {
                    continue;
                }
                if (($m['type'] ?? '') === 'image') {
                    $imageUrls[] = $url;
                } elseif (($m['type'] ?? '') === 'video' && (($m['scene'] ?? '') === 'audit' || ($m['scene'] ?? '') === '')) {
                    $videoUrls[] = $url;
                }
            }
            $imageUrls = array_values(array_unique($imageUrls));
            $videoUrls = array_values(array_unique($videoUrls));
            $report->setAttr('image_urls', $imageUrls);
            $report->setAttr('audit_videos', $videoUrls);

            $ct = $report['create_time'] ?? null;
            if ($ct) {
                if (is_numeric($ct)) {
                    $report->setAttr('create_time_text', date('Y-m-d H:i:s', (int)$ct));
                } else {
                    $report->setAttr('create_time_text', (string)$ct);
                }
            } else {
                $report->setAttr('create_time_text', '');
            }

            $at = $report['audit_time'] ?? null;
            if ($at) {
                $report->setAttr('audit_time_text', date('Y-m-d H:i:s', (int)$at));
            } else {
                $report->setAttr('audit_time_text', '');
            }

            $report->setAttr('status_text', $statusMap[$report['status']] ?? '未知');
            $report->setAttr('quality_text', $qualityMap[$report['quality_status']] ?? '');
            $report->setAttr('item_nos_display', $this->formatItemNosForDisplay($report['item_nos'] ?? ''));
        }

        View::assign('reports', $reports);
        View::assign('title', '报工审核详情');
        return $this->fetchWithLayout('mes/report/detail');
    }

    /**
     * 将 item_nos（JSON 或字符串）格式化为模板可安全输出的显示文本（用 <br> 分隔）
     */
    protected function formatItemNosForDisplay(string $rawNos): string
    {
        if ($rawNos === '') {
            return '';
        }
        $tmpNos = json_decode($rawNos, true);
        if (is_array($tmpNos)) {
            $parts = array_map(function ($v) {
                return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
            }, $tmpNos);
            return implode('<br>', $parts);
        }
        return htmlspecialchars($rawNos, ENT_QUOTES, 'UTF-8');
    }

    /**
     * 审核报工（通过/拒绝）
     */
    public function audit(): Response
    {
        if (!$this->request->isPost()) {
            return $this->auditRespond(false, '非法请求');
        }

        $ids = $this->request->post('ids');
        $status = $this->request->post('status');
        $reason = trim($this->request->post('audit_reason', ''));
        $auditNotes = trim($this->request->post('audit_notes', ''));
        $qualityStatus = (int) $this->request->post('quality_status', 1);

        $auditImagesRaw = $this->request->post('audit_images', '');
        $auditVideosRaw = $this->request->post('audit_videos', '');

        $auditImages = [];
        if (is_array($auditImagesRaw)) {
            $auditImages = $auditImagesRaw;
        } elseif (is_string($auditImagesRaw) && $auditImagesRaw !== '') {
            $tmp = json_decode($auditImagesRaw, true);
            if (is_array($tmp)) {
                $auditImages = $tmp;
            } else {
                $parts = preg_split('/[\s,]+/', $auditImagesRaw, -1, PREG_SPLIT_NO_EMPTY);
                if ($parts) {
                    foreach ($parts as $u) {
                        $auditImages[] = (string) $u;
                    }
                }
            }
        }

        $auditVideos = [];
        if (is_array($auditVideosRaw)) {
            $auditVideos = $auditVideosRaw;
        } elseif (is_string($auditVideosRaw) && $auditVideosRaw !== '') {
            $tmpv = json_decode($auditVideosRaw, true);
            if (is_array($tmpv)) {
                $auditVideos = $tmpv;
            } else {
                $parts = preg_split('/[\s,]+/', $auditVideosRaw, -1, PREG_SPLIT_NO_EMPTY);
                if ($parts) {
                    foreach ($parts as $u) {
                        $auditVideos[] = (string) $u;
                    }
                }
            }
        }

        // 参数校验与 ID 解析
        if (empty($ids) || !in_array((string)$status, ['1', '2']) || !in_array($qualityStatus, [0, 1])) {
            return $this->auditRespond(false, '参数错误：状态只能是1(通过)/2(拒绝)，质检状态只能是0(不合格)/1(合格)');
        }
        $status = (int) $status;
        // 拒绝审核必须填写原因
        if ($status == 2 && empty($reason)) {
            return $this->auditRespond(false, '拒绝审核必须填写拒绝原因');
        }

        // 更宽松地解析 IDs，支持逗号分隔 / 数组 / JSON 字符串
        $rawIds = $ids;
        $idsArr = [];
        if (is_array($rawIds)) {
            $idsArr = $rawIds;
        } elseif (is_string($rawIds)) {
            $rawIds = trim($rawIds);
            if ($rawIds !== '') {
                if ($rawIds[0] === '[') {
                    $tmp = json_decode($rawIds, true);
                    if (is_array($tmp)) {
                        $idsArr = $tmp;
                    }
                }
                if (!$idsArr) {
                    $idsArr = preg_split('/[^\d]+/', $rawIds, -1, PREG_SPLIT_NO_EMPTY);
                }
            }
        }
        $idsArr = array_values(array_unique(array_filter(array_map('intval', (array)$idsArr), function ($v) {
            return $v > 0;
        })));
        if (empty($idsArr)) {
            return $this->auditRespond(false, '请选择要审核的记录');
        }

        $tenantId = $this->getTenantId();

        $adminId = $this->auth->id ?? 0;
        $success = 0;
        $fail = 0;

        Db::startTrans();
        try {
            foreach ($idsArr as $id) {
                $id = (int) $id;
                /** @var ReportModel $report */
                $report = ReportModel::where('tenant_id', $tenantId)->find($id);
                if (!$report || $report->status != 0) {
                    $fail++;
                    continue;
                }

                // 更新报工审核状态
                $report->status = $status;
                $report->audit_user_id = $adminId;
                $report->audit_time = time();
                $report->audit_reason = $reason;
                $report->audit_notes = $auditNotes;
                $report->quality_status = $qualityStatus;

                if ($auditImages || $auditVideos) {
                    foreach ($auditImages as $u) {
                        $url = $this->normalizeMediaUrl($u);
                        if (!$url) {
                            continue;
                        }
                        ReportMediaModel::create([
                            'tenant_id'   => $tenantId,
                            'report_id'   => $report->id,
                            'type'        => 'image',
                            'scene'       => 'audit',
                            'url'         => $url,
                            'create_time' => time(),
                        ]);
                    }
                    foreach ($auditVideos as $u) {
                        $url = $this->normalizeMediaUrl($u);
                        if (!$url) {
                            continue;
                        }
                        ReportMediaModel::create([
                            'tenant_id'   => $tenantId,
                            'report_id'   => $report->id,
                            'type'        => 'video',
                            'scene'       => 'audit',
                            'url'         => $url,
                            'create_time' => time(),
                        ]);
                    }
                }

                $report->save();

                $allocation = null;
                if ($status == 1 && $qualityStatus == 1) {
                    $allocation = AllocationModel::where('tenant_id', $tenantId)->find($report->allocation_id);
                    if ($allocation && $report->quantity > 0 && $allocation->model_id > 0) {
                        StockLogModel::logProduct(
                            $tenantId,
                            (int) $allocation->model_id,
                            (float) $report->quantity,
                            'production_in',
                            $report->id,
                            $adminId,
                            '完工入库：报工审核通过'
                        );
                    }

                    if ($allocation && $report->work_type === 'piece') {
                        $existsTrace = TraceCodeModel::where('tenant_id', $tenantId)
                            ->where('report_id', $report->id)
                            ->find();
                        if (!$existsTrace) {
                            $traceCode = TraceCodeModel::generateTraceCode();
                            $domain = $this->request->domain();
                            $qrUrl = $domain . '/index/trace/query?code=' . $traceCode;

                            $itemNo = '';
                            $rawNos = $report->item_nos ?? '';
                            if (is_string($rawNos) && $rawNos !== '') {
                                $tmpNos = json_decode($rawNos, true);
                                if (is_array($tmpNos) && $tmpNos) {
                                    $first = reset($tmpNos);
                                    if ($first !== '' && $first !== null && $first !== false) {
                                        $itemNo = (string) $first;
                                    }
                                } else {
                                    $itemNo = $rawNos;
                                }
                            }

                            TraceCodeModel::create([
                                'tenant_id'    => $tenantId,
                                'trace_code'   => $traceCode,
                                'report_id'    => $report->id,
                                'allocation_id'=> $allocation->id ?? 0,
                                'order_id'     => $allocation->order_id ?? 0,
                                'model_id'     => $allocation->model_id ?? 0,
                                'process_id'   => $allocation->process_id ?? 0,
                                'user_id'      => $report->user_id,
                                'item_no'      => $itemNo,
                                'qrcode_url'   => $qrUrl,
                                'status'       => 1,
                                'create_time'  => time(),
                                'update_time'  => time(),
                            ]);
                        }
                    }
                }

                $success++;
            }

            Db::commit();
            $msg = "审核成功：{$success} 条";
            if ($fail > 0) {
                $msg .= "，失败：{$fail} 条（可能是记录不存在或已审核）";
            }
            return $this->auditRespond(true, $msg);
        } catch (\Exception $e) {
            Db::rollback();
            return $this->auditRespond(false, '审核失败：' . $e->getMessage());
        }
    }

    protected function auditRespond(bool $ok, string $msg): Response
    {
        if ($this->request->isAjax()) {
            return $ok ? $this->success($msg) : $this->error($msg);
        }

        $base = rtrim($this->request->root(), '/');
        if ($ok) {
            $target = $base . '/mes/report/index';
            $script = 'alert(' . json_encode($msg, JSON_UNESCAPED_UNICODE) . ');window.location.href=' . json_encode($target, JSON_UNESCAPED_UNICODE) . ';';
        } else {
            $script = 'alert(' . json_encode($msg, JSON_UNESCAPED_UNICODE) . ');window.history.back();';
        }

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>审核结果</title></head><body><script>'
            . $script
            . '</script></body></html>';

        return Response::create($html, 'html');
    }

    protected function normalizeMediaUrl($val): string
    {
        if (is_array($val)) {
            $flat = [];
            foreach ($val as $v) {
                if (is_array($v)) {
                    foreach ($v as $vv) {
                        if ($vv !== '' && $vv !== null && $vv !== false) {
                            $flat[] = (string) $vv;
                        }
                    }
                } elseif ($v !== '' && $v !== null && $v !== false) {
                    $flat[] = (string) $v;
                }
            }
            if ($flat) {
                return $flat[0];
            }
            return '';
        }

        $url = trim((string) $val);
        if ($url === '') {
            return '';
        }

        $decoded = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
        if ($decoded !== '' && $decoded !== $url) {
            $url = $decoded;
        }

        $url = trim($url, " \t\n\r\0\x0B\"'");

        $first = $url[0] ?? '';
        if ($first === '[' || $first === '{') {
            $tmp = json_decode($url, true);
            if (is_array($tmp)) {
                $flat = [];
                foreach ($tmp as $v) {
                    if (is_array($v)) {
                        foreach ($v as $vv) {
                            if ($vv !== '' && $vv !== null && $vv !== false) {
                                $flat[] = (string) $vv;
                            }
                        }
                    } elseif ($v !== '' && $v !== null && $v !== false) {
                        $flat[] = (string) $v;
                    }
                }
                if ($flat) {
                    return $flat[0];
                }
            } elseif (is_string($tmp) && $tmp !== '') {
                return trim($tmp);
            }
        }

        if (preg_match('@https?://[^\s"\'<>]+@', $url, $m)) {
            return $m[0];
        }

        return $url;
    }

    protected function extractReportImages(string $remark): array
    {
        $remark = trim($remark);
        if ($remark === '') {
            return [];
        }

        $rawRemark = $remark;
        $imgs = [];

        $tmp = json_decode($rawRemark, true);
        if (is_array($tmp)) {
            $imgSource = null;

            if (isset($tmp['images']) && is_array($tmp['images'])) {
                $imgSource = $tmp['images'];
            } elseif (isset($tmp['images_raw'])) {
                $raw = $tmp['images_raw'];
                $inner = null;
                if (is_string($raw)) {
                    $inner = json_decode($raw, true);
                    if (!is_array($inner)) {
                        $rawDecoded = html_entity_decode($raw, ENT_QUOTES, 'UTF-8');
                        $inner = json_decode($rawDecoded, true);
                        if (!is_array($inner)) {
                            $raw = $rawDecoded;
                        }
                    }
                } elseif (is_array($raw)) {
                    $inner = $raw;
                }
                if (is_array($inner)) {
                    $imgSource = $inner;
                }
            } else {
                $imgSource = $tmp;
            }

            if (!empty($imgSource)) {
                if (\think\helper\Arr::isAssoc($imgSource)) {
                    foreach ($imgSource as $kv) {
                        if (is_array($kv)) {
                            foreach ($kv as $u) {
                                if ($u) {
                                    $imgs[] = (string) $u;
                                }
                            }
                        } elseif ($kv) {
                            $imgs[] = (string) $kv;
                        }
                    }
                } else {
                    foreach ($imgSource as $u) {
                        if ($u) {
                            $imgs[] = (string) $u;
                        }
                    }
                }
            }
        }

        if (!$imgs) {
            if (preg_match_all('@https?://[^\s"\'<>]+@', $rawRemark, $m)) {
                foreach ($m[0] as $u) {
                    if ($u) {
                        $imgs[] = (string) $u;
                    }
                }
            }
        }

        if (!$imgs) {
            return [];
        }

        $imgs = array_values(array_unique($imgs));
        return $imgs;
    }

    protected function extractReportVideos(string $remark): array
    {
        $remark = trim($remark);
        if ($remark === '') {
            return [];
        }

        $videos = [];
        $tmp = json_decode($remark, true);
        if (is_array($tmp)) {
            if (isset($tmp['audit_videos']) && is_array($tmp['audit_videos'])) {
                foreach ($tmp['audit_videos'] as $u) {
                    if ($u) {
                        $videos[] = (string)$u;
                    }
                }
            } elseif (isset($tmp['videos']) && is_array($tmp['videos'])) {
                foreach ($tmp['videos'] as $u) {
                    if ($u) {
                        $videos[] = (string)$u;
                    }
                }
            } else {
                foreach ($tmp as $k => $v) {
                    if (stripos((string)$k, 'video') === false) {
                        continue;
                    }
                    if (is_array($v)) {
                        foreach ($v as $u) {
                            if ($u) {
                                $videos[] = (string)$u;
                            }
                        }
                    } elseif ($v) {
                        $videos[] = (string)$v;
                    }
                }
            }
        }

        if (!$videos) {
            if (preg_match_all('@(?:https?://|/)[^\s"\'<>]+\.(mp4|mov|m4v|webm|ogg|avi)(\?|#|$)@i', $remark, $m)) {
                foreach ($m[0] as $u) {
                    if ($u) {
                        $videos[] = (string)$u;
                    }
                }
            }
        }

        if (!$videos) {
            return [];
        }

        $videos = array_values(array_unique($videos));
        return $videos;
    }

    /**
     * 一键修复报工媒体URL（去除多余JSON/HTML转义，只留纯URL）
     * 仅限当前租户，访问一次即可；后续新数据在写入时已自动清洗
     */
    public function fixMediaUrl(): Response
    {
        $tenantId = $this->getTenantId();
        $list = ReportMediaModel::where('tenant_id', $tenantId)->select();
        $fixed = 0;
        foreach ($list as $media) {
            $old = (string) ($media['url'] ?? '');
            $new = $this->normalizeMediaUrl($old);
            if ($new !== '' && $new !== $old) {
                $media->url = $new;
                $media->save();
                $fixed++;
            }
        }
        return $this->success('媒体URL修复完成，共处理 ' . $fixed . ' 条');
    }
}