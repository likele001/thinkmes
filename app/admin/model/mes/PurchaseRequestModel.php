<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

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
