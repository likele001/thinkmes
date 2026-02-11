<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\TraceCodeModel;
use app\admin\model\mes\ReportModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 追溯码管理
 */
class TraceCode extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '追溯码管理');
            return $this->fetchWithLayout('mes/trace_code/index');
        }
        
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $tenantId = $this->getTenantId();
        $query = TraceCodeModel::with(['order', 'model.product', 'process', 'report'])
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc');
        
        // 搜索条件
        $traceCode = trim((string) $this->request->get('trace_code'));
        if ($traceCode !== '') {
            $query->where('trace_code', 'like', '%' . $traceCode . '%');
        }
        
        $itemNo = trim((string) $this->request->get('item_no'));
        if ($itemNo !== '') {
            $query->where('item_no', 'like', '%' . $itemNo . '%');
        }
        
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 生成追溯码（从报工记录生成）
     */
    public function generate(): Response
    {
        $reportId = (int) $this->request->post('report_id');
        if (!$reportId) {
            return $this->error('报工ID不能为空');
        }
        
        $tenantId = $this->getTenantId();
        $report = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process'])
            ->where('tenant_id', $tenantId)
            ->find($reportId);
        
        if (!$report) {
            return $this->error('报工记录不存在');
        }
        
        // 检查是否已生成追溯码
        $exists = TraceCodeModel::where('tenant_id', $tenantId)
            ->where('report_id', $reportId)
            ->find();
        
        if ($exists) {
            return $this->success('追溯码已存在', ['trace_code' => $exists->trace_code]);
        }
        
        try {
            $traceCode = TraceCodeModel::generateTraceCode();
            $allocation = $report->allocation;
            
            // 生成二维码URL
            $domain = $this->request->domain();
            $qrUrl = $domain . '/index/trace/query?code=' . $traceCode;
            
            $trace = TraceCodeModel::create([
                'tenant_id' => $tenantId,
                'trace_code' => $traceCode,
                'report_id' => $reportId,
                'allocation_id' => $allocation->id ?? 0,
                'order_id' => $allocation->order_id ?? 0,
                'model_id' => $allocation->model_id ?? 0,
                'process_id' => $allocation->process_id ?? 0,
                'user_id' => $report->user_id,
                'item_no' => $report->item_nos ?? '',
                'qrcode_url' => $qrUrl,
                'status' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ]);
            
            return $this->success('追溯码生成成功', ['trace_code' => $traceCode, 'id' => $trace->id]);
        } catch (\Exception $e) {
            return $this->error('追溯码生成失败：' . $e->getMessage());
        }
    }

    /**
     * 批量生成追溯码
     */
    public function batchGenerate(): Response
    {
        $reportIds = $this->request->post('report_ids');
        if (empty($reportIds)) {
            return $this->error('请选择要生成追溯码的报工记录');
        }
        
        $tenantId = $this->getTenantId();
        $reportIds = is_array($reportIds) ? $reportIds : explode(',', (string) $reportIds);
        $successCount = 0;
        $skipCount = 0;
        
        Db::startTrans();
        try {
            foreach ($reportIds as $reportId) {
                // 检查是否已存在
                $exists = TraceCodeModel::where('tenant_id', $tenantId)
                    ->where('report_id', $reportId)
                    ->find();
                
                if ($exists) {
                    $skipCount++;
                    continue;
                }
                
                $report = ReportModel::with(['allocation'])
                    ->where('tenant_id', $tenantId)
                    ->find($reportId);
                
                if (!$report) {
                    continue;
                }
                
                $traceCode = TraceCodeModel::generateTraceCode();
                $allocation = $report->allocation;
                
                $domain = $this->request->domain();
                $qrUrl = $domain . '/index/trace/query?code=' . $traceCode;
                
                TraceCodeModel::create([
                    'tenant_id' => $tenantId,
                    'trace_code' => $traceCode,
                    'report_id' => $reportId,
                    'allocation_id' => $allocation->id ?? 0,
                    'order_id' => $allocation->order_id ?? 0,
                    'model_id' => $allocation->model_id ?? 0,
                    'process_id' => $allocation->process_id ?? 0,
                    'user_id' => $report->user_id,
                    'item_no' => $report->item_nos ?? '',
                    'qrcode_url' => $qrUrl,
                    'status' => 1,
                    'create_time' => time(),
                    'update_time' => time(),
                ]);
                
                $successCount++;
            }
            
            Db::commit();
            return $this->success("批量生成成功：新增 {$successCount} 条，跳过 {$skipCount} 条");
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('批量生成失败：' . $e->getMessage());
        }
    }

    /**
     * 追溯查询（前端接口）
     */
    public function query(): Response
    {
        $code = trim((string) $this->request->get('code'));
        if (empty($code)) {
            return $this->error('追溯码不能为空');
        }
        
        $trace = TraceCodeModel::with(['order', 'model.product', 'process', 'report.allocation'])
            ->where('trace_code', $code)
            ->where('status', 1)
            ->find();
        
        if (!$trace) {
            return $this->error('追溯码不存在或已失效');
        }
        
        // 更新扫码次数
        $trace->scan_count += 1;
        $trace->last_scan_time = time();
        $trace->save();
        
        $data = $trace->toArray();
        return $this->success('查询成功', $data);
    }

    public function del(): Response
    {
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('请选择要删除的记录');
        }
        
        $tenantId = $this->getTenantId();
        $ids = is_array($ids) ? $ids : explode(',', (string) $ids);
        
        try {
            TraceCodeModel::where('tenant_id', $tenantId)
                ->whereIn('id', $ids)
                ->update(['status' => 0]);
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
