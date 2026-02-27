<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

class CustomerProductModel extends Model
{
    protected $name = 'mes_customer_product';

    protected $type = [
        'tenant_id'   => 'integer',
        'customer_id' => 'integer',
        'product_id'  => 'integer',
        'model_id'    => 'integer',
        'price'       => 'float',
        'min_qty'     => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id', 'id');
    }

    public function model()
    {
        return $this->belongsTo(ProductModelModel::class, 'model_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'id');
    }
}

