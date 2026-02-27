<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

class CustomerOrderItemModel extends Model
{
    protected $name = 'mes_customer_order_item';

    protected $type = [
        'tenant_id'          => 'integer',
        'customer_order_id'  => 'integer',
        'customer_product_id'=> 'integer',
        'product_id'         => 'integer',
        'model_id'           => 'integer',
        'quantity'           => 'integer',
        'price'              => 'float',
        'amount'             => 'float',
        'create_time'        => 'integer',
        'update_time'        => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(CustomerOrderModel::class, 'customer_order_id', 'id');
    }

    public function customerProduct()
    {
        return $this->belongsTo(CustomerProductModel::class, 'customer_product_id', 'id');
    }
}

