<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class OrderItemModel extends Model
{
    protected $name = 'restaurant_order_item';

    protected $type = [
        'id'          => 'integer',
        'tenant_id'   => 'integer',
        'order_id'    => 'integer',
        'item_id'     => 'integer',
        'combo_id'    => 'integer',
        'option_key'  => 'string',
        'unit_price'  => 'decimal',
        'line_amount' => 'decimal',
        'price'       => 'decimal',
        'quantity'    => 'decimal',
        'amount'      => 'decimal',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(ItemModel::class, 'item_id', 'id');
    }
}
