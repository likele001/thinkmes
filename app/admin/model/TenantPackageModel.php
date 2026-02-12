<?php
declare(strict_types=1);

namespace app\admin\model;

use app\common\model\BaseModel as Model;

class TenantPackageModel extends Model
{
    protected $name = 'tenant_package';

    protected $type = [
        'max_admin'   => 'integer',
        'max_user'    => 'integer',
        'expire_days' => 'integer',
        'sort'        => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
