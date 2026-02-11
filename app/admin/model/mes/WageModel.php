<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

class WageModel extends Model
{
    protected $name = 'mes_wage';
    
    protected $type = [
        'tenant_id'   => 'integer',
        'user_id'     => 'integer',
        'report_id'   => 'integer',
        'allocation_id' => 'integer',
        'quantity'    => 'integer',
        'work_hours'  => 'float',
        'unit_price'  => 'float',
        'total_wage'  => 'float',
        'create_time' => 'integer',
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
}
