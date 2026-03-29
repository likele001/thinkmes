<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class OrderModel extends Model
{
    protected $name = 'restaurant_order';

    protected $type = [
        'id'          => 'integer',
        'tenant_id'   => 'integer',
        'store_id'    => 'integer',
        'table_id'    => 'integer',
        'status'      => 'integer',
        'total_amount'=> 'decimal',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(OrderItemModel::class, 'order_id', 'id');
    }

    public static function generateOrderNo(): string
    {
        $prefix = 'RO';
        $date = date('YmdHis');
        $rand = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        return $prefix . $date . $rand;
    }
}

