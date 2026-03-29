<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class ItemOptionModel extends Model
{
    protected $name = 'restaurant_item_option';

    protected $type = [
        'id'          => 'integer',
        'tenant_id'   => 'integer',
        'group_id'    => 'integer',
        'price_delta' => 'decimal',
        'sort'        => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function group()
    {
        return $this->belongsTo(ItemOptionGroupModel::class, 'group_id', 'id');
    }
}

