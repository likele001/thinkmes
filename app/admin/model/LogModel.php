<?php
declare(strict_types=1);

namespace app\admin\model;

use app\common\model\BaseModel as Model;

class LogModel extends Model
{
    protected $name = 'log';

    protected $type = [
        'tenant_id'   => 'integer',
        'create_time' => 'integer',
    ];
}
