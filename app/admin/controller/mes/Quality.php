<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\QualityStandardModel;
use app\admin\model\mes\QualityCheckModel;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\ProcessModel;
use app\admin\model\mes\ProductModelModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 质检管理
 *
 * @icon fa fa-check-circle
 * @remark 管理质量标准和质检记录
 */
class Quality extends Backend
{
    /**
     * 质检标准列表
     */
    public function standard(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '质检标准管理');
            return $this->fetchWithLayout('mes/quality/standard');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = QualityStandardModel::with(['process', 'model'])
            ->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 添加质检标准
     */
    public function addStandard(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $tenantId = $this->getTenantId();
            $params['tenant_id'] = $tenantId;

            // 处理检查项JSON
            if (isset($params['check_items']) && is_array($params['check_items'])) {
                $params['check_items'] = json_encode($params['check_items'], JSON_UNESCAPED_UNICODE);
            }

            try {
                $standard = QualityStandardModel::create($params);
                return $this->success('添加成功', ['id' => $standard->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败');
            }
        }

        $tenantId = $this->getTenantId();
        // 获取工序列表
        $processList = ProcessModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        View::assign('processList', $processList ?: []);

        // 获取型号列表
        $modelList = ProductModelModel::with('product')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->select();
        $modelOptions = [];
        foreach ($modelList as $model) {
            $displayName = $model->product->name . ' - ' . $model->name;
            $modelOptions[$model->id] = $displayName;
        }
        View::assign('modelList', $modelOptions);

        View::assign('title', '添加质检标准');
        return $this->fetchWithLayout('mes/quality/add_standard');
    }

    /**
     * 质检记录列表
     */
    public function check(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '质检记录管理');
            return $this->fetchWithLayout('mes/quality/check');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = QualityCheckModel::with(['report', 'allocation', 'standard'])
            ->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }

        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 创建质检单
     */
    public function addCheck(): string|Response
    {
        $reportId = $this->request->get('report_id');
        if (!$reportId) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $report = ReportModel::with(['allocation'])->where('tenant_id', $tenantId)->find($reportId);
        if (!$report) {
            return $this->error('报工记录不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $params['tenant_id'] = $tenantId;
            $params['report_id'] = $reportId;
            $params['allocation_id'] = $report->allocation_id ?? 0;
            $params['check_no'] = QualityCheckModel::generateCheckNo();
            $params['check_user_id'] = $this->auth->id ?? 0;
            
            // 填充默认值，避免数据库 NOT NULL 约束导致失败
            $params['report_id'] = $params['report_id'] ?? $reportId;
            $params['allocation_id'] = $params['allocation_id'] ?? ($report->allocation_id ?? 0);
            $params['standard_id'] = $params['standard_id'] ?? 0;
            $params['check_quantity'] = $params['check_quantity'] ?? 0;
            $params['qualified_quantity'] = $params['qualified_quantity'] ?? 0;
            $params['unqualified_quantity'] = $params['unqualified_quantity'] ?? 0;

            // 处理检查时间
            if (!empty($params['check_time'])) {
                $params['check_time'] = strtotime($params['check_time']);
            }

            // 计算合格率
            $params['qualified_rate'] = 0;
            if ($params['check_quantity'] > 0) {
                $params['qualified_rate'] = round(($params['qualified_quantity'] / $params['check_quantity']) * 100, 2);
            }

            // 处理检查项JSON
            if (isset($params['check_items']) && is_array($params['check_items'])) {
                $params['check_items'] = json_encode($params['check_items'], JSON_UNESCAPED_UNICODE);
            }

            Db::startTrans();
            try {
                $qualityCheck = QualityCheckModel::create($params);

                // 更新报工质量状态
                $report->quality_status = $params['unqualified_quantity'] > 0 ? 2 : 1;
                $report->save();

                // 如果有不合格品，更新分配状态
                if ($params['unqualified_quantity'] > 0) {
                    $allocation = AllocationModel::where('tenant_id', $tenantId)
                        ->find($report->allocation_id);
                    if ($allocation) {
                        $allocation->status = 0; // 待开始（返工）
                        $allocation->save();
                    }
                }

                Db::commit();
                return $this->success('质检完成', ['id' => $qualityCheck->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('质检失败');
            }
        }

        // 获取质检标准列表
        $standardList = QualityStandardModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        View::assign('standardList', $standardList ?: []);

        View::assign('report', $report);
        View::assign('title', '创建质检单');
        return $this->fetchWithLayout('mes/quality/add_check');
    }

    /**
     * 质检统计
     */
    public function statistics(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '质检统计');
            return $this->fetchWithLayout('mes/quality/statistics');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-d'));

        $startTime = strtotime($startDate . ' 00:00:00');
        $endTime = strtotime($endDate . ' 23:59:59');

        // 按日期统计质检数据
        $query = QualityCheckModel::alias('qc')
            ->where('qc.tenant_id', $tenantId)
            ->where('qc.check_time', 'between', [$startTime, $endTime])
            ->field('DATE(FROM_UNIXTIME(qc.check_time)) as stat_date,
                     COUNT(*) as total_count,
                     SUM(qc.qualified_quantity) as total_qualified,
                     SUM(qc.unqualified_quantity) as total_unqualified,
                     AVG(qc.qualified_rate) as avg_qualified_rate')
            ->group('stat_date')
            ->order('stat_date', 'desc');

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }
}
