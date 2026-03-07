<?php
declare(strict_types=1);

namespace app\admin\model\equipment;

use app\common\model\BaseModel as Model;

/**
 * 设备维修记录模型
 */
class EquipmentRepairModel extends Model
{
    protected $name = 'equipment_repair';

    protected $type = [
        'tenant_id'     => 'integer',
        'equipment_id'  => 'integer',
        'cost'          => 'float',
        'create_time'   => 'integer',
        'update_time'   => 'integer',
    ];

    public function equipment()
    {
        return $this->belongsTo(EquipmentModel::class, 'equipment_id', 'id');
    }
}
