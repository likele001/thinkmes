<?php
declare(strict_types=1);

namespace app\admin\model;

use app\common\model\BaseModel as Model;

class ConfigModel extends Model
{
    protected $name = 'config';
    protected $type = ['create_time' => 'integer', 'update_time' => 'integer'];
}
