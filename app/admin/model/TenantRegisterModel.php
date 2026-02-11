<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

/**
 * 租户注册申请模型
 */
class TenantRegisterModel extends Model
{
    protected $name = 'tenant_register';

    protected $type = [
        'package_id'   => 'integer',
        'status'       => 'integer',
        'audit_user_id' => 'integer',
        'audit_time'   => 'integer',
        'create_time'   => 'integer',
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
     * 关联套餐
     */
    public function package()
    {
        return $this->belongsTo(TenantPackageModel::class, 'package_id', 'id');
    }
}
