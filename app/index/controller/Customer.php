<?php
declare(strict_types=1);

namespace app\index\controller;

use think\facade\View;
use think\facade\Request;
use think\Response;

class Customer
{
    private function fetchWithLayout(string $template): string
    {
        $content = View::fetch($template);
        View::assign('__CONTENT__', $content);
        return View::fetch('layout/default');
    }

    public function login(): string
    {
        View::assign('title', '客户登录');
        return $this->fetchWithLayout('customer/login');
    }

    public function index(): string|Response
    {
        $token = (string) (Request::cookie('customer_token') ?? '');
        if ($token === '') {
            return redirect((string) url('customer/login'));
        }
        View::assign('title', '客户下单');
        return $this->fetchWithLayout('customer/index');
    }

    public function orders(): string|Response
    {
        $token = (string) (Request::cookie('customer_token') ?? '');
        if ($token === '') {
            return redirect((string) url('customer/login'));
        }
        View::assign('title', '我的订单');
        return $this->fetchWithLayout('customer/orders');
    }
}

