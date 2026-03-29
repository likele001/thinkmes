<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class CartModel extends Model
{
    protected $name = 'restaurant_cart';

    protected $type = [
        'id'          => 'integer',
        'tenant_id'   => 'integer',
        'store_id'    => 'integer',
        'table_id'    => 'integer',
        'item_id'     => 'integer',
        'combo_id'    => 'integer',
        'option_key'  => 'string',
        'quantity'    => 'decimal',
        'unit_price'  => 'decimal',
        'line_amount' => 'decimal',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(ItemModel::class, 'item_id', 'id');
    }
}
