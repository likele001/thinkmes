<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class ItemOptionGroupModel extends Model
{
    protected $name = 'restaurant_item_option_group';

    protected $type = [
        'id'          => 'integer',
        'tenant_id'   => 'integer',
        'item_id'     => 'integer',
        'required'    => 'integer',
        'min_select'  => 'integer',
        'max_select'  => 'integer',
        'sort'        => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(ItemModel::class, 'item_id', 'id');
    }

    public function options()
    {
        return $this->hasMany(ItemOptionModel::class, 'group_id', 'id');
    }
}

