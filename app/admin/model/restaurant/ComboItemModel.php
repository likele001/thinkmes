<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class ComboItemModel extends Model
{
    protected $name = 'restaurant_combo_item';

    protected $type = [
        'id'          => 'integer',
        'tenant_id'   => 'integer',
        'combo_id'    => 'integer',
        'item_id'     => 'integer',
        'quantity'    => 'decimal',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(ItemModel::class, 'item_id', 'id');
    }
}

