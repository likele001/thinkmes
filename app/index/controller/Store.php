<?php
declare(strict_types=1);

namespace app\index\controller;

use think\facade\Lang;
use think\facade\View;

class Store
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

    public function index(): string
    {
        View::assign('title', '应用商店');
        return $this->fetchWithLayout('store/index');
    }

    public function detail(): string
    {
        View::assign('title', '应用详情');
        View::assign('pluginId', (int) request()->get('id', 0));
        return $this->fetchWithLayout('store/detail');
    }

    public function publish(): string
    {
        View::assign('title', '发布应用');
        return $this->fetchWithLayout('store/publish');
    }

    public function my(): string
    {
        View::assign('title', '我的应用');
        return $this->fetchWithLayout('store/my');
    }
}
