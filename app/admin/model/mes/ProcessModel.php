<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 工序模型
 */
class ProcessModel extends Model
{
    protected $name = 'mes_process';

    protected $type = [
        'tenant_id'   => 'integer',
        'sort'        => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];
}
