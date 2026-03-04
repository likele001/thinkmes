<?php
declare(strict_types=1);

namespace app\common\lib\payment;

/**
 * 支付网关抽象：下单与异步通知验签
 */
abstract class AbstractGateway
{
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * 创建支付订单，返回跳转 URL 或表单 HTML
     * @param array $params order_no, amount, title, notify_url, return_url, [attach]
     * @return array ['pay_url' => string] 或 ['form_html' => string]，失败 ['error' => string]
     */
    abstract public function createOrder(array $params): array;

    /**
     * 验证异步通知并解析订单结果
     * @param array $input 通常为 $_POST 或 request()->post()
     * @return array ['success' => bool, 'order_no' => string, 'transaction_id' => string, 'amount' => float] 失败 success=false
     */
    abstract public function verifyNotify(array $input): array;

    /** 网关编码，与 payment_gateway.code 对应 */
    abstract public static function code(): string;

    /** 显示名称 */
    abstract public static function name(): string;

    /** 所需配置项说明，用于后台表单 */
    public static function configFields(): array
    {
        return [];
    }

    protected function post(string $url, array $data, int $timeout = 15): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err !== '') {
            throw new \RuntimeException('请求失败: ' . $err);
        }
        return (string) $body;
    }
}
