<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

class ScheduleTaskModel extends Model
{
    protected $name = 'mes_schedule_task';

    protected $type = [
        'tenant_id' => 'integer',
        'plan_id' => 'integer',
        'order_id' => 'integer',
        'model_id' => 'integer',
        'process_id' => 'integer',
        'user_id' => 'integer',
        'quantity' => 'integer',
        'status' => 'integer',
        'create_time' => 'integer',
    ];

    public function plan()
    {
        return $this->belongsTo(ProductionPlanModel::class, 'plan_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(OrderModel::class, 'order_id', 'id');
    }

    public function model()
    {
        return $this->belongsTo(ProductModelModel::class, 'model_id', 'id');
    }

    public function process()
    {
        return $this->belongsTo(ProcessModel::class, 'process_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\app\common\model\UserModel::class, 'user_id', 'id');
    }
}

