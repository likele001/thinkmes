<?php
// 应用公共文件

use think\facade\Lang;

if (!function_exists('__')) {
    /**
     * 多语言翻译函数
     * @param string $name 语言变量名
     * @param array  $vars 动态变量值
     * @param string $lang 语言
     * @return string
     */
    function __(string $name, array $vars = [], string $lang = ''): string
    {
        return Lang::get($name, $vars, $lang);
    }
}

use think\facade\Db;
use think\facade\Session;

if (!function_exists('ai_global_switch')) {
    /**
     * 读取全局 AI 开关配置
     */
    function ai_global_switch(): array
    {
        $row = Db::name('ai_global_switch')->where('id', 1)->find();
        return $row ?: [];
    }
}

if (!function_exists('ai_is_enabled_global')) {
    /**
     * 全局是否允许 AI 功能
     */
    function ai_is_enabled_global(): bool
    {
        $cfg = ai_global_switch();
        return isset($cfg['enabled']) && intval($cfg['enabled']) === 1;
    }
}

if (!function_exists('tenant_has_ai_package')) {
    /**
     * 检查指定租户是否已购买生效的 AI 套餐
     */
    function tenant_has_ai_package(int $tenantId): bool
    {
        $now = time();
        $row = Db::name('tenant_ai_purchase')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->where(function ($query) use ($now) {
                $query->where('end_time', '>', $now)->whereOr('end_time', 0);
            })->order('end_time', 'desc')->find();
        return (bool)$row;
    }
}

if (!function_exists('tenant_ai_available')) {
    /**
     * 综合判断租户是否可以使用 AI：全局允许 && (不要求购买 || 已购买)
     */
    function tenant_ai_available(?int $tenantId = null): bool
    {
        $cfg = ai_global_switch();
        $enabled = isset($cfg['enabled']) && intval($cfg['enabled']) === 1;
        $require = isset($cfg['require_purchase']) && intval($cfg['require_purchase']) === 1;
        if (!$enabled) {
            return false;
        }
        if (!$require) {
            return true;
        }
        if ($tenantId === null) {
            $tenantId = Session::get('admin.tenant_id') ?: 0;
        }
        if (!$tenantId) {
            return false;
        }
        return tenant_has_ai_package((int)$tenantId);
    }
}

if (!function_exists('tenant_ai_module_enabled')) {
    /**
     * 判断某租户的某个 AI 子功能是否开启
     * 先查租户覆盖表 fa_tenant_ai_module_switch，无则用平台 fa_ai_global_switch 对应列
     * @param int|null $tenantId 租户ID，null 时从 Session 取
     * @param string $module 子功能：voice_report / anomaly / qa / crm_follow
     */
    function tenant_ai_module_enabled(?int $tenantId, string $module): bool
    {
        $map = [
            'voice_report' => 'switch_voice_report',
            'anomaly'      => 'switch_anomaly',
            'qa'           => 'switch_qa',
            'crm_follow'   => 'switch_crm_follow',
        ];
        $col = $map[$module] ?? null;
        if ($col === null) {
            return false;
        }
        if ($tenantId === null) {
            $tenantId = (int) (Session::get('admin.tenant_id') ?? 0);
        }
        try {
            if ($tenantId > 0) {
                $row = Db::name('tenant_ai_module_switch')->where('tenant_id', $tenantId)->where('module', $module)->find();
                if ($row !== null) {
                    return (int)($row['enabled'] ?? 0) === 1;
                }
            }
            $cfg = ai_global_switch();
            return isset($cfg[$col]) && (int)$cfg[$col] === 1;
        } catch (\Throwable $e) {
            return true;
        }
    }
}
