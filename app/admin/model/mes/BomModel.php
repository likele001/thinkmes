<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * BOM模型
 */
class BomModel extends Model
{
    protected $name = 'mes_bom';

    protected $type = [
        'tenant_id'     => 'integer',
        'product_id'    => 'integer',
        'model_id'      => 'integer',
        'base_quantity' => 'integer',
        'status'        => 'integer',
        'creator_id'    => 'integer',
        'approver_id'   => 'integer',
        'approve_time'  => 'integer',
        'publish_time'  => 'integer',
        'create_time'   => 'integer',
        'update_time'   => 'integer',
    ];

    /**
     * 获取状态列表
     */
    public function getStatusList(): array
    {
        return [
            0 => '草稿',
            1 => '审核中',
            2 => '已发布',
            3 => '已废弃'
        ];
    }

    /**
     * 生成BOM编号
     */
    public static function generateBomNo(): string
    {
        $prefix = 'BOM';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $date . $random;
    }

    /**
     * 关联BOM明细
     */
    public function items()
    {
        return $this->hasMany(BomItemModel::class, 'bom_id', 'id');
    }
}
