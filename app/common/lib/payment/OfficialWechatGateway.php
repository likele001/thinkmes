<?php
declare(strict_types=1);

namespace app\common\lib\payment;

/**
 * 官方微信支付（预留，可后续接入 wechatpay-php）
 * 需配置：mch_id, api_v3_key, cert_serial_no, private_key, appid
 */
class OfficialWechatGateway extends AbstractGateway
{
    public static function code(): string
    {
        return 'official_wechat';
    }

    public static function name(): string
    {
        return '官方微信支付';
    }

    public static function configFields(): array
    {
        return [
            'mch_id'         => ['label' => '商户号', 'required' => true],
            'appid'          => ['label' => 'APP ID', 'required' => true],
            'api_v3_key'     => ['label' => 'API v3 密钥', 'required' => true, 'type' => 'password'],
            'cert_serial_no' => ['label' => '证书序列号', 'required' => true],
            'private_key'    => ['label' => '商户私钥', 'required' => true, 'type' => 'textarea'],
        ];
    }

    public function createOrder(array $params): array
    {
        $mchId = $this->config['mch_id'] ?? '';
        if ($mchId === '') {
            return ['error' => '请先配置商户号及微信支付参数'];
        }
        return ['error' => '官方微信支付需接入 wechatpay/wechatpay-php 等 SDK 后在此实现'];
    }

    public function verifyNotify(array $input): array
    {
        return ['success' => false, 'order_no' => $input['out_trade_no'] ?? '', 'transaction_id' => $input['transaction_id'] ?? '', 'amount' => 0];
    }
}
