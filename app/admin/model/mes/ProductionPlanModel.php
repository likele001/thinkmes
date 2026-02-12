<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

class ProductionPlanModel extends Model
{
    protected $name = 'mes_production_plan';
    
    protected $type = [
        'tenant_id'         => 'integer',
        'order_id'          => 'integer',
        'model_id'          => 'integer',
        'total_quantity'    => 'integer',
        'completed_quantity' => 'integer',
        'status'            => 'integer',
        'progress'          => 'float',
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

    // 关联分工分配
    public function allocations()
    {
        return $this->hasMany(AllocationModel::class, 'plan_id', 'id');
    }

    // 生成计划编码
    public static function generatePlanCode(): string
    {
        return 'PLAN' . date('YmdHis') . rand(1000, 9999);
    }

    // 获取状态列表
    public static function getStatusList(): array
    {
        return [0 => '待开始', 1 => '进行中', 2 => '已完成', 3 => '已暂停'];
    }
}
