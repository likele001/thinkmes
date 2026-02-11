<?php
declare(strict_types=1);

namespace app\common\middleware;

use app\admin\model\TenantModel;
use app\admin\model\TenantPackageModel;
use app\admin\model\TenantPackageFeatureModel;
use Closure;
use think\Request;
use think\Response;

/**
 * 租户功能权限检查中间件
 * 检查租户是否有权限访问某个功能模块
 */
class TenantFeatureCheck
{
    /**
     * 功能代码到路由的映射（可根据实际业务调整）
     * 格式：'功能代码' => ['模块' => '模块名', '控制器' => '控制器名', '动作' => '动作名']
     */
    protected array $featureRouteMap = [
        // 示例：可以根据实际业务添加
        // 'order' => ['module' => 'admin', 'controller' => 'Order', 'action' => 'index'],
        // 'product' => ['module' => 'admin', 'controller' => 'Product', 'action' => 'index'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = (int) ($request->tenantId ?? 0);
        
        // 平台超管拥有所有功能
        if ($tenantId === 0) {
            return $next($request);
        }
        
        // 获取租户信息
        $tenant = TenantModel::find($tenantId);
        if (!$tenant || $tenant->status !== 1) {
            return json(['code' => 0, 'msg' => '租户不存在或已禁用']);
        }
        
        // 获取套餐信息
        $package = TenantPackageModel::find($tenant->package_id);
        if (!$package) {
            return json(['code' => 0, 'msg' => '套餐不存在']);
        }
        
        // 检查是否有功能限制（如果套餐没有配置任何功能，则允许所有功能）
        $features = TenantPackageFeatureModel::where('package_id', $package->id)->column('feature_code');
        
        // 如果套餐配置了功能列表，则只允许访问配置的功能
        if (!empty($features)) {
            // 根据当前路由判断功能代码（这里需要根据实际业务逻辑实现）
            $featureCode = $this->getFeatureCodeByRoute($request);
            
            if ($featureCode && !in_array($featureCode, $features, true)) {
                return json(['code' => 0, 'msg' => '您的套餐不支持此功能，请升级套餐']);
            }
        }
        
        // 将功能列表存储到请求中，供控制器使用
        $request->packageFeatures = $features;
        
        return $next($request);
    }

    /**
     * 根据路由获取功能代码
     * 可以根据实际业务逻辑实现路由到功能代码的映射
     */
    protected function getFeatureCodeByRoute(Request $request): ?string
    {
        $module = $request->module();
        $controller = $request->controller();
        $action = $request->action();
        
        // 示例：可以根据控制器和动作判断功能代码
        // 这里提供一个简单的示例，实际应该根据业务需求实现
        
        // 如果配置了映射，使用映射
        foreach ($this->featureRouteMap as $code => $route) {
            if (($route['module'] ?? '') === $module &&
                ($route['controller'] ?? '') === $controller &&
                ($route['action'] ?? '') === $action) {
                return $code;
            }
        }
        
        // 默认返回null，表示不限制（如果套餐没有配置功能列表，则允许所有）
        return null;
    }
}
