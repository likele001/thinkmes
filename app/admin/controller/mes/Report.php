<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\ProcessPriceModel;
use app\admin\model\mes\WageModel;
use app\admin\model\mes\StockLogModel;
use app\admin\model\mes\ProductModelModel;
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
        $query = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process'])
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
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
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
            // 过滤item_nos中的空值
            if (is_array($itemNos)) {
                $itemNos = array_filter($itemNos, function($item) {
                    return $item !== '' && $item !== null && $item !== false;
                });
                $itemNos = array_values($itemNos); // 重置索引
            }

            $data = [
                'tenant_id' => $tenantId,
                'allocation_id' => $allocation->id,
                'user_id' => $allocation->user_id,
                'work_type' => $workType,
                'remark' => trim($params['remark'] ?? ''),
                'status' => 0, // 初始待审核状态
            ];

            // 计件处理
            if ($workType == 'piece') {
                $quantity = is_array($itemNos) ? count($itemNos) : max(0, (int) $itemNos);
                if ($quantity <= 0) {
                    return $this->error('计件数量必须大于0');
                }
                $data['quantity'] = $quantity;
                $data['item_nos'] = is_array($itemNos) ? json_encode($itemNos, JSON_UNESCAPED_UNICODE) : $itemNos;
                $data['wage'] = $quantity * $processPrice->price;
            } 
            // 计时处理
            else {
                $workHours = isset($params['work_hours']) ? (float) $params['work_hours'] : 0;
                if ($workHours <= 0) {
                    return $this->error('计时工时必须大于0');
                }
                $data['work_hours'] = $workHours;
                $data['wage'] = $workHours * $processPrice->time_price;
                // 计时工时报工按工时折算数量（可根据业务调整折算规则）
                $data['quantity'] = ceil($workHours / 1); // 示例：1小时=1件
            }

            Db::startTrans();
            try {
                $report = ReportModel::create($data);
                
                // 更新分配完成数量
                $allocation->completed_quantity += $data['quantity'];
                $allocation->completed_quantity = max(0, $allocation->completed_quantity); // 确保非负
                if ($allocation->completed_quantity >= $allocation->quantity) {
                    $allocation->status = 2; // 已完成
                } else {
                    $allocation->status = 1; // 进行中
                }
                $allocation->save();

                // 保存工资记录
                WageModel::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $allocation->user_id,
                    'report_id' => $report->id,
                    'allocation_id' => $allocation->id,
                    'work_type' => $workType,
                    'quantity' => $data['quantity'],
                    'work_hours' => $data['work_hours'] ?? 0,
                    'unit_price' => $workType == 'piece' ? $processPrice->price : $processPrice->time_price,
                    'total_wage' => $data['wage'],
                    'work_date' => date('Y-m-d'),
                    'create_time' => time(),
                    'status' => 0, // 待结算
                ]);

                Db::commit();
                return $this->success('报工成功', ['id' => $report->id]);
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
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : explode(',', $ids);
        // 过滤非数字ID
        $idsArr = array_filter($idsArr, 'is_numeric');
        if (empty($idsArr)) {
            return $this->error('参数错误，ID必须为数字');
        }

        $reports = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process'])
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $idsArr)
            ->select();

        View::assign('reports', $reports);
        View::assign('ids', implode(',', $idsArr));
        View::assign('status', $status);
        View::assign('title', '审核报工');
        return $this->fetchWithLayout('mes/report/audit');
    }

    /**
     * 审核报工（通过/拒绝）
     */
    public function audit(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $ids = $this->request->post('ids');
        $status = $this->request->post('status');
        $reason = trim($this->request->post('audit_reason', ''));
        $auditNotes = trim($this->request->post('audit_notes', ''));
        $qualityStatus = (int) $this->request->post('quality_status', 1);

        // 参数校验
        if (empty($ids) || !in_array((string)$status, ['1', '2']) || !in_array($qualityStatus, [0, 1])) {
            return $this->error('参数错误：状态只能是1(通过)/2(拒绝)，质检状态只能是0(不合格)/1(合格)');
        }
        $status = (int) $status;
        // 拒绝审核必须填写原因
        if ($status == 2 && empty($reason)) {
            return $this->error('拒绝审核必须填写拒绝原因');
        }

        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : explode(',', $ids);
        $idsArr = array_filter($idsArr, 'is_numeric'); // 过滤非数字ID
        if (empty($idsArr)) {
            return $this->error('参数错误，ID必须为数字');
        }

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
                $report->save();

                // 如果审核通过且质检合格，增加成品库存
                if ($status == 1 && $qualityStatus == 1 && $report->quantity > 0) {
                    $allocation = AllocationModel::where('tenant_id', $tenantId)->find($report->allocation_id);
                    if ($allocation && $allocation->model_id > 0) {
                        StockLogModel::logProduct(
                            $tenantId,
                            (int)$allocation->model_id,
                            (float)$report->quantity,
                            'production_in',
                            $report->id,
                            $adminId,
                            '完工入库：报工审核通过'
                        );
                        
                        // 更新工资记录状态为待发放
                        WageModel::where('report_id', $report->id)
                            ->where('tenant_id', $tenantId)
                            ->update(['status' => 1]);
                    }
                }

                $success++;
            }

            Db::commit();
            $msg = "审核成功：{$success} 条";
            if ($fail > 0) {
                $msg .= "，失败：{$fail} 条（可能是记录不存在或已审核）";
            }
            return $this->success($msg);
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('审核失败');
        }
    }
}
