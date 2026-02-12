<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 发货明细模型
 */
class ShipmentItemModel extends Model
{
    protected $name = 'mes_shipment_item';

    protected $type = [
        'tenant_id'    => 'integer',
        'shipment_id'   => 'integer',
        'model_id'      => 'integer',
        'quantity'      => 'integer',
        'create_time'   => 'integer',
    ];

    /**
     * 关联发货单
     */
    public function shipment()
    {
        return $this->belongsTo(ShipmentModel::class, 'shipment_id', 'id');
    }

    /**
     * 关联型号
     */
    public function model()
    {
        return $this->belongsTo(ProductModelModel::class, 'model_id', 'id');
    }

    /**
     * 关联追溯码
     */
    public function traceCode()
    {
        return $this->belongsTo(TraceCodeModel::class, 'trace_code', 'trace_code');
    }
}
