<?php
declare(strict_types=1);

namespace app\admin\controller\payment;

use app\admin\controller\Backend;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 支付回调日志（单用户版，不分租户）
 */
class CallbackLog extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            $query = Db::name('payment_callback_log')->alias('l')
                ->leftJoin('payment_gateway g', 'l.gateway_id = g.id')
                ->field('l.*, g.name as gateway_name')
                ->order('l.id', 'desc');
            $orderNo = trim((string) $this->request->get('order_no'));
            if ($orderNo !== '') {
                $query->where('l.order_no', 'like', '%' . $orderNo . '%');
            }
            $gatewayId = (int) $this->request->get('gateway_id', 0);
            if ($gatewayId > 0) {
                $query->where('l.gateway_id', $gatewayId);
            }
            $result = trim((string) $this->request->get('result'));
            if ($result !== '') {
                $query->where('l.result', $result);
            }
            $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
            $page = max(1, (int) $this->request->get('page', 1));
            $total = $query->count();
            $list = $query->page($page, $limit)->select()->toArray();
            foreach ($list as &$row) {
                $row['create_time_text'] = $row['create_time'] ? date('Y-m-d H:i:s', (int) $row['create_time']) : '-';
            }
            return $this->success('', ['total' => $total, 'list' => $list]);
        }
        View::assign('title', '回调日志');
        return $this->fetchWithLayout('payment/callback_log/index');
    }
}
