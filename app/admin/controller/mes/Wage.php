<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\WageModel;
use app\admin\model\mes\ReportModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 工资管理
 */
class Wage extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '工资管理');
            return $this->fetchWithLayout('mes/wage/index');
        }
        
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $tenantId = $this->getTenantId();
        $query = WageModel::order('work_date', 'desc')
            ->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }
        
        // 搜索条件
        $userId = $this->request->get('user_id');
        if ($userId) {
            $query->where('user_id', (int) $userId);
        }
        
        $workDate = $this->request->get('work_date');
        if ($workDate) {
            $query->where('work_date', $workDate);
        }
        
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 工资统计
     */
    public function statistics(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '工资统计');
            return $this->fetchWithLayout('mes/wage/statistics');
        }
        
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $tenantId = $this->getTenantId();
        
        // 按员工统计工资
        $query = Db::name('mes_wage')
            ->alias('w')
            ->join('fa_user u', 'w.user_id = u.id')
            ->field('w.user_id, u.nickname, 
                     SUM(CASE WHEN w.work_type = "piece" THEN w.total_wage ELSE 0 END) as piece_wage,
                     SUM(CASE WHEN w.work_type = "time" THEN w.total_wage ELSE 0 END) as time_wage,
                     SUM(w.total_wage) as total_wage,
                     SUM(CASE WHEN w.work_type = "piece" THEN w.quantity ELSE 0 END) as piece_count,
                     SUM(CASE WHEN w.work_type = "time" THEN w.work_hours ELSE 0 END) as time_hours')
            ->group('w.user_id, u.nickname')
            ->order('total_wage', 'desc');
        if ($tenantId > 0) {
            $query->where('w.tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('w.tenant_id', $tenantParam);
            }
        }
        
        $startDate = $this->request->get('start_date');
        $endDate = $this->request->get('end_date');
        if ($startDate) {
            $query->where('w.work_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('w.work_date', '<=', $endDate);
        }
        
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 导出工资明细
     */
    public function export(): Response
    {
        $tenantId = $this->getTenantId();
        $startDate = $this->request->get('start_date');
        $endDate = $this->request->get('end_date');
        
        $query = WageModel::order('work_date', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }
        if ($startDate) {
            $query->where('work_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('work_date', '<=', $endDate);
        }
        
        $list = $query->select()->toArray();
        
        // 这里可以实现Excel导出功能
        return $this->success('导出功能开发中', $list);
    }
}
