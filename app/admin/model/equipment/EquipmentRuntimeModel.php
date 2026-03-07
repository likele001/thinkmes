<?php
declare(strict_types=1);

namespace app\admin\model\equipment;

use app\common\model\BaseModel as Model;

/**
 * 设备运行记录模型
 */
class EquipmentRuntimeModel extends Model
{
    protected $name = 'equipment_runtime';

    protected $type = [
        'tenant_id'     => 'integer',
        'equipment_id'  => 'integer',
        'plan_hours'    => 'float',
        'run_hours'     => 'float',
        'down_hours'    => 'float',
        'create_time'   => 'integer',
    ];

    public function equipment()
    {
        return $this->belongsTo(EquipmentModel::class, 'equipment_id', 'id');
    }
}
