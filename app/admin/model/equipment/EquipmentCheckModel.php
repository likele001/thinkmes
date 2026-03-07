<?php
declare(strict_types=1);

namespace app\admin\model\equipment;

use app\common\model\BaseModel as Model;

/**
 * 设备点检记录模型
 */
class EquipmentCheckModel extends Model
{
    protected $name = 'equipment_check';

    protected $type = [
        'tenant_id'     => 'integer',
        'equipment_id'  => 'integer',
        'result'        => 'integer',
        'create_time'   => 'integer',
    ];

    public function equipment()
    {
        return $this->belongsTo(EquipmentModel::class, 'equipment_id', 'id');
    }

    public static function getResultList(): array
    {
        return [
            1 => '合格',
            0 => '不合格',
        ];
    }
}
