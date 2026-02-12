<?php
declare(strict_types=1);

namespace app\admin\model;

use app\common\model\BaseModel as Model;

class TenantModel extends Model
{
    protected $name = 'tenant';

    protected $type = [
        'package_id'  => 'integer',
        'expire_time' => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
