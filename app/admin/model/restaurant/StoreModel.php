<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class StoreModel extends Model
{
    protected $name = 'restaurant_store';

    protected $type = [
        'id'          => 'integer',
        'tenant_id'   => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function areas()
    {
        return $this->hasMany(AreaModel::class, 'store_id', 'id');
    }
}

