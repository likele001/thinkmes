<?php
declare(strict_types=1);

namespace app\admin\model\restaurant;

use app\common\model\BaseModel as Model;

class TableModel extends Model
{
    protected $name = 'restaurant_table';

    protected $type = [
        'id'          => 'integer',
        'tenant_id'   => 'integer',
        'store_id'    => 'integer',
        'area_id'     => 'integer',
        'seats'       => 'integer',
        'status'      => 'integer',
        'state'       => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(StoreModel::class, 'store_id', 'id');
    }

    public function area()
    {
        return $this->belongsTo(AreaModel::class, 'area_id', 'id');
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }
}

