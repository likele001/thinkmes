<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class ComboModel extends Model
{
    protected $name = 'restaurant_combo';

    protected $type = [
        'id'          => 'integer',
        'tenant_id'   => 'integer',
        'store_id'    => 'integer',
        'category_id' => 'integer',
        'price'       => 'decimal',
        'sort'        => 'integer',
        'sold_out'    => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(ComboItemModel::class, 'combo_id', 'id');
    }
}

