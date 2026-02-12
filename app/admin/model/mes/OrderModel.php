<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 订单模型
 */
class OrderModel extends Model
{
    protected $name = 'mes_order';

    protected $type = [
        'tenant_id'     => 'integer',
        'customer_id'   => 'integer',
        'total_quantity'=> 'integer',
        'status'        => 'integer',
        'delivery_time' => 'integer',
        'create_time'   => 'integer',
        'update_time'   => 'integer',
    ];

    /**
     * 获取状态列表
     */
    public function getStatusList(): array
    {
        return [
            0 => '待生产',
            1 => '生产中',
            2 => '已完成',
            3 => '已取消'
        ];
    }

    /**
     * 关联订单型号
     */
    public function orderModels()
    {
        return $this->hasMany(OrderModelModel::class, 'order_id', 'id');
    }

    /**
     * 关联客户
     */
    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id', 'id');
    }

    /**
     * 生成订单号
     */
    public static function generateOrderNo(): string
    {
        $prefix = 'ORD';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $date . $random;
    }
}
