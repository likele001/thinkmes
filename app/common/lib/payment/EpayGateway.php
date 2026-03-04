<?php
declare(strict_types=1);

namespace app\common\lib\payment;

/**
 * 易支付（个人版，兼容 8-pay 等）
 * 文档参考：https://www.8-pay.cn/doc 等
 * 参数：pid, key, type(alipay/wxpay), submit_url(提交地址), notify 回调验签
 */
class EpayGateway extends AbstractGateway
{
    public static function code(): string
    {
        return 'epay';
    }

    public static function name(): string
    {
        return '易支付(8-pay等)';
    }

    public static function configFields(): array
    {
        return [
            'pid'        => ['label' => '商户ID', 'required' => true],
            'key'        => ['label' => '商户密钥', 'required' => true, 'type' => 'password'],
            'submit_url' => ['label' => '提交地址', 'required' => true, 'placeholder' => 'https://xxx.com/submit.php'],
            'type'       => ['label' => '默认支付类型', 'options' => ['alipay' => '支付宝', 'wxpay' => '微信']],
        ];
    }

    public function createOrder(array $params): array
    {
        $pid = $this->config['pid'] ?? '';
        $key = $this->config['key'] ?? '';
        $submitUrl = rtrim($this->config['submit_url'] ?? '', '/');
        if ($pid === '' || $key === '' || $submitUrl === '') {
            return ['error' => '请配置商户ID、密钥和提交地址'];
        }
        $orderNo = $params['order_no'] ?? '';
        $amount = (float) ($params['amount'] ?? 0);
        $title = $params['title'] ?? '订单';
        $notifyUrl = $params['notify_url'] ?? '';
        $returnUrl = $params['return_url'] ?? '';
        $type = $params['pay_type'] ?? $this->config['type'] ?? 'alipay';
        if ($orderNo === '' || $amount <= 0 || $notifyUrl === '') {
            return ['error' => '缺少 order_no / amount / notify_url'];
        }
        $data = [
            'pid'          => $pid,
            'type'         => $type,
            'out_trade_no' => $orderNo,
            'notify_url'   => $notifyUrl,
            'return_url'   => $returnUrl,
            'name'         => mb_substr($title, 0, 64),
            'money'        => (string) $amount,
        ];
        $data['sign'] = $this->sign($data, $key);
        $data['sign_type'] = 'MD5';
        $formHtml = '<form id="epay_form" action="' . htmlspecialchars($submitUrl) . '" method="POST">';
        foreach ($data as $k => $v) {
            $formHtml .= '<input type="hidden" name="' . htmlspecialchars($k) . '" value="' . htmlspecialchars((string) $v) . '" />';
        }
        $formHtml .= '</form><script>document.getElementById("epay_form").submit();</script>';
        return ['form_html' => $formHtml, 'post_url' => $submitUrl, 'post_data' => $data];
    }

    public function verifyNotify(array $input): array
    {
        $key = $this->config['key'] ?? '';
        if ($key === '') {
            return ['success' => false, 'order_no' => '', 'transaction_id' => '', 'amount' => 0];
        }
        $orderNo = $input['out_trade_no'] ?? '';
        $sign = $input['sign'] ?? '';
        $tradeStatus = $input['trade_status'] ?? $input['status'] ?? '';
        $amount = (float) ($input['money'] ?? $input['total_fee'] ?? 0);
        $transactionId = $input['trade_no'] ?? $input['transaction_id'] ?? '';
        if ($orderNo === '') {
            return ['success' => false, 'order_no' => '', 'transaction_id' => '', 'amount' => 0];
        }
        $mySign = $this->sign($input, $key);
        if (!hash_equals($mySign, $sign)) {
            return ['success' => false, 'order_no' => $orderNo, 'transaction_id' => $transactionId, 'amount' => $amount];
        }
        $paid = ($tradeStatus === 'TRADE_SUCCESS' || $tradeStatus === 'success' || $tradeStatus === '1');
        return [
            'success'        => $paid,
            'order_no'       => $orderNo,
            'transaction_id' => $transactionId,
            'amount'         => $amount,
        ];
    }

    private function sign(array $data, string $key): string
    {
        unset($data['sign'], $data['sign_type']);
        ksort($data);
        $arr = [];
        foreach ($data as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $arr[] = $k . '=' . $v;
        }
        return md5(implode('&', $arr) . $key);
    }
}
