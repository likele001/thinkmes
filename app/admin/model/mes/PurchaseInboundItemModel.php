<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 采购入库单明细表
 */
class PurchaseInboundItemModel extends Model
{
    protected $name = 'mes_purchase_inbound_item';

    protected $type = [
        'tenant_id'           => 'integer',
        'inbound_id'         => 'integer',
        'purchase_request_id'=> 'integer',
        'material_id'        => 'integer',
        'request_quantity'   => 'float',
        'actual_quantity'    => 'float',
        'unit_price'         => 'float',
        'total_amount'       => 'float',
        'quality_status'      => 'integer',
        'create_time'        => 'integer',
        'update_time'        => 'integer',
    ];

    public function material()
    {
        return $this->belongsTo(MaterialModel::class, 'material_id', 'id');
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequestModel::class, 'purchase_request_id', 'id');
    }
}
