<?php
declare(strict_types=1);

namespace app\api\controller\restaurant;

use app\common\controller\BaseController;
use app\admin\model\restaurant\OrderModel;
use app\admin\model\restaurant\TableModel;
use app\common\lib\payment\PaymentService;
use think\facade\Db;
use think\Response;

class Payment extends BaseController
{
    protected function tenantId(): int
    {
        $tenantId = (int) ($this->request->tenantId ?? 0);
        if ($tenantId <= 0) {
            $tenantId = (int) $this->request->param('tenant_id', 0);
        }
        return $tenantId;
    }

    protected function tableByToken(int $tenantId, string $token): ?TableModel
    {
        return TableModel::where('tenant_id', $tenantId)->where('qr_token', $token)->where('status', 1)->find();
    }

    public function gateways(): Response
    {
        $tenantId = $this->tenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $rows = Db::name('payment_gateway')
            ->where('enabled', 1)
            ->order('id', 'asc')
            ->field('id,name,code')
            ->select()
            ->toArray();
        return $this->success('', ['list' => $rows]);
    }

    public function pay(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $tenantId = $this->tenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $token = trim((string) $this->request->post('token', ''));
        $gatewayId = (int) $this->request->post('gateway_id', 0);
        $orderId = (int) $this->request->post('order_id', 0);
        $returnUrl = trim((string) $this->request->post('return_url', ''));

        if ($token === '' || $gatewayId <= 0 || $orderId <= 0) {
            return $this->error('参数不完整');
        }
        $table = $this->tableByToken($tenantId, $token);
        if (!$table) {
            return $this->error('桌台不存在或已禁用');
        }
        $order = OrderModel::where('tenant_id', $tenantId)->where('table_id', (int) $table->id)->find($orderId);
        if (!$order) {
            return $this->error('订单不存在');
        }
        if ((int) $order->status === 4) {
            return $this->error('订单已结账');
        }

        $notifyUrl = $this->request->domain() . '/api/payment/notify/' . $gatewayId;
        $ret = PaymentService::create(
            $gatewayId,
            (string) $order->order_no,
            (float) $order->total_amount,
            '餐饮订单：' . (string) $order->order_no,
            $notifyUrl,
            $returnUrl,
            $tenantId
        );
        if (!empty($ret['error'])) {
            return $this->error((string) $ret['error']);
        }
        return $this->success('ok', ['pay_url' => $ret['pay_url'] ?? '', 'form_html' => $ret['form_html'] ?? '']);
    }
}

