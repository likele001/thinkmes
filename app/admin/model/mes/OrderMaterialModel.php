<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

/**
 * 订单物料需求模型
 */
class OrderMaterialModel extends Model
{
    protected $name = 'mes_order_material';

    protected $type = [
        'tenant_id'        => 'integer',
        'order_id'         => 'integer',
        'material_id'      => 'integer',
        'required_quantity'=> 'decimal',
        'estimated_price'  => 'decimal',
        'estimated_amount' => 'decimal',
        'supplier_id'      => 'integer',
        'loss_rate'        => 'decimal',
        'purchase_status'  => 'integer',
        'stock_status'     => 'integer',
        'create_time'      => 'integer',
    ];

    /**
     * 关联订单
     */
    public function order()
    {
        return $this->belongsTo(OrderModel::class, 'order_id', 'id');
    }

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
