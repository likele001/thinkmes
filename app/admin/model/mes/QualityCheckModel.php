<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

/**
 * 质检记录模型
 */
class QualityCheckModel extends Model
{
    protected $name = 'mes_quality_check';

    protected $type = [
        'tenant_id'         => 'integer',
        'report_id'         => 'integer',
        'allocation_id'      => 'integer',
        'standard_id'        => 'integer',
        'check_quantity'      => 'integer',
        'qualified_quantity'  => 'integer',
        'unqualified_quantity'=> 'integer',
        'qualified_rate'     => 'float',
        'check_user_id'      => 'integer',
        'check_time'          => 'integer',
        'status'             => 'integer',
        'create_time'        => 'integer',
        'update_time'        => 'integer',
    ];

    /**
     * 获取状态列表
     */
    public function getStatusList(): array
    {
        return [
            0 => '待质检',
            1 => '已质检',
            2 => '返工'
        ];
    }

    /**
     * 关联报工
     */
    public function report()
    {
        return $this->belongsTo(ReportModel::class, 'report_id', 'id');
    }

    /**
     * 关联分配
     */
    public function allocation()
    {
        return $this->belongsTo(AllocationModel::class, 'allocation_id', 'id');
    }

    /**
     * 关联质检标准
     */
    public function standard()
    {
        return $this->belongsTo(QualityStandardModel::class, 'standard_id', 'id');
    }

    /**
     * 生成质检单号
     */
    public static function generateCheckNo(): string
    {
        $prefix = 'QC';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $date . $random;
    }
}
