<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

class CustomerOrderModel extends Model
{
    protected $name = 'mes_customer_order';

    protected $type = [
        'tenant_id'        => 'integer',
        'customer_id'      => 'integer',
        'internal_order_id'=> 'integer',
        'status'           => 'integer',
        'total_amount'     => 'float',
        'create_time'      => 'integer',
        'update_time'      => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(CustomerOrderItemModel::class, 'customer_order_id', 'id');
    }

    public static function generateCustomerOrderNo(): string
    {
        $prefix = 'CO';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid('', true)), 0, 6));
        return $prefix . $date . $random;
    }
}

