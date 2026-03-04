<?php
declare(strict_types=1);

namespace app\admin\controller\payment;

use app\admin\controller\Backend;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 支付订单列表（仅查看）
 */
class Order extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            $tenantId = $this->getTenantId();
            $query = Db::name('payment_order')->alias('o')
                ->leftJoin('payment_gateway g', 'o.gateway_id = g.id')
                ->field('o.*, g.name as gateway_name')
                ->order('o.id', 'desc');
            if ($tenantId > 0) {
                $query->where('o.tenant_id', $tenantId);
            } else {
                $tid = (int) $this->request->get('tenant_id', 0);
                if ($tid > 0) {
                    $query->where('o.tenant_id', $tid);
                }
            }
            $orderNo = trim((string) $this->request->get('order_no'));
            if ($orderNo !== '') {
                $query->where('o.order_no', 'like', '%' . $orderNo . '%');
            }
            $status = $this->request->get('status');
            if ($status !== '' && $status !== null) {
                $query->where('o.status', (int) $status);
            }
            $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
            $page = max(1, (int) $this->request->get('page', 1));
            $total = $query->count();
            $list = $query->page($page, $limit)->select()->toArray();
            $statusMap = [0 => '待支付', 1 => '已支付', 2 => '已关闭', 3 => '已退款'];
            foreach ($list as &$row) {
                $row['status_text'] = $statusMap[$row['status']] ?? '-';
                $row['pay_time_text'] = $row['pay_time'] ? date('Y-m-d H:i:s', (int) $row['pay_time']) : '-';
                $row['create_time_text'] = $row['create_time'] ? date('Y-m-d H:i:s', (int) $row['create_time']) : '-';
            }
            return $this->success('', ['total' => $total, 'list' => $list]);
        }
        View::assign('title', '支付订单');
        return $this->fetchWithLayout('payment/order/index');
    }
}
