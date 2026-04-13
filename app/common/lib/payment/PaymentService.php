<?php
declare(strict_types=1);

namespace app\common\lib\payment;

use think\facade\Db;

/**
 * 支付统一服务：创建支付订单、处理回调
 */
class PaymentService
{
    /**
     * 创建支付订单并返回支付链接或表单
     * @param int $gatewayId payment_gateway.id
     * @param string $orderNo 业务订单号（唯一）
     * @param float $amount 金额(元)
     * @param string $title 订单标题
     * @param string $notifyUrl 异步通知地址
     * @param string $returnUrl 同步跳转地址
     * @param int $tenantId 租户ID
     * @return array ['pay_url' => ?string, 'form_html' => ?string, 'error' => ?string]
     */
    public static function create(int $gatewayId, string $orderNo, float $amount, string $title, string $notifyUrl, string $returnUrl = '', int $tenantId = 0): array
    {
        $gateway = Db::name('payment_gateway')->where('id', $gatewayId)->where('enabled', 1)->find();
        if (!$gateway) {
            return ['error' => '支付通道不存在或已关闭'];
        }
        $config = $gateway['config'] ? json_decode($gateway['config'], true) : [];
        $config = is_array($config) ? $config : [];
        if ($notifyUrl === '') {
            $notifyUrl = trim((string) ($config['notify_url'] ?? ''));
        }
        if ($returnUrl === '') {
            $returnUrl = trim((string) ($config['return_url'] ?? ''));
        }
        $gw = PaymentGatewayFactory::get($gateway['code'], $config);
        if (!$gw) {
            return ['error' => '不支持的支付类型'];
        }
        if ($notifyUrl === '') {
            return ['error' => '缺少异步通知URL'];
        }
        $exists = Db::name('payment_order')->where('order_no', $orderNo)->find();
        if ($exists) {
            if ((int) $exists['status'] === 1) {
                return ['error' => '订单已支付'];
            }
            Db::name('payment_order')->where('order_no', $orderNo)->update([
                'gateway_id'   => $gatewayId,
                'gateway_code' => $gateway['code'],
                'amount'       => $amount,
                'title'        => $title,
                'status'       => 0,
                'extra'        => json_encode(['notify_url' => $notifyUrl, 'return_url' => $returnUrl], JSON_UNESCAPED_UNICODE),
                'update_time'  => time(),
            ]);
        } else {
            Db::name('payment_order')->insert([
                'order_no'     => $orderNo,
                'tenant_id'    => $tenantId,
                'amount'       => $amount,
                'title'        => $title,
                'gateway_id'   => $gatewayId,
                'gateway_code' => $gateway['code'],
                'status'       => 0,
                'create_time'  => time(),
                'update_time'  => time(),
                'extra'        => json_encode(['notify_url' => $notifyUrl, 'return_url' => $returnUrl], JSON_UNESCAPED_UNICODE),
            ]);
        }
        $params = [
            'order_no'     => $orderNo,
            'amount'       => $amount,
            'title'        => $title,
            'notify_url'   => $notifyUrl,
            'return_url'   => $returnUrl,
        ];
        if ($gateway['code'] === 'epay') {
            $params['pay_type'] = (strpos($gateway['name'], '微信') !== false) ? 'wxpay' : 'alipay';
        }
        $result = $gw->createOrder($params);
        if (!empty($result['error'])) {
            return $result;
        }
        return $result;
    }

    /**
     * 处理异步通知，验签后更新 payment_order 并可选更新 tenant_order
     * @param int $gatewayId payment_gateway.id
     * @param array $input POST 数据
     * @return array ['handled' => bool, 'order_no' => string, 'message' => string] 若已处理需返回 success 给第三方
     */
    public static function handleNotify(int $gatewayId, array $input): array
    {
        $gateway = Db::name('payment_gateway')->where('id', $gatewayId)->find();
        if (!$gateway) {
            self::logCallback($gatewayId, '', $input, 'fail');
            return ['handled' => false, 'order_no' => '', 'message' => 'gateway not found'];
        }
        $config = $gateway['config'] ? json_decode($gateway['config'], true) : [];
        $config = is_array($config) ? $config : [];
        $gw = PaymentGatewayFactory::get($gateway['code'], $config);
        if (!$gw) {
            self::logCallback($gatewayId, '', $input, 'fail');
            return ['handled' => false, 'order_no' => '', 'message' => 'gateway not supported'];
        }
        $verify = $gw->verifyNotify($input);
        $orderNo = $verify['order_no'] ?? '';
        if ($orderNo === '') {
            self::logCallback($gatewayId, '', $input, 'fail');
            return ['handled' => false, 'order_no' => '', 'message' => 'invalid notify'];
        }
        $po = Db::name('payment_order')->where('order_no', $orderNo)->find();
        if (!$po) {
            self::logCallback($gatewayId, $orderNo, $input, 'order_not_found');
            return ['handled' => true, 'order_no' => $orderNo, 'message' => 'order not found'];
        }
        if ((int) $po['status'] === 1) {
            self::logCallback($gatewayId, $orderNo, $input, 'success');
            return ['handled' => true, 'order_no' => $orderNo, 'message' => 'success'];
        }
        if (!$verify['success']) {
            self::logCallback($gatewayId, $orderNo, $input, 'not_paid');
            return ['handled' => true, 'order_no' => $orderNo, 'message' => 'not paid'];
        }
        $now = time();
        Db::name('payment_order')->where('order_no', $orderNo)->update([
            'status'         => 1,
            'third_order_id' => $verify['transaction_id'] ?? '',
            'pay_time'       => $now,
            'update_time'    => $now,
        ]);
        try {
            if (Db::name('tenant_order')->where('order_no', $orderNo)->find()) {
                Db::name('tenant_order')->where('order_no', $orderNo)->update([
                    'status'      => 1,
                    'pay_method'  => $gateway['code'],
                    'pay_time'    => $now,
                    'update_time' => $now,
                ]);
            }
        } catch (\Throwable $e) {
            // tenant_order 表可能未安装，忽略
        }
        try {
            if (Db::name('restaurant_order')->where('order_no', $orderNo)->find()) {
                Db::name('restaurant_order')->where('order_no', $orderNo)->update([
                    'status'      => 4,
                    'update_time' => $now,
                ]);
            }
        } catch (\Throwable $e) {
        }
        try {
            if (Db::name('market_plugin_order')->where('order_no', $orderNo)->find()) {
                Db::name('market_plugin_order')->where('order_no', $orderNo)->update([
                    'status'      => 1,
                    'pay_time'    => $now,
                    'update_time' => $now,
                ]);
            }
        } catch (\Throwable $e) {
        }
        self::logCallback($gatewayId, $orderNo, $input, 'success');
        return ['handled' => true, 'order_no' => $orderNo, 'message' => 'success'];
    }

    private static function logCallback(int $gatewayId, string $orderNo, array $input, string $result): void
    {
        try {
            Db::name('payment_callback_log')->insert([
                'gateway_id'  => $gatewayId,
                'order_no'    => $orderNo,
                'raw'         => json_encode($input, JSON_UNESCAPED_UNICODE),
                'result'      => $result,
                'create_time' => time(),
            ]);
        } catch (\Throwable $e) {
            // 表可能未创建
        }
    }
}
