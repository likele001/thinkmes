<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

class TraceCodeModel extends Model
{
    protected $name = 'mes_trace_code';
    
    protected $type = [
        'tenant_id'      => 'integer',
        'report_id'      => 'integer',
        'allocation_id'  => 'integer',
        'order_id'       => 'integer',
        'model_id'       => 'integer',
        'process_id'     => 'integer',
        'user_id'        => 'integer',
        'scan_count'     => 'integer',
        'last_scan_time' => 'integer',
        'status'         => 'integer',
        'create_time'    => 'integer',
        'update_time'    => 'integer',
    ];

    // 关联报工记录
    public function report()
    {
        return $this->belongsTo(ReportModel::class, 'report_id', 'id');
    }

    // 关联分工分配
    public function allocation()
    {
        return $this->belongsTo(AllocationModel::class, 'allocation_id', 'id');
    }

    // 关联订单
    public function order()
    {
        return $this->belongsTo(OrderModel::class, 'order_id', 'id');
    }

    // 生成追溯码
    public static function generateTraceCode(): string
    {
        return 'TRACE' . date('YmdHis') . rand(10000, 99999);
    }
}
