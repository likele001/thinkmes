<?php
declare(strict_types=1);

namespace app\common\middleware;

use Closure;
use think\Request;
use think\Response;
use think\facade\Db;
use think\facade\Session;

/**
 * AI 计费/使用记录中间件
 * 功能：记录每次 AI 调用次数（按天统计），为后续按量/按次计费或统计提供基础数据
 */
class AIBilling
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $tenantId = Session::get('admin.tenant_id') ?: (int) $request->header('tenant_id');
            $admin = Session::get('admin_info') ?: [];
            $adminId = (int) ($admin['id'] ?? 0);

            // 模块/动作从 pathinfo 或 controller/action 推断
            $path = trim((string) $request->pathinfo(), '/');
            $controller = $request->controller();
            $action = $request->action();
            $module = $controller ?: $path;

            $statDate = date('Y-m-d');
            $now = time();

            $where = [
                'tenant_id' => (int) $tenantId,
                'module' => (string) $module,
                'action' => (string) $action,
                'stat_date' => $statDate,
            ];

            $exists = Db::name('ai_usage')->where($where)->find();
            if ($exists) {
                Db::name('ai_usage')->where('id', $exists['id'])->update([
                    'call_count' => Db::raw('call_count + 1'),
                    'update_time' => $now,
                ]);
            } else {
                Db::name('ai_usage')->insert(array_merge($where, [
                    'admin_id' => $adminId,
                    'call_count' => 1,
                    'tokens_used' => 0,
                    'update_time' => $now,
                ]));
            }
        } catch (\Throwable $e) {
            // 计费记录非阻塞，记录错误到日志即可
            \think\facade\Log::error('AIBilling error: ' . $e->getMessage());
        }

        return $next($request);
    }
}
