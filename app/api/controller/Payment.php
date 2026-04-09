<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\lib\payment\PaymentService;
use think\facade\Request;
use think\Response;

/**
 * 支付 API：异步通知（无需登录）、创建订单（可被后台或业务侧调用）
 */
class Payment extends BaseController
{
    /**
     * 支付异步通知（由第三方支付平台 POST 调用）
     * URL 示例：/api/payment/notify/1 其中 1 为 payment_gateway.id
     */
    public function notify(): Response
    {
        $gatewayId = (int) Request::param('gateway_id', 0);
        if ($gatewayId <= 0) {
            return Response::create('fail', 'html', 200)->header(['Content-Type' => 'text/plain; charset=utf-8']);
        }
        $input = Request::post();
        if (empty($input)) {
            $input = Request::param();
        }
        $result = PaymentService::handleNotify($gatewayId, $input);
        if ($result['handled'] && $result['message'] === 'success') {
            return Response::create('success', 'html', 200)->header(['Content-Type' => 'text/plain; charset=utf-8']);
        }
        return Response::create('fail', 'html', 200)->header(['Content-Type' => 'text/plain; charset=utf-8']);
    }

    /**
     * 创建支付订单（需由后台或内部调用，传 gateway_id, order_no, amount, title, notify_url, return_url）
     * 建议：后台 TenantOrder 等先校验权限再调本接口
     */
    public function create(): Response
    {
        $gatewayId = (int) Request::post('gateway_id', 0);
        $orderNo = trim((string) Request::post('order_no', ''));
        $amount = (float) Request::post('amount', 0);
        $title = trim((string) Request::post('title', '订单'));
        $notifyUrl = trim((string) Request::post('notify_url', ''));
        $returnUrl = trim((string) Request::post('return_url', ''));
        $tenantId = (int) Request::post('tenant_id', 0);
        if ($gatewayId <= 0 || $orderNo === '' || $amount <= 0) {
            return json(['code' => 0, 'msg' => '参数不完整']);
        }
        $baseUrl = Request::domain();
        if ($notifyUrl === '') {
            $notifyUrl = $baseUrl . '/api/payment/notify/' . $gatewayId;
        }
        if (strpos($notifyUrl, 'http') !== 0) {
            $notifyUrl = $baseUrl . '/' . ltrim($notifyUrl, '/');
        }
        if ($returnUrl !== '' && strpos($returnUrl, 'http') !== 0) {
            $returnUrl = $baseUrl . '/' . ltrim($returnUrl, '/');
        }
        $ret = PaymentService::create($gatewayId, $orderNo, $amount, $title, $notifyUrl, $returnUrl, $tenantId);
        if (!empty($ret['error'])) {
            return json(['code' => 0, 'msg' => $ret['error']]);
        }
        $data = ['pay_url' => $ret['pay_url'] ?? '', 'form_html' => $ret['form_html'] ?? ''];
        return json(['code' => 1, 'msg' => 'ok', 'data' => $data]);
    }
}
