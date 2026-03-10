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
 * 工资管理（参考 scanwork：明细列表 + 统计汇总 + 趋势图）
 */
class Wage extends Backend
{
    /**
     * 工资明细列表（独立页面，数据来源报工表：员工、订单、产品、工序、报工数量、计件工资、状态、报工时间，含已确认/待确认）
     */
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '工资明细');
            return $this->fetchWithLayout('mes/wage/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process', 'user'])
            ->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }

        $userId = $this->request->get('user_id');
        if ($userId !== '' && $userId !== null) {
            $query->where('user_id', (int) $userId);
        }

        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        $rows = [];
        foreach ($list as $report) {
            $row = $report->toArray();
            $allocation = $report->allocation;
            $row['order_no'] = $allocation && $allocation->order ? (string) ($allocation->order['order_no'] ?? '') : '';
            $row['product_name'] = '';
            $row['model_name'] = '';
            $row['process_name'] = '';
            if ($allocation && $allocation->model) {
                $row['model_name'] = (string) ($allocation->model['name'] ?? '');
                if ($allocation->model->product) {
                    $row['product_name'] = (string) ($allocation->model->product['name'] ?? '');
                }
            }
            if ($allocation && $allocation->process) {
                $row['process_name'] = (string) ($allocation->process['name'] ?? '');
            }
            $row['nickname'] = $report->user ? (string) ($report->user['nickname'] ?? '') : '';
            $rows[] = $row;
        }

        return $this->success('', ['total' => $total, 'list' => $rows]);
    }

    /**
     * 工资统计（仪表盘：汇总卡片 + 员工汇总表 + 趋势图，数据来源报工表）
     */
    public function statistics(): string|Response
    {
        View::assign('title', '工资统计');
        return $this->fetchWithLayout('mes/wage/statistics');
    }

    /**
     * 获取报工汇总数据（用于统计页卡片和员工表）
     */
    public function getSummary(): Response
    {
        $startDate = $this->request->param('start_date', date('Y-m-01'));
        $endDate = $this->request->param('end_date', date('Y-m-d'));
        $userId = $this->request->param('user_id', '');

        $tenantId = $this->getTenantId();
        $query = Db::name('mes_report')
            ->where('status', 1)
            ->where('create_time', '>=', strtotime($startDate . ' 00:00:00'))
            ->where('create_time', '<=', strtotime($endDate . ' 23:59:59'));
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        }
        if ($userId !== '' && $userId !== null) {
            $query->where('user_id', (int) $userId);
        }

        $summary = $query->field('user_id, SUM(quantity) as total_quantity, SUM(wage) as total_wage, COUNT(*) as report_count')
            ->group('user_id')
            ->select()
            ->toArray();

        $totalQuantity = 0;
        $totalWage = '0.00';
        $totalCount = 0;
        foreach ($summary as $row) {
            $totalQuantity += (int) ($row['total_quantity'] ?? 0);
            $totalWage = bcadd((string) $totalWage, (string) ($row['total_wage'] ?? '0'), 2);
            $totalCount += (int) ($row['report_count'] ?? 0);
        }
        $userCount = count($summary);

        // 补全 user 信息（with 可能未加载 nickname）
        $userIds = array_column($summary, 'user_id');
        $users = $userIds ? Db::name('user')->whereIn('id', $userIds)->column('nickname', 'id') : [];
        foreach ($summary as &$item) {
            $item['user'] = ['nickname' => $users[$item['user_id']] ?? '未知'];
        }
        unset($item);

        return $this->success('', [
            'summary' => $summary,
            'total' => [
                'user_count' => $userCount,
                'quantity' => $totalQuantity,
                'wage' => $totalWage,
                'count' => $totalCount,
            ],
        ]);
    }

    /**
     * 获取工资图表数据：趋势（按日）、按员工、按工序
     */
    public function getChart(): Response
    {
        $startDate = $this->request->param('start_date', date('Y-m-01'));
        $endDate = $this->request->param('end_date', date('Y-m-d'));
        $userId = $this->request->param('user_id', '');

        $tenantId = $this->getTenantId();
        $prefix = config('database.connections.mysql.prefix', 'fa_');

        $tsStart = strtotime($startDate . ' 00:00:00');
        $tsEnd = strtotime($endDate . ' 23:59:59');

        // 1. 趋势：按日汇总工资金额、报工数量
        $daily = Db::name('mes_report')->alias('r')
            ->where('r.status', 1)
            ->where('r.create_time', '>=', $tsStart)
            ->where('r.create_time', '<=', $tsEnd);
        if ($tenantId > 0) {
            $daily->where('r.tenant_id', $tenantId);
        }
        if ($userId !== '' && $userId !== null) {
            $daily->where('r.user_id', (int) $userId);
        }
        $daily = $daily
            ->field("FROM_UNIXTIME(r.create_time,'%Y-%m-%d') as date, SUM(r.quantity) as quantity, SUM(r.wage) as wage")
            ->group("FROM_UNIXTIME(r.create_time,'%Y-%m-%d')")
            ->order('date', 'asc')
            ->select()
            ->toArray();
        $dates = [];
        $quantities = [];
        $wages = [];
        foreach ($daily as $row) {
            $dates[] = $row['date'];
            $quantities[] = (int) ($row['quantity'] ?? 0);
            $wages[] = round((float) ($row['wage'] ?? 0), 2);
        }

        // 2. 按员工汇总（饼图）
        $byUser = Db::name('mes_report')->alias('r')
            ->join($prefix . 'user u', 'r.user_id = u.id')
            ->where('r.status', 1)
            ->where('r.create_time', '>=', $tsStart)
            ->where('r.create_time', '<=', $tsEnd);
        if ($tenantId > 0) {
            $byUser->where('r.tenant_id', $tenantId);
        }
        if ($userId !== '' && $userId !== null) {
            $byUser->where('r.user_id', (int) $userId);
        }
        $byUser = $byUser
            ->field('u.nickname as name, SUM(r.wage) as value')
            ->group('r.user_id, u.nickname')
            ->order('value', 'desc')
            ->select()
            ->toArray();
        $userPie = [];
        foreach ($byUser as $row) {
            $userPie[] = ['name' => (string) ($row['name'] ?? '未知'), 'value' => round((float) ($row['value'] ?? 0), 2)];
        }

        // 3. 按工序汇总（柱状图）
        $byProcess = Db::name('mes_report')->alias('r')
            ->join($prefix . 'mes_allocation a', 'r.allocation_id = a.id')
            ->join($prefix . 'mes_process p', 'a.process_id = p.id')
            ->where('r.status', 1)
            ->where('r.create_time', '>=', $tsStart)
            ->where('r.create_time', '<=', $tsEnd);
        if ($tenantId > 0) {
            $byProcess->where('r.tenant_id', $tenantId);
        }
        if ($userId !== '' && $userId !== null) {
            $byProcess->where('r.user_id', (int) $userId);
        }
        $byProcess = $byProcess
            ->field('p.name as process_name, SUM(r.wage) as wage')
            ->group('a.process_id, p.name')
            ->order('wage', 'desc')
            ->select()
            ->toArray();
        $processNames = [];
        $processWages = [];
        foreach ($byProcess as $row) {
            $processNames[] = (string) ($row['process_name'] ?? '未知');
            $processWages[] = round((float) ($row['wage'] ?? 0), 2);
        }

        return $this->success('', [
            'dates' => $dates,
            'quantities' => $quantities,
            'wages' => $wages,
            'by_user' => $userPie,
            'by_process_names' => $processNames,
            'by_process_wages' => $processWages,
        ]);
    }

    /**
     * 获取有报工记录的员工列表（用于筛选下拉）
     */
    public function getReportUsers(): Response
    {
        $tenantId = $this->getTenantId();
        $query = Db::name('mes_report')->alias('r')
            ->join('fa_user u', 'r.user_id = u.id')
            ->where('r.status', 1)
            ->field('u.id, u.nickname')
            ->group('r.user_id, u.id, u.nickname')
            ->order('u.nickname', 'asc');
        if ($tenantId > 0) {
            $query->where('r.tenant_id', $tenantId);
        }
        $list = $query->select()->toArray();
        return $this->success('', ['list' => $list]);
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
