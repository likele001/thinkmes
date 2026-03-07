<?php
declare(strict_types=1);

namespace app\admin\model\hr;

use app\common\model\BaseModel as Model;

class HrAttendanceModel extends Model
{
    protected $name = 'hr_attendance';

    protected $type = [
        'tenant_id'    => 'integer',
        'employee_id' => 'integer',
        'clock_in'    => 'integer',
        'clock_out'   => 'integer',
        'create_time' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(HrEmployeeModel::class, 'employee_id', 'id');
    }
}
