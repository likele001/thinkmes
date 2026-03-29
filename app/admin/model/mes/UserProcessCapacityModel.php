<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

class UserProcessCapacityModel extends Model
{
    protected $name = 'mes_user_process_capacity';

    protected $type = [
        'tenant_id' => 'integer',
        'user_id' => 'integer',
        'process_id' => 'integer',
        'capacity_per_day' => 'integer',
        'status' => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\app\common\model\UserModel::class, 'user_id', 'id');
    }

    public function process()
    {
        return $this->belongsTo(ProcessModel::class, 'process_id', 'id');
    }
}

