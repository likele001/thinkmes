<?php
declare(strict_types=1);

namespace app\admin\model\hr;

use app\common\model\BaseModel as Model;

class HrPositionModel extends Model
{
    protected $name = 'hr_position';

    protected $type = [
        'tenant_id'   => 'integer',
        'sort'        => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
