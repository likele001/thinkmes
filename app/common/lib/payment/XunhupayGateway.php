<?php
declare(strict_types=1);

namespace app\common\lib\payment;

/**
 * 虎皮椒/讯虎支付 https://www.xunhupay.com/doc/api/pay.html
 * 支持支付宝、微信（通过 type 或不同 appid 区分，此处用 code: xunhupay_alipay / xunhupay_wechat）
 */
class XunhupayGateway extends AbstractGateway
{
    private const API_URL = 'https://api.xunhupay.com/payment/do.html';
    private const API_URL_BACKUP = 'https://api.dpweixin.com/payment/do.html';

    public static function code(): string
    {
        return 'xunhupay'; // 实际创建网关时可选 xunhupay_alipay / xunhupay_wechat，配置里存 type
    }

    public static function name(): string
    {
        return '虎皮椒(讯虎)';
    }

    public static function configFields(): array
    {
        return [
            'appid'     => ['label' => 'APP ID', 'required' => true],
            'appsecret' => ['label' => 'APP Secret', 'required' => true, 'type' => 'password'],
            'type'      => ['label' => '支付类型', 'required' => true, 'options' => ['alipay' => '支付宝', 'wechat' => '微信']],
            'api_url'   => ['label' => '接口地址', 'default' => self::API_URL],
        ];
    }

    public function createOrder(array $params): array
    {
        $appid = $this->config['appid'] ?? '';
        $secret = $this->config['appsecret'] ?? '';
        $apiUrl = trim($this->config['api_url'] ?? self::API_URL) ?: self::API_URL;
        if ($appid === '' || $secret === '') {
            return ['error' => '请配置 APP ID 和 APP Secret'];
        }
        $orderNo = $params['order_no'] ?? '';
        $amount = (float) ($params['amount'] ?? 0);
        $title = mb_substr($params['title'] ?? '订单', 0, 42);
        $notifyUrl = $params['notify_url'] ?? '';
        $returnUrl = $params['return_url'] ?? '';
        if ($orderNo === '' || $amount <= 0 || $notifyUrl === '') {
            return ['error' => '缺少 order_no / amount / notify_url'];
        }
        $time = time();
        $nonce = md5(uniqid((string) mt_rand(), true));
        $data = [
            'version'        => '1.1',
            'appid'          => $appid,
            'trade_order_id' => $orderNo,
            'total_fee'      => (string) $amount,
            'title'          => $title,
            'time'           => (string) $time,
            'notify_url'     => $notifyUrl,
            'return_url'     => $returnUrl,
            'nonce_str'      => $nonce,
        ];
        $data['hash'] = $this->sign($data, $secret);
        try {
            $body = $this->post($apiUrl, $data);
            $json = json_decode($body, true);
            if (!is_array($json)) {
                return ['error' => '接口返回异常: ' . $body];
            }
            if (isset($json['errcode']) && (int) $json['errcode'] !== 0) {
                return ['error' => $json['errmsg'] ?? '下单失败'];
            }
            $payUrl = $json['url'] ?? $json['url_qrcode'] ?? '';
            if ($payUrl === '') {
                return ['error' => '未返回支付链接'];
            }
            return ['pay_url' => $payUrl];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function verifyNotify(array $input): array
    {
        $secret = $this->config['appsecret'] ?? '';
        if ($secret === '') {
            return ['success' => false, 'order_no' => '', 'transaction_id' => '', 'amount' => 0];
        }
        $hash = $input['hash'] ?? '';
        $orderNo = $input['trade_order_id'] ?? '';
        $totalFee = (float) ($input['total_fee'] ?? 0);
        $transactionId = $input['transaction_id'] ?? $input['open_order_id'] ?? '';
        $status = $input['status'] ?? '';
        if ($orderNo === '') {
            return ['success' => false, 'order_no' => '', 'transaction_id' => '', 'amount' => 0];
        }
        $sign = $this->sign($input, $secret);
        if (!hash_equals($sign, $hash)) {
            return ['success' => false, 'order_no' => $orderNo, 'transaction_id' => $transactionId, 'amount' => $totalFee];
        }
        $paid = (strtoupper($status) === 'OD');
        return [
            'success'        => $paid,
            'order_no'       => $orderNo,
            'transaction_id' => $transactionId,
            'amount'         => $totalFee,
        ];
    }

    private function sign(array $data, string $secret): string
    {
        unset($data['hash']);
        ksort($data);
        $arr = [];
        foreach ($data as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $arr[] = $k . '=' . $v;
        }
        return md5(implode('&', $arr) . $secret);
    }
}
