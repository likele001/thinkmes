<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 发货单模型
 */
class ShipmentModel extends Model
{
    protected $name = 'mes_shipment';

    protected $type = [
        'tenant_id'          => 'integer',
        'order_id'           => 'integer',
        'customer_id'         => 'integer',
        'shipment_quantity'   => 'integer',
        'shipment_time'        => 'integer',
        'sign_time'           => 'integer',
        'status'              => 'integer',
        'operator_id'          => 'integer',
        'create_time'          => 'integer',
        'update_time'          => 'integer',
    ];

    /**
     * 获取状态列表
     */
    public function getStatusList(): array
    {
        return [
            0 => '待发货',
            1 => '已发货',
            2 => '已签收',
            3 => '已退回'
        ];
    }

    /**
     * 关联订单
     */
    public function order()
    {
        return $this->belongsTo(OrderModel::class, 'order_id', 'id');
    }

    /**
     * 关联客户
     */
    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id', 'id');
    }

    /**
     * 关联发货明细
     */
    public function items()
    {
        return $this->hasMany(ShipmentItemModel::class, 'shipment_id', 'id');
    }

    /**
     * 生成发货单号
     */
    public static function generateShipmentNo(): string
    {
        $prefix = 'SHIP';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $date . $random;
    }
}
