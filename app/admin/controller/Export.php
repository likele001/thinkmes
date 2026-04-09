<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use think\Response;

class Export extends Backend
{
    public function index(): string|Response
    {
        View::assign('title', '数据导出');
        return $this->fetchWithLayout('export/index');
    }

    protected function csv(string $filename, array $header, array $rows): Response
    {
        $csv = "\xEF\xBB\xBF";
        $csv .= implode(',', $header) . "\n";
        foreach ($rows as $row) {
            $escaped = [];
            foreach ($row as $v) {
                $s = (string) ($v ?? '');
                $s = str_replace('"', '""', $s);
                if (preg_match('/[,"\r\n]/', $s)) $s = '"' . $s . '"';
                $escaped[] = $s;
            }
            $csv .= implode(',', $escaped) . "\n";
        }
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function download(): Response
    {
        $tenantId = $this->getTenantId();
        $type = trim((string) $this->request->get('type', ''));
        if ($type === '') return $this->error('type required');

        $orderNo = trim((string) $this->request->get('order_no', ''));
        $from = trim((string) $this->request->get('from', ''));
        $to = trim((string) $this->request->get('to', ''));
        $fromTs = $from !== '' ? strtotime($from . ' 00:00:00') : null;
        $toTs = $to !== '' ? strtotime($to . ' 23:59:59') : null;

        if ($type === 'orders') {
            $q = Db::name('mes_order')->alias('o')
                ->leftJoin('mes_customer c', 'c.id = o.customer_id AND c.tenant_id = o.tenant_id')
                ->where('o.tenant_id', $tenantId)
                ->order('o.id', 'desc');
            if ($orderNo !== '') $q->whereLike('o.order_no', '%' . $orderNo . '%');
            if ($fromTs) $q->where('o.create_time', '>=', $fromTs);
            if ($toTs) $q->where('o.create_time', '<=', $toTs);
            $list = $q->limit(10000)->field('o.id,o.order_no,c.name as customer_name,o.status,o.total_quantity,o.create_time')->select()->toArray();
            $rows = [];
            foreach ($list as $r) {
                $rows[] = [
                    $r['id'] ?? '',
                    $r['order_no'] ?? '',
                    $r['customer_name'] ?? '',
                    $r['status'] ?? '',
                    $r['total_quantity'] ?? '',
                    !empty($r['create_time']) ? date('Y-m-d H:i:s', (int) $r['create_time']) : '',
                ];
            }
            return $this->csv('orders_' . date('YmdHis') . '.csv', ['ID', '订单号', '客户', '状态', '总数量', '创建时间'], $rows);
        }

        if ($type === 'plans') {
            $q = Db::name('mes_production_plan')->alias('p')
                ->leftJoin('mes_order o', 'o.id = p.order_id AND o.tenant_id = p.tenant_id')
                ->leftJoin('mes_product_model m', 'm.id = p.model_id AND m.tenant_id = p.tenant_id')
                ->leftJoin('mes_product prd', 'prd.id = m.product_id AND prd.tenant_id = m.tenant_id')
                ->where('p.tenant_id', $tenantId)
                ->order('p.id', 'desc');
            if ($orderNo !== '') $q->whereLike('o.order_no', '%' . $orderNo . '%');
            if ($fromTs) $q->where('p.create_time', '>=', $fromTs);
            if ($toTs) $q->where('p.create_time', '<=', $toTs);
            $list = $q->limit(10000)->field('p.id,p.plan_code,o.order_no,prd.name as product_name,m.name as model_name,p.total_quantity,p.completed_quantity,p.status,p.create_time')->select()->toArray();
            $rows = [];
            foreach ($list as $r) {
                $rows[] = [
                    $r['id'] ?? '',
                    $r['plan_code'] ?? '',
                    $r['order_no'] ?? '',
                    $r['product_name'] ?? '',
                    $r['model_name'] ?? '',
                    $r['total_quantity'] ?? '',
                    $r['completed_quantity'] ?? '',
                    $r['status'] ?? '',
                    !empty($r['create_time']) ? date('Y-m-d H:i:s', (int) $r['create_time']) : '',
                ];
            }
            return $this->csv('plans_' . date('YmdHis') . '.csv', ['ID', '计划编码', '订单号', '产品', '型号', '计划数量', '完成数量', '状态', '创建时间'], $rows);
        }

        if ($type === 'allocations') {
            $q = Db::name('mes_allocation')->alias('a')
                ->leftJoin('mes_order o', 'o.id = a.order_id AND o.tenant_id = a.tenant_id')
                ->leftJoin('mes_production_plan p', 'p.id = a.plan_id AND p.tenant_id = a.tenant_id')
                ->leftJoin('mes_product_model m', 'm.id = a.model_id AND m.tenant_id = a.tenant_id')
                ->leftJoin('mes_product prd', 'prd.id = m.product_id AND prd.tenant_id = m.tenant_id')
                ->leftJoin('mes_process pr', 'pr.id = a.process_id AND pr.tenant_id = a.tenant_id')
                ->leftJoin('user u', 'u.id = a.user_id')
                ->where('a.tenant_id', $tenantId)
                ->order('a.id', 'desc');
            if ($orderNo !== '') $q->whereLike('o.order_no', '%' . $orderNo . '%');
            if ($fromTs) $q->where('a.create_time', '>=', $fromTs);
            if ($toTs) $q->where('a.create_time', '<=', $toTs);
            $list = $q->limit(10000)->field('a.id,o.order_no,p.plan_code,prd.name as product_name,m.name as model_name,pr.name as process_name,u.nickname,u.username,a.quantity,a.completed_quantity,a.status,a.create_time')->select()->toArray();
            $rows = [];
            foreach ($list as $r) {
                $rows[] = [
                    $r['id'] ?? '',
                    $r['order_no'] ?? '',
                    $r['plan_code'] ?? '',
                    $r['product_name'] ?? '',
                    $r['model_name'] ?? '',
                    $r['process_name'] ?? '',
                    ($r['nickname'] ?? '') !== '' ? $r['nickname'] : ($r['username'] ?? ''),
                    $r['quantity'] ?? '',
                    $r['completed_quantity'] ?? '',
                    $r['status'] ?? '',
                    !empty($r['create_time']) ? date('Y-m-d H:i:s', (int) $r['create_time']) : '',
                ];
            }
            return $this->csv('allocations_' . date('YmdHis') . '.csv', ['ID', '订单号', '计划', '产品', '型号', '工序', '员工', '分配数量', '完成数量', '状态', '创建时间'], $rows);
        }

        if ($type === 'reports') {
            $q = Db::name('mes_report')->alias('r')
                ->leftJoin('mes_allocation a', 'a.id = r.allocation_id AND a.tenant_id = r.tenant_id')
                ->leftJoin('mes_order o', 'o.id = a.order_id AND o.tenant_id = a.tenant_id')
                ->leftJoin('mes_product_model m', 'm.id = a.model_id AND m.tenant_id = a.tenant_id')
                ->leftJoin('mes_product prd', 'prd.id = m.product_id AND prd.tenant_id = m.tenant_id')
                ->leftJoin('mes_process pr', 'pr.id = a.process_id AND pr.tenant_id = a.tenant_id')
                ->leftJoin('user u', 'u.id = r.user_id')
                ->where('r.tenant_id', $tenantId)
                ->order('r.id', 'desc');
            if ($orderNo !== '') $q->whereLike('o.order_no', '%' . $orderNo . '%');
            if ($fromTs) $q->where('r.create_time', '>=', $fromTs);
            if ($toTs) $q->where('r.create_time', '<=', $toTs);
            $status = $this->request->get('status', '');
            if ($status !== '' && $status !== null) $q->where('r.status', (int) $status);
            $list = $q->limit(10000)->field('r.id,o.order_no,prd.name as product_name,m.name as model_name,pr.name as process_name,u.nickname,u.username,r.quantity,r.status,r.create_time')->select()->toArray();
            $rows = [];
            foreach ($list as $r) {
                $rows[] = [
                    $r['id'] ?? '',
                    $r['order_no'] ?? '',
                    $r['product_name'] ?? '',
                    $r['model_name'] ?? '',
                    $r['process_name'] ?? '',
                    ($r['nickname'] ?? '') !== '' ? $r['nickname'] : ($r['username'] ?? ''),
                    $r['quantity'] ?? '',
                    $r['status'] ?? '',
                    !empty($r['create_time']) ? date('Y-m-d H:i:s', (int) $r['create_time']) : '',
                ];
            }
            return $this->csv('reports_' . date('YmdHis') . '.csv', ['ID', '订单号', '产品', '型号', '工序', '报工人', '报工数量', '状态', '报工时间'], $rows);
        }

        return $this->error('unknown type');
    }
}
