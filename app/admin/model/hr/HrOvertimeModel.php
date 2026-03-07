<?php
declare(strict_types=1);

namespace app\admin\model\hr;

use app\common\model\BaseModel as Model;

class HrOvertimeModel extends Model
{
    protected $name = 'hr_overtime';

    protected $type = [
        'tenant_id'    => 'integer',
        'employee_id' => 'integer',
        'hours'       => 'float',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(HrEmployeeModel::class, 'employee_id', 'id');
    }

    public static function getStatusList(): array
    {
        return [0 => '待审', 1 => '通过', 2 => '拒绝'];
    }
}
