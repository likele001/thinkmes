<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class {$TableName}Model extends Model
{
    protected $name = '{$table}';
    protected $type = [
        'tenant_id'   => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
