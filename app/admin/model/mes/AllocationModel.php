<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

class AllocationModel extends Model
{
    protected $name = 'mes_allocation';
    
    protected $type = [
        'tenant_id'         => 'integer',
        'order_id'          => 'integer',
        'model_id'          => 'integer',
        'process_id'        => 'integer',
        'user_id'           => 'integer',
        'quantity'          => 'integer',
        'completed_quantity' => 'integer',
        'status'            => 'integer',
        'planned_start_time' => 'integer',
        'planned_end_time'   => 'integer',
        'actual_start_time'  => 'integer',
        'actual_end_time'    => 'integer',
        'create_time'       => 'integer',
        'update_time'       => 'integer',
    ];

    // 关联订单
    public function order()
    {
        return $this->belongsTo(OrderModel::class, 'order_id', 'id');
    }

    // 关联产品型号
    public function model()
    {
        return $this->belongsTo(ProductModelModel::class, 'model_id', 'id');
    }

    // 关联工序
    public function process()
    {
        return $this->belongsTo(ProcessModel::class, 'process_id', 'id');
    }

    // 关联报工记录
    public function reports()
    {
        return $this->hasMany(ReportModel::class, 'allocation_id', 'id');
    }

    // 关联用户（员工）
    public function user()
    {
        return $this->belongsTo(\app\common\model\UserModel::class, 'user_id', 'id');
    }

    // 生成分配编码
    public static function generateAllocationCode(): string
    {
        return 'ALLOC' . date('YmdHis') . rand(1000, 9999);
    }

    // 获取状态列表
    public static function getStatusList(): array
    {
        return [0 => '待开始', 1 => '进行中', 2 => '已完成'];
    }
}
