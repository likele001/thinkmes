<?php
declare(strict_types=1);

namespace app\index\controller;

use think\facade\View;
use think\Response;
use think\facade\Request;
use think\facade\Cache;
use think\facade\Lang;

/**
 * 前端 C 端用户：登录、注册、会员中心、个人资料、修改密码、找回密码
 * 页面仅渲染视图，实际注册/登录/资料提交由前端 JS 调用 /api/user/* 接口
 * 多语言：自动加载 lang/{locale}/User.php，模板内用 {:lang('key')}
 */
class User
{
    /** 按 cookie 设置语言并加载当前控制器语言包（index 控制器无 initialize 调用，故在此执行） */
    private function ensureLang(): void
    {
        $cookieVar = config('lang.cookie_var', 'think_lang');
        $cookieVal = request()->cookie($cookieVar, '');
        if ($cookieVal !== '' && $cookieVal !== null) {
            $allow = config('lang.allow_lang_list', []);
            if (is_array($allow) && in_array($cookieVal, $allow, true)) {
                Lang::setLangSet($cookieVal);
            }
        }
        $langSet = Lang::getLangSet();
        $ctrl = (new \ReflectionClass($this))->getShortName();
        $path = app()->getAppPath() . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . $ctrl . '.php';
        if (is_file($path)) {
            Lang::load($path);
        }
    }

    /** 未登录时跳转的登录页 URL（带应用前缀 /index/） */
    private function loginUrl(): string
    {
        $root = rtrim((string) request()->root(true), '/');
        return $root . '/index/user/login';
    }

    /** 验证 token 是否有效 */
    private function isValidToken(string $token): bool
    {
        if ($token === '') {
            return false;
        }
        $cacheKey = \app\api\middleware\UserAuth::CACHE_PREFIX . $token;
        $payload = Cache::get($cacheKey);
        return !empty($payload) && is_array($payload) && isset($payload['user_id']) && $payload['user_id'] > 0;
    }

    private function fetchWithLayout(string $template): string
    {
        $this->ensureLang();
        $content = View::fetch($template);
        View::assign('__CONTENT__', $content);
        return View::fetch('layout/default');
    }

    /**
     * 会员中心首页：未登录或 token 无效必须跳转登录/注册页
     */
    public function index(): string|Response
    {
        $token = (string) (Request::cookie('user_token') ?? '');
        if (!$this->isValidToken($token)) {
            return redirect($this->loginUrl());
        }
        View::assign('title', '会员中心');
        return $this->fetchWithLayout('user/index');
    }

    /**
     * 登录页
     */
    public function login(): string
    {
        View::assign('title', '用户登录');
        return $this->fetchWithLayout('user/login');
    }

    /**
     * 注册页：统一跳转到登录页并切换到注册 Tab
     */
    public function register(): string|Response
    {
        return redirect($this->loginUrl() . '?tab=register');
    }

    /**
     * 个人资料
     */
    public function profile(): string|Response
    {
        $token = (string) (Request::cookie('user_token') ?? '');
        if (!$this->isValidToken($token)) {
            return redirect($this->loginUrl());
        }
        View::assign('title', '个人资料');
        return $this->fetchWithLayout('user/profile');
    }

    /**
     * 修改密码
     */
    public function changepwd(): string|Response
    {
        $token = (string) (Request::cookie('user_token') ?? '');
        if (!$this->isValidToken($token)) {
            return redirect($this->loginUrl());
        }
        View::assign('title', '修改密码');
        return $this->fetchWithLayout('user/changepwd');
    }

    /**
     * 找回密码 - 发送验证码
     */
    public function forgot(): string
    {
        View::assign('title', '找回密码');
        return $this->fetchWithLayout('user/forgot');
    }

    /**
     * 找回密码 - 重置
     */
    public function resetpwd(): string
    {
        View::assign('title', '重置密码');
        return $this->fetchWithLayout('user/resetpwd');
    }

    /**
     * 登出：前端清除 token 后跳转登录；也可服务端重定向
     */
    public function logout(): Response
    {
        return redirect($this->loginUrl());
    }
}
