<?php
declare(strict_types=1);

namespace app\index\controller;

use think\facade\View;
use think\Response;

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
    public function index(): string
    {
        View::assign('title', '会员中心');
        return $this->fetchWithLayout('user/index');
    }

    /**
     * 登录页
     */
    public function login(): string
    {
        View::assign('title', '用户登录');
        return View::fetch('user/login');
    }

    /**
     * 注册页
     */
    public function register(): string
    {
        View::assign('title', '用户注册');
        return View::fetch('user/register');
    }

    /**
     * 个人资料
     */
    public function profile(): string
    {
        View::assign('title', '个人资料');
        return $this->fetchWithLayout('user/profile');
    }

    /**
     * 修改密码
     */
    public function changepwd(): string
    {
        View::assign('title', '修改密码');
        return $this->fetchWithLayout('user/changepwd');
    }

    /**
     * 找回密码 - 发送验证码
     */
    public function forgot(): string
    {
        View::assign('title', '找回密码');
        return View::fetch('user/forgot');
    }

    /**
     * 找回密码 - 重置
     */
    public function resetpwd(): string
    {
        View::assign('title', '重置密码');
        return View::fetch('user/resetpwd');
    }

    /**
     * 登出：前端清除 token 后跳转登录；也可服务端重定向
     */
    public function logout(): Response
    {
        return redirect((string) url('user/login'));
    }
}
