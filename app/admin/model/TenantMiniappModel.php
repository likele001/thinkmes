<?php
declare(strict_types=1);

namespace app\admin\model;

use app\common\model\BaseModel as Model;

class TenantMiniappModel extends Model
{
    protected $name = 'tenant_miniapp';

    protected $type = [
        'tenant_id'   => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}

