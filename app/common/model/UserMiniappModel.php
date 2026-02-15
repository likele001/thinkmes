<?php
declare(strict_types=1);

namespace app\common\model;

use app\common\model\BaseModel as Model;

class UserMiniappModel extends Model
{
    protected $name = 'user_miniapp';

    protected $type = [
        'tenant_id'        => 'integer',
        'user_id'          => 'integer',
        'last_login_time'  => 'integer',
        'create_time'      => 'integer',
        'update_time'      => 'integer',
    ];
}

