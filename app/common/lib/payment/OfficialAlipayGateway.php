<?php
declare(strict_types=1);

namespace app\common\lib\payment;

/**
 * 官方支付宝（预留，可后续接入 alipay-sdk）
 * 需配置：app_id, private_key, alipay_public_key, [notify_url 可选]
 */
class OfficialAlipayGateway extends AbstractGateway
{
    public static function code(): string
    {
        return 'official_alipay';
    }

    public static function name(): string
    {
        return '官方支付宝';
    }

    public static function configFields(): array
    {
        return [
            'app_id'            => ['label' => '应用 APP ID', 'required' => true],
            'private_key'       => ['label' => '应用私钥', 'required' => true, 'type' => 'textarea'],
            'alipay_public_key' => ['label' => '支付宝公钥', 'required' => true, 'type' => 'textarea'],
            'gateway_url'       => ['label' => '网关地址', 'default' => 'https://openapi.alipay.com/gateway.do'],
        ];
    }

    public function createOrder(array $params): array
    {
        $appId = $this->config['app_id'] ?? '';
        $privateKey = $this->config['private_key'] ?? '';
        if ($appId === '' || $privateKey === '') {
            return ['error' => '请先配置应用 APP ID 和应用私钥'];
        }
        // 预留：可在此接入 alipay-sdk 的 page pay
        return ['error' => '官方支付宝需接入支付宝开放平台 SDK，请先安装 alipay/easysdk 或 alipay-sdk-php 后在此实现'];
    }

    public function verifyNotify(array $input): array
    {
        return ['success' => false, 'order_no' => $input['out_trade_no'] ?? '', 'transaction_id' => $input['trade_no'] ?? '', 'amount' => (float) ($input['total_amount'] ?? 0)];
    }
}
