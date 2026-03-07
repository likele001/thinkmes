<?php
declare(strict_types=1);

namespace app\admin\model\equipment;

use app\common\model\BaseModel as Model;

/**
 * 设备档案模型
 */
class EquipmentModel extends Model
{
    protected $name = 'equipment';

    protected $type = [
        'tenant_id'   => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function getStatusList(): array
    {
        return [
            1 => '正常',
            0 => '停用',
            2 => '维修中',
        ];
    }
}
