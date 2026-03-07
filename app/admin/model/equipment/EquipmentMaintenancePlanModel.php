<?php
declare(strict_types=1);

namespace app\admin\model\equipment;

use app\common\model\BaseModel as Model;

/**
 * 设备保养计划模型
 */
class EquipmentMaintenancePlanModel extends Model
{
    protected $name = 'equipment_maintenance_plan';

    protected $type = [
        'tenant_id'     => 'integer',
        'equipment_id'  => 'integer',
        'cycle_days'    => 'integer',
        'create_time'   => 'integer',
        'update_time'   => 'integer',
    ];

    public function equipment()
    {
        return $this->belongsTo(EquipmentModel::class, 'equipment_id', 'id');
    }

    public static function getPlanTypeList(): array
    {
        return [
            'daily'   => '日保养',
            'weekly'  => '周保养',
            'monthly' => '月保养',
            'quarterly' => '季保养',
            'yearly'  => '年保养',
        ];
    }
}
