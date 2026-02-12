<?php
declare(strict_types=1);

namespace app\common\model;

use think\facade\Request;
use think\facade\Session;

/**
 * TenantScope 辅助 Trait（非侵入式）
 *
 * 提供了一个便捷方法 `applyTenantScope($query, $field = 'tenant_id')`
 * 在需要强制租户过滤的地方调用即可：
 *   $query = $this->applyTenantScope($query);
 *
 * 说明：此 trait 不会自动修改所有查询，避免引入不可预期的副作用。
 * 推荐迁移策略：逐个模型或关键查询引入此 trait 的 `applyTenantScope`。
 */
trait TenantScope
{
    /**
     * 将 tenant_id 过滤应用到查询构造器（若 tenant_id>0）
     * @param \think\contract\Queryable|\think\Model $query
     * @param string $field
     * @return mixed
     */
    protected function applyTenantScope($query, string $field = 'tenant_id')
    {
        $tenantId = 0;
        // 优先使用会话
        try {
            $admin = Session::get('admin_info');
            if (!empty($admin) && isset($admin['tenant_id']) && (int)$admin['tenant_id'] > 0) {
                $tenantId = (int)$admin['tenant_id'];
            }
        } catch (\Throwable $e) {
            // ignore
        }
        // 尝试从 Request header
        if ($tenantId === 0) {
            try {
                $header = Request::header('x-tenant-id');
                if ($header !== null && $header !== '') {
                    $tenantId = max(0, (int)$header);
                }
            } catch (\Throwable $e) {
            }
        }
        if ($tenantId > 0) {
            // 支持传入模型或查询构造器
            if (is_object($query)) {
                return $query->where($field, $tenantId);
            }
        }
        return $query;
    }
}
