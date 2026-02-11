<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

/**
 * 报工模型
 */
class ReportModel extends Model
{
    protected $name = 'mes_report';

    protected $type = [
        'tenant_id'     => 'integer',
        'allocation_id' => 'integer',
        'user_id'       => 'integer',
        'quantity'      => 'integer',
        'work_hours'    => 'decimal',
        'wage'          => 'decimal',
        'status'         => 'integer',
        'audit_user_id'  => 'integer',
        'audit_time'     => 'integer',
        'quality_status' => 'integer',
        'create_time'    => 'integer',
        'update_time'    => 'integer',
    ];

    /**
     * 获取状态列表
     */
    public function getStatusList(): array
    {
        return [
            0 => '待审核',
            1 => '已通过',
            2 => '已拒绝'
        ];
    }

    /**
     * 关联分配
     */
    public function allocation()
    {
        return $this->belongsTo(AllocationModel::class, 'allocation_id', 'id');
    }

    /**
     * 关联用户（员工）
     */
    public function user()
    {
        return $this->belongsTo(\app\common\model\UserModel::class, 'user_id', 'id');
    }
}
