<?php
declare(strict_types=1);

namespace app\index\controller;

use think\facade\View;
use think\Response;
use think\facade\Request;

/**
 * 前端 C 端用户：登录、注册、会员中心、个人资料、修改密码、找回密码
 * 页面仅渲染视图，实际注册/登录/资料提交由前端 JS 调用 /api/user/* 接口
 */
class User
{
    private function fetchWithLayout(string $template): string
    {
        $content = View::fetch($template);
        View::assign('__CONTENT__', $content);
        return View::fetch('layout/default');
    }

    /**
     * 会员中心首页（需前端根据 token 判断是否已登录，未登录跳转登录页）
     */
    public function index(): string|Response
    {
        $token = (string) (Request::cookie('user_token') ?? '');
        if ($token === '') {
            return redirect((string) url('user/login'));
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
        return redirect((string) url('user/login') . '?tab=register');
    }

    /**
     * 个人资料
     */
    public function profile(): string|Response
    {
        $token = (string) (Request::cookie('user_token') ?? '');
        if ($token === '') {
            return redirect((string) url('user/login'));
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
        if ($token === '') {
            return redirect((string) url('user/login'));
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
        return redirect((string) url('user/login'));
    }
}
