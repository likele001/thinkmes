<?php
declare(strict_types=1);

namespace app\index\controller\prompt;

use app\common\controller\BaseController;
use app\common\model\UserModel;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;
use think\Response;
use think\exception\HttpResponseException;

/**
 * AI 提示词工坊 - 前端 H5 应用基类
 * 从 cookie user_token 解析当前用户，未登录跳转登录页
 */
abstract class BasePrompt extends BaseController
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
        // Cache miss 时从数据库恢复
        if (!$payload || !is_array($payload)) {
            try {
                $row = Db::name('user_token')
                    ->where('token', $token)
                    ->where('expire_time', '>', time())
                    ->find();
                if ($row) {
                    $payload = ['user_id' => (int)$row['user_id'], 'tenant_id' => (int)$row['tenant_id']];
                    $remainTtl = (int)$row['expire_time'] - time();
                    if ($remainTtl > 0) {
                        Cache::set($cacheKey, $payload, $remainTtl);
                    }
                }
            } catch (\Throwable $e) {}
        }
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
        View::assign('promptUser', $this->userInfo);
        View::assign('promptUserId', $this->userId);
        View::assign('promptTenantId', $this->tenantId);
        View::assign('promptToken', $token);
    }

    protected function redirectToLogin(): Response
    {
        $root = rtrim((string) request()->root(true), '/');
        $current = $root . '/index/prompt/index';
        return redirect($root . '/index/user/login?redirect=' . urlencode($current));
    }

    /** 渲染提示词工坊布局 */
    protected function fetchWithLayout(string $template): string
    {
        View::assign('promptUser', $this->userInfo ?: []);
        $content = View::fetch($template);
        View::assign('__CONTENT__', $content);
        return View::fetch('layout/prompt');
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
