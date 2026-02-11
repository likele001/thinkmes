<?php
declare(strict_types=1);

namespace app\common\middleware;

use app\admin\model\TenantModel;
use app\admin\model\TenantPackageModel;
use app\admin\model\AdminModel;
use app\common\model\UserModel;
use Closure;
use think\Request;
use think\Response;

/**
 * 租户资源限制检查中间件
 * 检查租户是否超出套餐限制（管理员数、用户数等）
 */
class TenantResourceCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = (int) ($request->tenantId ?? 0);
        
        // 平台超管不限制
        if ($tenantId === 0) {
            return $next($request);
        }
        
        // 获取租户信息
        $tenant = TenantModel::find($tenantId);
        if (!$tenant || $tenant->status !== 1) {
            return json(['code' => 0, 'msg' => '租户不存在或已禁用']);
        }
        
        // 检查租户是否过期
        if ($tenant->expire_time !== null && $tenant->expire_time > 0 && $tenant->expire_time < time()) {
            return json(['code' => 0, 'msg' => '租户已过期，请联系管理员续费']);
        }
        
        // 获取套餐信息
        $package = TenantPackageModel::find($tenant->package_id);
        if (!$package) {
            return json(['code' => 0, 'msg' => '套餐不存在']);
        }
        
        // 检查管理员数限制
        $adminCount = AdminModel::where('tenant_id', $tenantId)->where('status', 1)->count();
        if ($package->max_admin > 0 && $adminCount >= $package->max_admin) {
            // 如果是创建管理员的请求，直接返回错误
            if ($request->controller() === 'Admin' && $request->action() === 'addPost') {
                return json(['code' => 0, 'msg' => '已达到最大管理员数限制（' . $package->max_admin . '人），请升级套餐或删除其他管理员']);
            }
        }
        
        // 检查用户数限制
        $userCount = UserModel::where('tenant_id', $tenantId)->where('status', 1)->count();
        if ($package->max_user > 0 && $userCount >= $package->max_user) {
            // 如果是用户注册请求，直接返回错误
            if ($request->module() === 'api' && $request->controller() === 'User' && $request->action() === 'register') {
                return json(['code' => 0, 'msg' => '已达到最大用户数限制（' . $package->max_user . '人），请联系管理员升级套餐']);
            }
            // 如果是后台创建用户请求
            if ($request->controller() === 'Member' && $request->action() === 'addPost') {
                return json(['code' => 0, 'msg' => '已达到最大用户数限制（' . $package->max_user . '人），请升级套餐或删除其他用户']);
            }
        }
        
        // 将套餐信息存储到请求中，供控制器使用
        $request->package = $package;
        $request->tenant = $tenant;
        $request->adminCount = $adminCount;
        $request->userCount = $userCount;
        
        return $next($request);
    }
}
