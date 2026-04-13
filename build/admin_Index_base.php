<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\admin\model\AdminModel;
use app\admin\model\AuthRuleModel;
use app\admin\model\ConfigModel;
use app\common\lib\Auth;
use app\common\lib\Hook;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

/**
 * 基础版后台 Index（无 MES、无租户、无用户统计）
 */
class Index extends Backend
{
    public function login(): string|Response
    {
        if ($this->request->isPost()) {
            $username = trim((string) $this->request->post('username'));
            $password = (string) $this->request->post('password');
            $url = trim((string) $this->request->post('url', 'admin/index/index'));

            if (strlen($username) < 2 || strlen($username) > 50) {
                return $this->error('请输入正确的账号');
            }
            if (strlen($password) < 6 || strlen($password) > 32) {
                return $this->error('密码长度为 6-32 位');
            }

            $loginCaptcha = ConfigModel::where('group', 'safe')->where('name', 'login_captcha')->value('value');
            if ($loginCaptcha === '1' || $loginCaptcha === 'true') {
                $captcha = trim((string) $this->request->post('captcha', ''));
                if ($captcha === '' || $captcha !== Session::get('captcha')) {
                    return $this->error('验证码错误');
                }
            }

            $admin = AdminModel::where('username', $username)->where('status', 1)->find();
            if (!$admin) {
                return $this->error('账号不存在或已禁用');
            }
            if (!password_verify($password, $admin['password'])) {
                return $this->error('密码错误');
            }

            $adminId = (int) $admin['id'];
            $loginTime = time();
            $loginIp = $this->request->ip();
            Db::name('admin')->where('id', $adminId)->update(['login_time' => $loginTime, 'login_ip' => $loginIp]);

            $logData = [
                'admin_id'   => $adminId,
                'type'       => 'login',
                'content'    => '登录成功',
                'url'        => $this->request->url(),
                'ip'         => $loginIp,
                'create_time' => $loginTime,
            ];
            if ($this->hasTableColumn('log', 'tenant_id')) {
                $logData['tenant_id'] = 0;
            }
            Db::name('log')->insert($logData);

            $adminArr = $admin->toArray();
            unset($adminArr['password'], $adminArr['salt']);
            Session::set('admin_info', $adminArr);

            Hook::trigger('login_after', [$adminArr]);

            $redirectUrl = $url ?: 'admin/index/index';
            $path = preg_replace('#^admin/#', '', $redirectUrl);
            $fullUrl = rtrim($this->request->domain(), '/') . $this->getAdminUrlPrefix() . '/' . str_replace('.', '/', $path);
            if (!$this->request->isAjax()) {
                return redirect($fullUrl);
            }
            return $this->success('登录成功', ['url' => $fullUrl]);
        }

        if (Session::has('admin_info')) {
            return redirect($this->getAdminUrlPrefix() . '/index/index');
        }

        $loginCaptcha = ConfigModel::where('group', 'safe')->where('name', 'login_captcha')->value('value');
        $loginCaptchaOn = $loginCaptcha === '1' || $loginCaptcha === 'true';
        View::assign('title', '后台登录');
        View::assign('url', $this->request->get('url', 'admin/index/index'));
        View::assign('loginCaptchaOn', $loginCaptchaOn);
        return View::fetch('index/login');
    }

    public function logout(): Response
    {
        Session::delete('admin_info');
        return redirect($this->getAdminUrlPrefix() . '/index/login');
    }

    public function captcha(): Response
    {
        $code = (string) mt_rand(1000, 9999);
        Session::set('captcha', $code);
        $width = 120;
        $height = 40;
        $image = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($image, 248, 250, 252);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);
        $textColor = imagecolorallocate($image, 55, 65, 81);
        $accent = imagecolorallocate($image, 129, 140, 248);
        for ($i = 0; $i < 40; $i++) {
            $x = mt_rand(0, $width);
            $y = mt_rand(0, $height);
            imagesetpixel($image, $x, $y, $accent);
        }
        imagestring($image, 5, 28, 12, $code, $textColor);

        ob_start();
        imagepng($image);
        $data = ob_get_clean();

