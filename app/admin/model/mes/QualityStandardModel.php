<?php
declare(strict_types=1);

namespace app\admin\model\mes;

use think\Model;

/**
 * 质检标准模型
 */
class QualityStandardModel extends Model
{
    protected $name = 'mes_quality_standard';

    protected $type = [
        'tenant_id'      => 'integer',
        'process_id'      => 'integer',
        'model_id'        => 'integer',
        'qualified_rate'   => 'float',
        'status'          => 'integer',
        'create_time'     => 'integer',
        'update_time'     => 'integer',
    ];

    /**
     * 获取状态列表
     */
    public function getStatusList(): array
    {
        return [
            1 => '启用',
            0 => '禁用'
        ];
    }

    /**
     * 关联工序
     */
    public function process()
    {
        return $this->belongsTo(ProcessModel::class, 'process_id', 'id');
    }

    /**
     * 关联型号
     */
    public function model()
    {
        return $this->belongsTo(ProductModelModel::class, 'model_id', 'id');
    }

    /**
     * 关联质检记录
     */
    public function checks()
    {
        return $this->hasMany(QualityCheckModel::class, 'standard_id', 'id');
    }
}
