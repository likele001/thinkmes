<?php
declare(strict_types=1);

namespace app\index\controller;

use app\common\controller\BaseController;
use think\response\Redirect;

class Index extends BaseController
{
    /**
     * 站点根路径 /：未安装则进安装页，已安装则进后台入口
     */
    public function index(): Redirect
    {
        $lockFile = runtime_path() . 'install.lock';
        if (!is_file($lockFile)) {
            return redirect('/install');
        }
        $envFile = root_path() . '.env';
        if (is_file($envFile)) {
            $content = (string) file_get_contents($envFile);
            if (preg_match('/^\s*ADMIN_ENTRY\s*=\s*(\S+)/m', $content, $m) && trim($m[1]) !== '') {
                $entry = trim($m[1]);
                if (substr($entry, -4) === '.php') {
                    $entry = substr($entry, 0, -4);
                }
                return redirect('/' . $entry . '/index/login');
            }
        }
        return redirect('/admin/index/login');
    }
}
