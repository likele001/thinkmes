/<?php
declare(strict_types=1);

namespace app\admin\model\hr;

use app\common\model\BaseModel as Model;

class HrEmployeeModel extends Model
{
    protected $name = 'hr_employee';

    protected $type = [
        'tenant_id'      => 'integer',
        'department_id'  => 'integer',
        'position_id'    => 'integer',
        'status'         => 'integer',
        'create_time'    => 'integer',
        'update_time'    => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(HrDepartmentModel::class, 'department_id', 'id');
    }

    public function position()
    {
        return $this->belongsTo(HrPositionModel::class, 'position_id', 'id');
    }

    public static function getStatusList(): array
    {
        return [1 => '在职', 2 => '离职'];
    }
}
