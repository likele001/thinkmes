<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 物料分类模型（与 report scanwork_material_category 对应）
 */
class MaterialCategoryModel extends Model
{
    protected $name = 'mes_material_category';

    protected $type = [
        'tenant_id'   => 'integer',
        'sort'        => 'integer',
        'status'      => 'integer',
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    /**
     * 状态列表
     */
    public function getStatusList(): array
    {
        return [1 => '启用', 0 => '禁用'];
    }

    /**
     * 关联物料
     */
    public function materials()
    {
        return $this->hasMany(MaterialModel::class, 'category_id', 'id');
    }
}
