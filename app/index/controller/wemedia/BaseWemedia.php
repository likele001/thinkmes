<?php
declare(strict_types=1);

namespace app\index\controller\wemedia;

use app\common\controller\BaseController;
use app\common\model\UserModel;
use think\facade\Cache;
use think\facade\Request;
use think\facade\View;
use think\Response;
use think\exception\HttpResponseException;

/**
 * 自媒体工作流 - 用户中心独立应用基类
 * 从 cookie user_token 解析当前用户，未登录跳转登录页
 */
abstract class BaseWemedia extends BaseController
{
    /** @var int */
    protected $userId = 0;
    /** @var int */
    protected $tenantId = 0;
    /** @var array */
    protected $userInfo = [];

    protected function initialize(): void
    {
        parent::initialize();
        $token = (string) (Request::cookie('user_token') ?? Request::get('token', ''));
        if ($token === '') {
            throw new HttpResponseException($this->redirectToLogin());
        }
        $cacheKey = \app\api\middleware\UserAuth::CACHE_PREFIX . $token;
        $payload = Cache::get($cacheKey);
        if (!$payload || !is_array($payload)) {
            throw new HttpResponseException($this->redirectToLogin());
        }
        $this->userId   = (int) ($payload['user_id'] ?? 0);
        $this->tenantId = (int) ($payload['tenant_id'] ?? 0);
        if ($this->userId <= 0) {
            throw new HttpResponseException($this->redirectToLogin());
        }
        $user = UserModel::where('id', $this->userId)
            ->where('tenant_id', $this->tenantId)
            ->where('status', 1)
            ->find();
        if (!$user) {
            throw new HttpResponseException($this->redirectToLogin());
        }
        $this->userInfo = $user->toArray();
        View::assign('wemediaUser', $this->userInfo);
        View::assign('wemediaUserId', $this->userId);
        View::assign('wemediaTenantId', $this->tenantId);
        View::assign('wemediaToken', $token);
    }

    protected function redirectToLogin(): Response
    {
        $root = rtrim((string) request()->root(true), '/');
        return redirect($root . '/index/user/login');
    }

    /** 渲染自媒体布局（Pear Admin 风格） */
    protected function fetchWithLayout(string $template): string
    {
        View::assign('wemediaUser', $this->userInfo ?: []);
        $content = View::fetch($template);
        View::assign('__CONTENT__', $content);
        return View::fetch('layout/wemedia');
    }

    /** 返回 JSON（接口用） */
    protected function jsonSuccess(string $msg = '成功', array $data = []): Response
    {
        return json(['code' => 1, 'msg' => $msg, 'data' => $data]);
    }

    protected function jsonError(string $msg = '失败'): Response
    {
        return json(['code' => 0, 'msg' => $msg, 'data' => []]);
    }
}
