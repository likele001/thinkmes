<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

/**
 * 生产领料单模型
 */
class StockOutModel extends Model
{
    protected $name = 'mes_stock_out';

    protected $type = [
        'tenant_id'     => 'integer',
        'order_id'      => 'integer',
        'material_id'    => 'integer',
        'out_quantity'   => 'float',
        'out_time'       => 'integer',
        'warehouse_id'   => 'integer',
        'operator_id'    => 'integer',
        'receiver_id'    => 'integer',
        'status'         => 'integer',
        'create_time'    => 'integer',
        'update_time'    => 'integer',
    ];

    /**
     * 获取状态列表
     */
    public function getStatusList(): array
    {
        return [
            0 => '待出库',
            1 => '已出库',
            2 => '已退回'
        ];
    }

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
     * 关联仓库
     */
    public function warehouse()
    {
        return $this->belongsTo(WarehouseModel::class, 'warehouse_id', 'id');
    }

    /**
     * 生成出库单号
     */
    public static function generateOutNo(): string
    {
        $prefix = 'OUT';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $date . $random;
    }
}
