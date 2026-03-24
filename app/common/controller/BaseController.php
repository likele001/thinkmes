<?php
declare(strict_types=1);

namespace app\common\controller;

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

}
