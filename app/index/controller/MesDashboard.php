<?php
declare(strict_types=1);

namespace app\index\controller;

use think\facade\Cache;
use think\facade\Lang;
use think\facade\Request;
use think\facade\View;
use think\Response;

class MesDashboard
{
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

    private function loginUrl(): string
    {
        $root = rtrim((string) request()->root(true), '/');
        return $root . '/index/user/login';
    }

    private function isValidToken(string $token): bool
    {
        if ($token === '') {
            return false;
        }
        $cacheKey = \app\api\middleware\UserAuth::CACHE_PREFIX . $token;
        $payload = Cache::get($cacheKey);
        return !empty($payload) && is_array($payload) && isset($payload['user_id']) && (int) $payload['user_id'] > 0;
    }

    public function index(): string|Response
    {
        $this->ensureLang();
        $token = (string) (Request::cookie('user_token') ?? '');
        if ($token !== '') {
            $token = urldecode($token);
        }
        if (!$this->isValidToken($token)) {
            $redirect = urlencode((string) request()->url(true));
            return redirect($this->loginUrl() . '?redirect=' . $redirect);
        }

        View::assign('title', 'MES 全链路数据监管大屏');
        return View::fetch('mes_dashboard/index');
    }
}

