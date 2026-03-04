<?php
declare(strict_types=1);

namespace app\admin\controller\ai;

use app\admin\controller\Backend;
use app\common\lib\AiService;
use think\Response;

/**
 * 工厂 AI 模块基类
 * 严格 tenant_id 隔离，异常捕获完善
 */
abstract class Base extends Backend
{
    protected function getAiService(): AiService
    {
        $admin = \think\facade\Session::get('admin_info');
        $adminId = (int) ($admin['id'] ?? 0);
        return new AiService($this->getTenantId(), $adminId);
    }

    /**
     * 校验当前租户是否开启指定 AI 子功能，未开启则返回错误 Response
     * @param string $module voice_report / anomaly / qa / crm_follow
     */
    protected function checkModule(string $module): ?Response
    {
        $tenantId = $this->getTenantId();
        if (!\tenant_ai_module_enabled($tenantId, $module)) {
            return $this->error('该 AI 子功能已关闭，请在「AI 套餐管理」->「全局开关」中开启');
        }
        return null;
    }

    /**
     * 安全调用 AI，失败时返回 null 不抛异常
     */
    protected function safeAiCall(callable $fn)
    {
        try {
            // 检查租户与全局是否允许使用 AI
            $tenantId = $this->getTenantId();
            if (!\tenant_ai_available($tenantId)) {
                return $this->error('AI 功能未启用或租户未购买，请联系管理员开通');
            }
            if (!$this->getAiService()->checkRateLimit()) {
                return $this->error('今日 AI 调用次数已达上限');
            }
            return $fn();
        } catch (\Throwable $e) {
            \think\facade\Log::error('AI module error: ' . $e->getMessage());
            return $this->error('AI 服务暂时不可用，请稍后重试');
        }
    }
}
