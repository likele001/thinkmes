<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class ItemModel extends Model
{
    protected $name = 'restaurant_item';

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

    public function category()
    {
        return $this->belongsTo(CategoryModel::class, 'category_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(StoreModel::class, 'store_id', 'id');
    }
}

