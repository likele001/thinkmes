<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class CategoryModel extends Model
{
    protected $name = 'restaurant_category';

    protected $type = [
        'id'          => 'integer',
        'tenant_id'   => 'integer',
        'store_id'    => 'integer',
        'sort'        => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(StoreModel::class, 'store_id', 'id');
    }
}

