<?php
declare(strict_types=1);

namespace app\common\controller;

use app\admin\model\TenantModel;
use app\admin\model\TenantPackageModel;
use app\admin\model\AdminModel;
use app\common\model\UserModel;
use think\App;
use think\exception\ValidateException;
use think\Response;
use think\Validate;
use think\response\Json;

/**
 * 基础控制器（统一响应）
 */
abstract class BaseController
{
    protected $request;
    protected $app;
    protected $batchValidate = false;
    protected $middleware = [];

    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
        $this->initialize();
    }

    protected function initialize(): void
    {}

    /**
     * 成功响应
     */
    protected function success(string $msg = '操作成功', array $data = [], int $code = 1): Json
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 失败响应
     */
    protected function error(string $msg = '操作失败', int $code = 0): Json
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => [],
        ]);
    }

    protected function validate(array $data, string|array $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            $scene = null;
            if (strpos($validate, '.') !== false) {
                [$validate, $scene] = explode('.', $validate, 2);
            }
            $class = str_contains($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if ($scene !== null) {
                $v->scene($scene);
            }
        }
        $v->message($message);
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }
        return $v->failException(true)->check($data);
    }

    /**
     * 检查租户资源限制（管理员数、用户数等）
     * @param string $resourceType 资源类型：admin/user
     * @return array ['allowed' => bool, 'current' => int, 'max' => int, 'msg' => string]
     */
    protected function checkResourceLimit(string $resourceType): array
    {
        $tenantId = (int) ($this->request->tenantId ?? 0);
        if ($tenantId === 0) {
            return ['allowed' => true, 'current' => 0, 'max' => 0, 'msg' => ''];
        }

        $package = $this->request->package ?? null;
        if (!$package) {
            // 如果没有从中间件获取，手动查询
            $tenant = TenantModel::find($tenantId);
            if (!$tenant) {
                return ['allowed' => false, 'current' => 0, 'max' => 0, 'msg' => '租户不存在'];
            }
            $package = TenantPackageModel::find($tenant->package_id);
            if (!$package) {
                return ['allowed' => false, 'current' => 0, 'max' => 0, 'msg' => '套餐信息不存在'];
            }
        }

        $current = 0;
        $max = 0;
        $resourceName = '';

        if ($resourceType === 'admin') {
            $current = AdminModel::where('tenant_id', $tenantId)->where('status', 1)->count();
            $max = (int) $package->max_admin;
            $resourceName = '管理员';
        } elseif ($resourceType === 'user') {
            $current = UserModel::where('tenant_id', $tenantId)->where('status', 1)->count();
            $max = (int) $package->max_user;
            $resourceName = '用户';
        } else {
            return ['allowed' => false, 'current' => 0, 'max' => 0, 'msg' => '未知的资源类型'];
        }

        $allowed = ($max === 0 || $current < $max);
        $msg = $allowed ? '' : "已达到最大{$resourceName}数限制（{$max}人），请升级套餐";

        return [
            'allowed' => $allowed,
            'current' => $current,
            'max' => $max,
            'msg' => $msg
        ];
    }
}
