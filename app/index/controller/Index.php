<?php
declare(strict_types=1);

namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\Lang;
use think\facade\View;
use think\Response;
use think\response\Redirect;

class Index extends BaseController
{
    /** 与 User 一致：cookie 语言 + 加载布局所需语言包 */
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
        $base = app()->getAppPath() . 'lang' . DIRECTORY_SEPARATOR;
        foreach ([$langSet . '.php', $langSet . '/Index.php', $langSet . '/User.php'] as $rel) {
            $path = $base . $rel;
            if (is_file($path)) {
                Lang::load($path);
            }
        }
    }

    private function fetchWithLayout(string $template): string
    {
        $this->ensureLang();
        $content = View::fetch($template);
        View::assign('__CONTENT__', $content);
        return View::fetch('layout/default');
    }

    /**
     * 站点根 /index/index：未安装 → 安装页；已安装 → 前台欢迎页（用户中心 / 后台入口），不再默认 302 只进后台
     */
    public function index(): string|Redirect|Response
    {
        if (!install_is_locked()) {
            return redirect('/install');
        }
        $this->ensureLang();
        $adminLoginUrl = '/admin/index/login';
        $envFile = root_path() . '.env';
        if (is_file($envFile)) {
            $content = (string) file_get_contents($envFile);
            if (preg_match('/^\s*ADMIN_ENTRY\s*=\s*(\S+)/m', $content, $m) && trim($m[1]) !== '') {
                $entry = trim($m[1]);
                if (substr($entry, -4) === '.php') {
                    $entry = substr($entry, 0, -4);
                }
                $adminLoginUrl = '/' . $entry . '/index/login';
            }
        }
        $root = rtrim((string) request()->root(true), '/');
        View::assign('title', Lang::get('welcome_page_title') ?: '首页');
        View::assign('admin_login_url', $adminLoginUrl);
        View::assign('user_login_url', $root . '/index/user/login');
        View::assign('wemedia_url', $root . '/index/wemedia/index');
        return $this->fetchWithLayout('index/welcome');
    }

    /**
     * 官网首页
     */
    public function homepage(): string
    {
        return View::fetch('index/homepage');
    }

    /**
     * 使用指南
     */
    public function guide(): string
    {
        return View::fetch('index/guide');
    }
}
