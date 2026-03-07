<?php
declare(strict_types=1);

namespace app\admin\model\hr;

use app\common\model\BaseModel as Model;

class HrDepartmentModel extends Model
{
    protected $name = 'hr_department';

    protected $type = [
        'tenant_id'   => 'integer',
        'pid'         => 'integer',
        'sort'        => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'pid', 'id');
    }
}
