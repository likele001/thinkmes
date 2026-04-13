<?php
declare(strict_types=1);

namespace app\index\controller;

use think\facade\View;
use think\facade\Request;
use think\facade\Lang;
use think\Response;

class DeveloperCenter
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

    private function fetchWithLayout(string $template): string
    {
        $this->ensureLang();
        $content = View::fetch($template);
        View::assign('__CONTENT__', $content);
        return View::fetch('layout/default');
    }

    public function login(): string
    {
        View::assign('title', '开发者登录');
        $redirect = (string) Request::get('redirect', '/index/store/publish');
        View::assign('redirect', $redirect);
        return $this->fetchWithLayout('developer/login');
    }

    public function register(): string
    {
        View::assign('title', '开发者注册');
        $redirect = (string) Request::get('redirect', '/index/developer/center');
        View::assign('redirect', $redirect);
        return $this->fetchWithLayout('developer/register');
    }

    public function center(): string
    {
        View::assign('title', '开发者中心');
        return $this->fetchWithLayout('developer/center');
    }
}
