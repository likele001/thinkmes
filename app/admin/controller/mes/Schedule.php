<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ScheduleTaskModel;
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
        $r = PieceScheduler::generate($tenantId, $startDate, $days, $reset);
        if (!($r['ok'] ?? false)) return $this->error((string) ($r['error'] ?? '生成失败'));
        return $this->success('已生成', $r);
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
            $groups[$k] = ($groups[$k] ?? 0) + $qty;
        }
        if (!$groups) return $this->error('排产任务数据异常');

        $now = time();
        $allocRows = [];
        foreach ($groups as $k => $qty) {
            [$orderId, $planId, $modelId, $processId, $userId] = array_map('intval', explode(':', $k));
            $allocRows[] = [
                'tenant_id' => $tenantId,
                'plan_id' => $planId ?: null,
                'order_id' => $orderId,
                'model_id' => $modelId,
                'process_id' => $processId,
                'user_id' => $userId,
                'quantity' => $qty,
                'completed_quantity' => 0,
                'status' => 0,
                'create_time' => $now,
                'update_time' => $now,
            ];
        }

        Db::startTrans();
        try {
            Db::name('mes_allocation')->insertAll($allocRows);
            Db::name('mes_schedule_task')
                ->where('tenant_id', $tenantId)
                ->where('batch_id', $batchId)
                ->where('status', 0)
                ->update(['status' => 1]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('下发失败：' . $e->getMessage());
        }
        return $this->success('已下发', ['allocations' => count($allocRows)]);
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

