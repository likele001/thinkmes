<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 订单型号模型
 */
class OrderModelModel extends Model
{
    protected $name = 'mes_order_model';

    protected $type = [
        'tenant_id'   => 'integer',
        'order_id'    => 'integer',
        'model_id'    => 'integer',
        'quantity'    => 'integer',
        'create_time' => 'integer',
    ];

    /**
     * 关联订单
     */
    public function order()
    {
        return $this->belongsTo(OrderModel::class, 'order_id', 'id');
    }

    /**
     * 关联型号
     */
    public function model()
    {
        return $this->belongsTo(ProductModelModel::class, 'model_id', 'id');
    }
}
