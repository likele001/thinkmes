<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

/**
 * 售后管理模型
 */
class AfterSalesModel extends Model
{
    protected $name = 'mes_after_sales';

    protected $type = [
        'tenant_id'     => 'integer',
        'order_id'      => 'integer',
        'customer_id'   => 'integer',
        'type'          => 'integer',
        'status'        => 'integer',
        'operator_id'   => 'integer',
        'create_time'   => 'integer',
        'update_time'   => 'integer',
    ];

    /**
     * 获取状态列表
     */
    public function getStatusList(): array
    {
        return [
            0 => '待处理',
            1 => '处理中',
            2 => '已完成',
            3 => '已取消'
        ];
    }

    /**
     * 获取售后类型列表
     */
    public function getTypeList(): array
    {
        return [
            1 => '退货',
            2 => '换货',
            3 => '维修'
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
     * 关联客户
     */
    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id', 'id');
    }

    /**
     * 生成售后单号
     */
    public static function generateAfterSalesNo(): string
    {
        $prefix = 'AFT';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $date . $random;
    }
}
