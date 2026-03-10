<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;
use app\admin\model\mes\MaterialModel;
use app\admin\model\mes\SupplierModel;
use app\admin\model\mes\OrderModel;

/**
 * 采购申请模型
 */
class PurchaseRequestModel extends Model
{
    protected $name = 'mes_purchase_request';

    protected $type = [
        'tenant_id'        => 'integer',
        'material_id'      => 'integer',
        'supplier_id'      => 'integer',
        'required_quantity'=> 'decimal',
        'estimated_price'  => 'decimal',
        'estimated_amount' => 'decimal',
        'order_id'         => 'integer',
        'order_material_id'=> 'integer',
        'status'           => 'integer',
        'create_time'      => 'integer',
        'update_time'      => 'integer',
    ];

    /** 关联物料 */
    public function material()
    {
        return $this->belongsTo(MaterialModel::class, 'material_id', 'id');
    }

    /** 关联供应商 */
    public function supplier()
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id', 'id');
    }

    /** 关联订单 */
    public function order()
    {
        return $this->belongsTo(OrderModel::class, 'order_id', 'id');
    }

    /**
     * 生成申请单号
     */
    public static function generateRequestNo(): string
    {
        $prefix = 'PR';
        $date = date('YmdHis');
        $random = rand(1000, 9999);
        return $prefix . $date . $random;
    }
}
