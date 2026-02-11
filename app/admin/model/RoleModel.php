<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class RoleModel extends Model
{
    protected $name = 'role';

    protected $type = [
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
