<?php
declare(strict_types=1);

namespace app\index\controller;

use think\facade\View;
use think\facade\Request;
use think\facade\Cache;
use think\Response;
use app\api\middleware\CustomerAuth;

class Customer
{
    private function fetchWithLayout(string $template, string $layout = 'layout/default'): string
    {
        $content = View::fetch($template);
        View::assign('__CONTENT__', $content);
        return View::fetch($layout);
    }

    private function requireCustomerToken(): ?Response
    {
        $token = (string) (Request::cookie('customer_token') ?? '');
        if ($token === '') {
            return redirect((string) url('customer/login'));
        }
        $key = CustomerAuth::CACHE_PREFIX . $token;
        $payload = Cache::get($key);
        if (!$payload || !is_array($payload)) {
            return redirect((string) url('customer/login'));
        }
        return null;
    }

    public function login(): string
    {
        View::assign('title', '客户登录');
        return $this->fetchWithLayout('customer/login', 'layout/default');
    }

    public function index(): string|Response
    {
        $err = $this->requireCustomerToken();
        if ($err !== null) {
            return $err;
        }
        View::assign('title', '客户下单');
        return $this->fetchWithLayout('customer/index', 'layout/customer');
    }

    public function orders(): string|Response
    {
        $err = $this->requireCustomerToken();
        if ($err !== null) {
            return $err;
        }
        View::assign('title', '我的订单');
        return $this->fetchWithLayout('customer/orders', 'layout/customer');
    }

    public function logout(): Response
    {
        $token = (string) (Request::cookie('customer_token') ?? '');
        if ($token !== '') {
            CustomerAuth::invalidateToken($token);
        }
        return redirect((string) url('customer/login'))
            ->cookie('customer_token', '', -86400);
    }
}
