<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

/**
 * 产品模型
 */
class ProductModel extends Model
{
    protected $name = 'mes_product';

    protected $type = [
        'tenant_id'   => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    /**
     * 获取状态列表
     */
    public function getStatusList(): array
    {
        return [
            1 => '正常',
            0 => '禁用'
        ];
    }

    /**
     * 关联型号
     */
    public function models()
    {
        return $this->hasMany(ProductModelModel::class, 'product_id', 'id');
    }
}
