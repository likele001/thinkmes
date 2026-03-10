<?php
declare(strict_types=1);

namespace app\index\controller;

use think\facade\View;
use think\facade\Request;
use think\facade\Cache;
use think\facade\Lang;
use think\Response;
use app\api\middleware\CustomerAuth;

class Customer
{
    /** 按 cookie 设置语言并加载当前控制器语言包 */
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

    private function fetchWithLayout(string $template, string $layout = 'layout/default'): string
    {
        $this->ensureLang();
        View::assign('currentLang', Lang::getLangSet());
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
