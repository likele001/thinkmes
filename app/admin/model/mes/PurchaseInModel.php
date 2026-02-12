<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 采购入库单模型
 */
class PurchaseInModel extends Model
{
    protected $name = 'mes_purchase_in';

    protected $type = [
        'tenant_id'          => 'integer',
        'purchase_request_id' => 'integer',
        'supplier_id'        => 'integer',
        'material_id'         => 'integer',
        'in_quantity'        => 'float',
        'actual_price'        => 'float',
        'total_amount'        => 'float',
        'in_time'            => 'integer',
        'warehouse_id'        => 'integer',
        'operator_id'         => 'integer',
        'status'             => 'integer',
        'create_time'         => 'integer',
        'update_time'         => 'integer',
    ];

    /**
     * 获取状态列表
     */
    public function getStatusList(): array
    {
        return [
            0 => '待入库',
            1 => '已入库',
            2 => '已退货'
        ];
    }

    /**
     * 关联采购申请
     */
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequestModel::class, 'purchase_request_id', 'id');
    }

    /**
     * 关联供应商
     */
    public function supplier()
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id', 'id');
    }

    /**
     * 关联物料
     */
    public function material()
    {
        return $this->belongsTo(MaterialModel::class, 'material_id', 'id');
    }

    /**
     * 关联仓库
     */
    public function warehouse()
    {
        return $this->belongsTo(WarehouseModel::class, 'warehouse_id', 'id');
    }

    /**
     * 生成入库单号
     */
    public static function generateInNo(): string
    {
        $prefix = 'IN';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $date . $random;
    }
}
