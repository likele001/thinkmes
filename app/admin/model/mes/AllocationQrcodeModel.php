<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use app\common\model\BaseModel as Model;

class AllocationQrcodeModel extends Model
{
    protected $name = 'mes_allocation_qrcode';
    
    protected $type = [
        'tenant_id'      => 'integer',
        'allocation_id'  => 'integer',
        'scan_count'     => 'integer',
        'last_scan_time' => 'integer',
        'status'         => 'integer',
        'create_time'    => 'integer',
        'update_time'    => 'integer',
    ];

    // 关联分工分配
    public function allocation()
    {
        return $this->belongsTo(AllocationModel::class, 'allocation_id', 'id');
    }
}