        return response($data, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function errorPage(): string
    {
        $msg = $this->request->get('msg', '无权限访问');
        View::assign('msg', $msg);
        View::assign('title', '无权限');
        return View::fetch('index/error');
    }

    /** 基础版控制台：仅管理员、角色、日志、附件、数据库统计 */
    public function index(): string
    {
        $adminCount = (int) Db::name('admin')->count();
        $roleCount = (int) Db::name('role')->count();
        $logCount = (int) Db::name('log')->count();
        $uploadTotal = (int) Db::name('upload')->count();

        $dbTables = 0;
        $dbSize = 0;
        try {
            $conn = Db::connect();
            $tables = $conn->query("SHOW TABLE STATUS");
            if (is_array($tables)) {
                $dbTables = count($tables);
                foreach ($tables as $t) {
                    $dbSize += (int) ($t['Data_length'] ?? 0) + (int) ($t['Index_length'] ?? 0);
                }
                $dbSize = round($dbSize / 1024 / 1024, 2);
            }
        } catch (\Throwable $e) {}

        $uploadSize = 0;
        $imageCount = 0;
        $imageSize = 0;
        try {
            $uploads = Db::name('upload')->select();
            foreach ($uploads as $u) {
                $size = (int) ($u['size'] ?? 0);
                $uploadSize += $size;
                $mime = strtolower($u['mime_type'] ?? '');
                $url = strtolower($u['url'] ?? '');
                if (strpos($mime, 'image/') === 0 || preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url)) {
                    $imageCount++;
                    $imageSize += $size;
                }
            }
            $uploadSize = round($uploadSize / 1024, 2);
            $imageSize = round($imageSize / 1024, 2);
        } catch (\Throwable $e) {}

        $auth = new Auth();
        list($menulist, $navlist, $fixedmenu, $referermenu) = $auth->getSidebar([
            'dashboard' => 'hot',
            'addon'     => ['new', 'red', 'badge'],
            'auth/rule' => 'Menu',
        ], 'dashboard');

        View::assign('title', '控制台');
        View::assign('stats', [
            'admin_count'      => $adminCount,
            'role_count'      => $roleCount,
            'tenant_count'    => 0,
            'user_count'      => 0,
            'attachment_count' => $uploadTotal,
            'log_count'       => $logCount,
            'db_tables'       => $dbTables,
            'db_size'         => $dbSize,
            'upload_size'     => $uploadSize,
            'image_count'     => $imageCount,
            'image_size'      => $imageSize,
            'register_trend'  => [],
            'tenant_stats'    => [],
        ]);
        View::assign('menulist', $menulist);
        View::assign('navlist', $navlist);
        View::assign('fixedmenu', $fixedmenu);
        View::assign('referermenu', $referermenu);

        return $this->fetchWithLayout('index/index');
    }

    public function menu(): Response
    {
        $admin = Session::get('admin_info');
        if (!$admin) {
            return $this->error('未登录');
        }
        $adminId = (int) $admin['id'];

        $auth = new Auth();
        $userRule = $auth->getRuleIds($adminId);

        $model = new AuthRuleModel();
        $ruleList = $model->where('status', 1)->where('ismenu', 1)->order('sort', 'desc')->order('id')->select()->toArray();

        foreach ($ruleList as $k => &$v) {
            $nameLower = strtolower($v['name'] ?? '');
            if (!in_array('*', $userRule, true) && !in_array($nameLower, $userRule, true)) {
                unset($ruleList[$k]);
                continue;
            }
            $v['icon'] = ($v['icon'] ?? '') . ' fa-fw';
            if (!isset($v['url']) || !$v['url']) {
                $name = $v['name'] ?? '';
                $prefix = $this->getAdminUrlPrefix();
                $v['url'] = str_starts_with($name, 'admin/') ? ($prefix . '/' . substr($name, 6)) : ($prefix . '/' . $name);
            }
            $v['title'] = $v['title'] ?? '';
            $v['menuclass'] = '';
            $v['menutabs'] = 'addtabs="' . ($v['id'] ?? '') . '"';
        }

        $presentAll = array_map(function ($it) { return strtolower($it['name'] ?? ''); }, $ruleList);
        $needCommon = [
            ['id' => 'virt_profile_center', 'name' => 'profile/index', 'title' => '个人中心', 'icon' => 'fas fa-user-cog', 'pid' => 0],
        ];
        foreach ($needCommon as $it) {
            if (!in_array(strtolower($it['name']), $presentAll, true)) {
                $ruleList[] = [
                    'id' => $it['id'],
                    'name' => $it['name'],
                    'title' => $it['title'],
                    'icon' => ($it['icon'] ?? '') . ' fa-fw',
                    'pid' => 0,
                    'url' => $this->getAdminUrlPrefix() . '/' . $it['name'],
                    'menuclass' => '',
                    'menutabs' => 'addtabs="' . $it['id'] . '"',
                ];
            }
        }

        $pidArr = array_unique(array_filter(array_column($ruleList, 'pid')));
        $pidDiffArr = array_diff($pidArr, array_column($ruleList, 'id'));
        foreach ($ruleList as $index => $item) {
            if (in_array($item['id'], $pidDiffArr)) {
                unset($ruleList[$index]);
            }
        }

        $tree = $this->buildMenuTree(array_values($ruleList), 0);
        return $this->success('', $tree);
    }

    protected function buildMenuTree(array $list, int $pid): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ((int) ($item['pid'] ?? 0) === $pid) {
                $curId = $item['id'] ?? 0;
                $children = is_numeric($curId) ? $this->buildMenuTree($list, (int) $curId) : [];
                $item['children'] = $children;
                $item['url'] = $children ? 'javascript:;' : $item['url'];
                $tree[] = $item;
            }
        }
        return $tree;
    }

    public function clearCache(): Response
    {
        $auth = new Auth();
        $auth->clearAllCache();
        \think\facade\Cache::clear();
        return $this->success('缓存已清理');
    }
}
