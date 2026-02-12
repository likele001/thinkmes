<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\admin\model\AdminModel;
use app\admin\model\AuthRuleModel;
use app\admin\model\ConfigModel;
use app\admin\model\LogModel;
use app\common\model\UserModel;
use app\common\lib\Auth;
use app\common\lib\Hook;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

class Index extends Backend
{
    /**
     * 登录：GET 展示页，POST 处理
     */
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
                $logData['tenant_id'] = (int) ($this->request->tenantId ?? 0);
            }
            Db::name('log')->insert($logData);

            $adminArr = $admin->toArray();
            unset($adminArr['password'], $adminArr['salt']);
            Session::set('admin_info', $adminArr);

            Hook::trigger('login_after', [$adminArr]);

            $redirectUrl = $url ?: 'admin/index/index';
            $fullUrl = $this->request->domain() . '/' . ltrim(str_replace('.', '/', $redirectUrl), '/');
            // 非 AJAX 请求直接 302 跳转到后台首页（仿 FastAdmin 传统表单提交）
            if (!$this->request->isAjax()) {
                return redirect($fullUrl);
            }
            return $this->success('登录成功', ['url' => $fullUrl]);
        }

        if (Session::has('admin_info')) {
            return redirect((string) url('/admin/index/index'));
        }

        View::assign('title', '后台登录');
        View::assign('url', $this->request->get('url', 'admin/index/index'));
        return View::fetch('index/login');
    }

    public function logout(): Response
    {
        Session::delete('admin_info');
        return redirect((string) url('/admin/index/login'));
    }

    public function captcha(): Response
    {
        // 简单验证码占位，可替换为 think-captcha 扩展
        $code = (string) mt_rand(1000, 9999);
        Session::set('captcha', $code);
        return response($code, 200, ['Content-Type' => 'text/plain']);
    }

    public function errorPage(): string
    {
        $msg = $this->request->get('msg', '无权限访问');
        View::assign('msg', $msg);
        View::assign('title', '无权限');
        return View::fetch('index/error');
    }

    public function index(): string
    {
        $tenantId = $this->getTenantId();
        $todayStart = strtotime(date('Y-m-d 00:00:00'));
        $todayEnd = time();
        $threeDaysAgo = strtotime('-3 days', $todayStart);
        $sevenDaysAgo = strtotime('-7 days', $todayStart);
        $monthAgo = strtotime('-30 days', $todayStart);
        
        // 基础统计
        // ThinkPHP 8: 使用 Db::name() 或模型的静态方法创建查询对象
        $adminQuery = Db::name('admin');
        $userQuery = Db::name('user');
        $logQuery = Db::name('log');
        $uploadQuery = Db::name('upload');
        
        if ($tenantId > 0) {
            $adminQuery->where('tenant_id', $tenantId);
            $userQuery->where('tenant_id', $tenantId);
            $logQuery->where('tenant_id', $tenantId);
        }
        
        // 用户相关统计
        $userTotal = (int) $userQuery->count();
        $userTodayReg = (int) $userQuery->where('create_time', '>=', $todayStart)->count();
        $userTodayLogin = (int) $userQuery->where('login_time', '>=', $todayStart)->where('login_time', '<=', $todayEnd)->count();
        $userThreeDays = (int) $userQuery->where('create_time', '>=', $threeDaysAgo)->count();
        $userSevenDays = (int) $userQuery->where('create_time', '>=', $sevenDaysAgo)->count();
        $userSevenActive = (int) $userQuery->where('login_time', '>=', $sevenDaysAgo)->count();
        $userMonthActive = (int) $userQuery->where('login_time', '>=', $monthAgo)->count();
        
        // 注册趋势（最近7天）
        $registerTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $dayStart = strtotime("-$i days", $todayStart);
            $dayEnd = strtotime("-$i days", $todayEnd);
            $count = (int) $userQuery->where('create_time', '>=', $dayStart)->where('create_time', '<', $dayEnd)->count();
            $registerTrend[] = [
                'date' => date('m-d', $dayStart),
                'count' => $count
            ];
        }
        
        // 数据库统计
        $dbTables = 0;
        $dbSize = 0;
        try {
            // ThinkPHP 8: 使用 connect()->query() 执行原生 SQL（Connection 对象有 query 方法）
            $conn = Db::connect();
            $tables = $conn->query("SHOW TABLE STATUS");
            if (is_array($tables)) {
                $dbTables = count($tables);
                foreach ($tables as $t) {
                    $dbSize += (int) ($t['Data_length'] ?? 0) + (int) ($t['Index_length'] ?? 0);
                }
                $dbSize = round($dbSize / 1024 / 1024, 2); // MB
            }
        } catch (\Throwable $e) {
            // 忽略错误，使用默认值
        }
        
        // 附件统计
        $uploadTotal = (int) $uploadQuery->count();
        $uploadSize = 0;
        $imageCount = 0;
        $imageSize = 0;
        try {
            $uploads = $uploadQuery->select();
            foreach ($uploads as $u) {
                $size = (int) ($u['size'] ?? 0);
                $uploadSize += $size;
                $mime = strtolower($u['mime_type'] ?? '');
                $url = strtolower($u['url'] ?? '');
                $isImage = strpos($mime, 'image/') === 0 || 
                          preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url);
                if ($isImage) {
                    $imageCount++;
                    $imageSize += $size;
                }
            }
            $uploadSize = round($uploadSize / 1024, 2); // KB
            $imageSize = round($imageSize / 1024, 2); // KB
        } catch (\Throwable $e) {
            // 忽略错误
        }
        
        // 租户统计（仅平台超管显示）
        $tenantStats = [];
        if ($tenantId === 0) {
            try {
                $tenantTotal = (int) Db::name('tenant')->count();
                $tenantNormal = (int) Db::name('tenant')->where('status', 1)->count();
                $tenantDisabled = (int) Db::name('tenant')->where('status', 0)->count();
                $tenantExpiring = (int) Db::name('tenant')
                    ->where('status', 1)
                    ->where('expire_time', '>', 0)
                    ->where('expire_time', '<=', time() + 7 * 86400) // 7天内到期
                    ->count();
            } catch (\Throwable $e) {
                $tenantTotal = 0;
                $tenantNormal = 0;
                $tenantDisabled = 0;
                $tenantExpiring = 0;
            }
            
            // 订单统计（检查表是否存在）
            $orderTotal = 0;
            $orderPaid = 0;
            $orderPending = 0;
            $orderAmount = 0.00;
            try {
                if ($this->hasTable('tenant_order')) {
                    $orderTotal = (int) Db::name('tenant_order')->count();
                    $orderPaid = (int) Db::name('tenant_order')->where('status', 1)->count();
                    $orderPending = (int) Db::name('tenant_order')->where('status', 0)->count();
                    $orderAmount = (float) Db::name('tenant_order')->where('status', 1)->sum('amount') ?: 0;
                }
            } catch (\Throwable $e) {
                // 表不存在，使用默认值0
            }
            
            $tenantStats = [
                'tenant_total' => $tenantTotal,
                'tenant_normal' => $tenantNormal,
                'tenant_disabled' => $tenantDisabled,
                'tenant_expiring' => $tenantExpiring,
                'order_total' => $orderTotal,
                'order_paid' => $orderPaid,
                'order_pending' => $orderPending,
                'order_amount' => round($orderAmount, 2),
            ];
        }
        
        View::assign('title', '控制台');
        View::assign('stats', [
            'admin_count'      => (int) $adminQuery->count(),
            'role_count'       => (int) Db::name('role')->count(),
            'tenant_count'     => (int) Db::name('tenant')->where('status', 1)->count(),
            'user_count'       => $userTotal,
            'attachment_count' => $uploadTotal,
            'log_count'        => (int) $logQuery->count(),
            // KPI
            'user_today_reg'   => $userTodayReg,
            'user_today_login' => $userTodayLogin,
            'user_three_days'  => $userThreeDays,
            'user_seven_days'  => $userSevenDays,
            'user_seven_active' => $userSevenActive,
            'user_month_active' => $userMonthActive,
            // 趋势数据
            'register_trend'   => $registerTrend,
            // 资源统计
            'db_tables'        => $dbTables,
            'db_size'          => $dbSize,
            'upload_size'      => $uploadSize,
            'image_count'      => $imageCount,
            'image_size'       => $imageSize,
            // 租户统计（仅平台超管）
            'tenant_stats'     => $tenantStats,
        ]);
        
        // 与 report FastAdmin 一致：获取侧边栏菜单
        $auth = new Auth();
        list($menulist, $navlist, $fixedmenu, $referermenu) = $auth->getSidebar([
            'dashboard' => 'hot',
            'addon'     => ['new', 'red', 'badge'],
            'auth/rule' => 'Menu',
        ], 'dashboard');
        
        View::assign('menulist', $menulist);
        View::assign('navlist', $navlist);
        View::assign('fixedmenu', $fixedmenu);
        View::assign('referermenu', $referermenu);
        
        return $this->fetchWithLayout('index/index');
    }

    /**
     * 菜单树（按权限过滤）
     */
    public function menu(): Response
    {
        $admin = Session::get('admin_info');
        if (!$admin) {
            return $this->error('未登录');
        }
        $adminId = (int) $admin['id'];

        $auth = new Auth();
        $userRule = $auth->getRuleIds($adminId);
        
        // 读取所有菜单项
        $model = new AuthRuleModel();
        $ruleList = $model->where('status', 1)->where('ismenu', 1)->order('sort', 'desc')->order('id')->select()->toArray();
        
        // 过滤菜单项，只保留用户有权限的
        foreach ($ruleList as $k => &$v) {
            if (!in_array(strtolower($v['name'] ?? ''), $userRule)) {
                unset($ruleList[$k]);
                continue;
            }
            $v['icon'] = ($v['icon'] ?? '') . ' fa-fw';
            // URL生成：根据name生成正确的URL
            if (!isset($v['url']) || !$v['url']) {
                $name = $v['name'] ?? '';
                // 如果name已经以admin/开头，只加前导斜杠（admin/role/index -> /admin/role/index）
                if (str_starts_with($name, 'admin/')) {
                    $v['url'] = '/' . $name;
                } else {
                    // 否则加上/admin/前缀（mes/order -> /admin/mes/order）
                    $v['url'] = '/admin/' . $name;
                }
            }
            $v['title'] = $v['title'] ?? '';
            $v['menuclass'] = '';
            $v['menutabs'] = 'addtabs="' . ($v['id'] ?? '') . '"';
        }
        
        // 处理父菜单
        $pidArr = array_unique(array_filter(array_column($ruleList, 'pid')));
        $lastArr = array_unique(array_filter(array_column($ruleList, 'pid')));
        $pidDiffArr = array_diff($pidArr, $lastArr);
        
        // 删除所有子菜单都被删除的父菜单
        foreach ($ruleList as $index => $item) {
            if (in_array($item['id'], $pidDiffArr)) {
                unset($ruleList[$index]);
            }
        }
        
        // 重新构建树形结构
        $tree = $this->buildMenuTree(array_values($ruleList), 0);
        
        return $this->success('', $tree);
    }

    /** 构建菜单树 */
    protected function buildMenuTree(array $list, int $pid): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ((int) ($item['pid'] ?? 0) === $pid) {
                $children = $this->buildMenuTree($list, (int) ($item['id'] ?? 0));
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
