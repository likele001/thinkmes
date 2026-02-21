<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

class TraceCodeModel extends Model
{
    protected $name = 'mes_trace_code';
    
    protected $type = [
        'tenant_id'      => 'integer',
        'code_type'      => 'integer',
        'report_id'      => 'integer',
        'allocation_id'  => 'integer',
        'order_id'       => 'integer',
        'model_id'       => 'integer',
        'route_id'       => 'integer',
        'process_id'     => 'integer',
        'user_id'        => 'integer',
        'scan_count'     => 'integer',
        'last_scan_time' => 'integer',
        'status'         => 'integer',
        'create_time'    => 'integer',
        'update_time'    => 'integer',
    ];

    public function report()
    {
        return $this->belongsTo(ReportModel::class, 'report_id', 'id');
    }

    public function allocation()
    {
        return $this->belongsTo(AllocationModel::class, 'allocation_id', 'id');
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

    public static function generateTraceCode(): string
    {
        return 'TRACE' . date('YmdHis') . rand(10000, 99999);
    }
}
