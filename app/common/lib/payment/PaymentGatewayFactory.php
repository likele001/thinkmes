<?php
declare(strict_types=1);

namespace app\common\lib\payment;

/**
 * 根据 code 返回对应网关实例
 */
class PaymentGatewayFactory
{
    private static array $map = [
        'official_alipay'   => OfficialAlipayGateway::class,
        'official_wechat'  => OfficialWechatGateway::class,
        'xunhupay'         => XunhupayGateway::class,
        'xunhupay_alipay'  => XunhupayGateway::class,
        'xunhupay_wechat'  => XunhupayGateway::class,
        'epay'             => EpayGateway::class,
    ];

    public static function get(string $code, array $config): ?AbstractGateway
    {
        $class = self::$map[$code] ?? self::$map['epay'];
        if (!class_exists($class)) {
            return null;
        }
        return new $class($config);
    }

    /** 返回所有可用网关的 code => name */
    public static function allNames(): array
    {
        return [
            'official_alipay'   => OfficialAlipayGateway::name(),
            'official_wechat'  => OfficialWechatGateway::name(),
            'xunhupay_alipay'  => '虎皮椒-支付宝',
            'xunhupay_wechat'  => '虎皮椒-微信',
            'epay'             => EpayGateway::name(),
        ];
    }

    /** 返回某网关的配置项说明 */
    public static function configFields(string $code): array
    {
        $class = self::$map[$code] ?? null;
        if ($class === null) {
            return [];
        }
        return $class::configFields();
    }
}
