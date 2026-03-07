<?php
declare(strict_types=1);

namespace app\admin\model\hr;

use app\common\model\BaseModel as Model;

class HrLeaveModel extends Model
{
    protected $name = 'hr_leave';

    protected $type = [
        'tenant_id'    => 'integer',
        'employee_id' => 'integer',
        'type'        => 'integer',
        'days'        => 'float',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(HrEmployeeModel::class, 'employee_id', 'id');
    }

    public static function getTypeList(): array
    {
        return [1 => '事假', 2 => '病假', 3 => '年假'];
    }

    public static function getStatusList(): array
    {
        return [0 => '待审', 1 => '通过', 2 => '拒绝'];
    }
}
