<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 物料模型
 */
class MaterialModel extends Model
{
    protected $name = 'mes_material';

    protected $type = [
        'tenant_id'           => 'integer',
        'category_id'         => 'integer',
        'default_supplier_id' => 'integer',
        'current_price'       => 'decimal',
        'stock'               => 'decimal',
        'min_stock'           => 'decimal',
        'max_stock'           => 'decimal',
        'safety_stock'        => 'decimal',
        'lead_time'           => 'integer',
        'create_time'         => 'integer',
        'update_time'         => 'integer',
    ];

    /**
     * 关联物料分类
     */
    public function category()
    {
        return $this->belongsTo(MaterialCategoryModel::class, 'category_id', 'id');
    }

    /**
     * 关联默认供应商
     */
    public function defaultSupplier()
    {
        return $this->belongsTo(SupplierModel::class, 'default_supplier_id', 'id');
    }

    /**
     * 单位列表（与 report 一致）
     */
    public function getUnitList(): array
    {
        return [
            'PCS' => '件',
            '米' => '米',
            '个' => '个',
            '公斤' => '公斤',
            '平方米' => '平方米',
            '卷' => '卷',
            '套' => '套',
            '条' => '条',
        ];
    }
}
