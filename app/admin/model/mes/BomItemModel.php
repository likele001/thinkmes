<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

/**
 * BOM明细模型
 */
class BomItemModel extends Model
{
    protected $name = 'mes_bom_item';

    protected $type = [
        'tenant_id'   => 'integer',
        'bom_id'      => 'integer',
        'parent_id'   => 'integer',
        'material_id' => 'integer',
        'quantity'    => 'decimal',
        'loss_rate'   => 'decimal',
        'unit_price'  => 'decimal',
        'supplier_id' => 'integer',
        'level'        => 'integer',
        'sequence'    => 'integer',
        'create_time'  => 'integer',
    ];

    /**
     * 关联物料
     */
    public function material()
    {
        return $this->belongsTo(MaterialModel::class, 'material_id', 'id');
    }

    /**
     * 关联供应商
     */
    public function supplier()
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id', 'id');
    }
}
