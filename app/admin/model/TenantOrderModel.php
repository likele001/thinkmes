<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class TenantOrderModel extends Model
{
    protected $name = 'tenant_order';

    protected $type = [
        'tenant_id' => 'integer',
        'package_id' => 'integer',
        'type' => 'integer',
        'amount' => 'float',
        'status' => 'integer',
        'pay_time' => 'integer',
        'expire_days' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    /**
     * 生成订单号
     */
    public static function generateOrderNo(): string
    {
        return 'T' . date('YmdHis') . mt_rand(1000, 9999);
    }
}
