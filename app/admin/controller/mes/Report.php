<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\ProcessPriceModel;
use app\admin\model\mes\WageModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

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
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc');

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
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['allocation_id'])) {
                return $this->error('参数不能为空');
            }

            $tenantId = $this->getTenantId();
            $allocation = AllocationModel::where('tenant_id', $tenantId)
                ->find($params['allocation_id']);
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
            $itemNos = $params['item_nos'] ?? [];

            $data = [
                'tenant_id' => $tenantId,
                'allocation_id' => $allocation->id,
                'user_id' => $allocation->user_id,
                'work_type' => $workType,
                'remark' => $params['remark'] ?? '',
            ];

            if ($workType == 'piece') {
                $quantity = is_array($itemNos) ? count($itemNos) : (int) $itemNos;
                $data['quantity'] = $quantity;
                $data['item_nos'] = is_array($itemNos) ? json_encode($itemNos) : $itemNos;
                $data['wage'] = $quantity * $processPrice->price;
            } else {
                $workHours = (float) ($params['work_hours'] ?? 0);
                $data['work_hours'] = $workHours;
                $data['wage'] = $workHours * $processPrice->time_price;
            }

            Db::startTrans();
            try {
                $report = ReportModel::create($data);
                
                // 更新分配完成数量
                $allocation->completed_quantity += ($data['quantity'] ?? 0);
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
                    'quantity' => $data['quantity'] ?? 0,
                    'work_hours' => $data['work_hours'] ?? 0,
                    'unit_price' => $workType == 'piece' ? $processPrice->price : $processPrice->time_price,
                    'total_wage' => $data['wage'],
                    'work_date' => date('Y-m-d'),
                    'create_time' => time(),
                ]);

                Db::commit();
                return $this->success('报工成功', ['id' => $report->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('报工失败：' . $e->getMessage());
            }
        }

        $allocationId = $this->request->get('allocation_id');
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
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = ReportModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('报工记录不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败：' . $e->getMessage());
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
        
        try {
            foreach ($idsArr as $id) {
                $report = ReportModel::where('tenant_id', $tenantId)->find($id);
                if ($report) {
                    $report->delete();
                }
            }
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
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
        $reports = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process'])
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $idsArr)
            ->select();

        View::assign('reports', $reports);
        View::assign('ids', $ids);
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
        $reason = $this->request->post('audit_reason', '');
        $auditNotes = $this->request->post('audit_notes', '');
        $qualityStatus = $this->request->post('quality_status', 1);

        if (empty($ids) || !in_array($status, ['1', '2'])) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : explode(',', $ids);
        $success = 0;
        $fail = 0;

        foreach ($idsArr as $id) {
            $report = ReportModel::where('tenant_id', $tenantId)->find($id);
            if (!$report) {
                $fail++;
                continue;
            }

            try {
                $report->status = (int) $status;
                $report->audit_user_id = $this->auth->id ?? 0;
                $report->audit_time = time();
                $report->audit_reason = $reason;
                $report->audit_notes = $auditNotes;
                $report->quality_status = (int) $qualityStatus;
                $report->save();

                $success++;
            } catch (\Exception $e) {
                $fail++;
            }
        }

        if ($success > 0) {
            $msg = "审核成功：{$success} 条";
            if ($fail > 0) {
                $msg .= "，失败：{$fail} 条";
            }
            return $this->success($msg);
        } else {
            return $this->error('审核失败');
        }
    }
}
