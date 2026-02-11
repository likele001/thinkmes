<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

/**
 * 仓库模型
 */
class WarehouseModel extends Model
{
    protected $name = 'mes_warehouse';

    protected $type = [
        'tenant_id'   => 'integer',
        'manager_id'  => 'integer',
        'status'      => 'integer',
        'is_default'  => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    /**
     * 获取状态列表
     */
    public function getStatusList(): array
    {
        return [
            1 => '启用',
            0 => '禁用'
        ];
    }

    /**
     * 获取默认仓库
     */
    public static function getDefaultWarehouse(int $tenantId)
    {
        return self::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->where('is_default', 1)
            ->find();
    }
}
