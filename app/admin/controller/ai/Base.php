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
     * 安全调用 AI，失败时返回 null 不抛异常
     */
    protected function safeAiCall(callable $fn)
    {
        try {
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
