<?php
namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        if ($request->isAjax() || str_starts_with($request->pathinfo(), 'api/')) {
            $msg = $e->getMessage();
            if ($e instanceof ValidateException) {
                return json(['code' => 0, 'msg' => $msg, 'data' => []]);
            }
            if ($e instanceof HttpException) {
                return json(['code' => 0, 'msg' => $msg ?: '请求错误', 'data' => []]);
            }
            // 调试模式或开启 show_error_msg 时显示真实错误，否则显示友好提示
            if (!config('app.show_error_msg') && !$this->app->isDebug()) {
                $msg = config('app.error_message', '页面错误！请稍后再试～');
            }
            return json(['code' => 0, 'msg' => $msg, 'data' => []]);
        }
        return parent::render($request, $e);
    }
}
