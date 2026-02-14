<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\lib\Auth;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;

/**
 * 后台基类：注入 config（controllername/actionname/jsname）、admin、site，供布局与 RequireJS 使用
 */
abstract class Backend extends BaseController
{
    /**
     * 权限控制类
     * @var Auth
     */
    protected $auth = null;

    protected function initialize(): void
    {
        parent::initialize();
        
        $this->auth = new Auth();
        
        // 定义是否Dialog请求
        if (!defined('IS_DIALOG')) {
            define('IS_DIALOG', (bool)$this->request->get('dialog'));
        }
        
        // 定义是否AJAX请求
        if (!defined('IS_AJAX')) {
            define('IS_AJAX', $this->request->isAjax());
        }
        
        $this->assignBackendConfig();
    }

    /**
     * 为视图注入 config、admin、site（仿 FastAdmin）
     */
    protected function assignBackendConfig(): void
    {
        $controller = $this->request->controller();
        $action     = $this->request->action();
        
        // 获取路径信息，用于检测子命名空间（如 mes\Process）
        $pathinfo = trim($this->request->pathinfo(), '/');
        $pathParts = $pathinfo ? explode('/', $pathinfo) : [];
        
        // 如果控制器名包含点号（如 mes.Process），提取真正的控制器名
        // 例如：mes.Process -> Process
        if (strpos($controller, '.') !== false) {
            $controllerParts = explode('.', $controller);
            $controller = end($controllerParts); // 取最后一部分
        }
        
        // controllername: 驼峰转小写路径，如 Admin->admin, AuthRule->auth_rule
        $controllername = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $controller));
        
        // 如果路径中包含mes等子目录，需要添加到controllername前面
        // 例如：mes/process/index -> mes/process
        if (count($pathParts) >= 2 && $pathParts[0] === 'mes') {
            $controllername = 'mes/' . $controllername;
        }
        
        $actionname     = strtolower($action);

        // 后台根 URL：若 root() 已含 /admin 则不再追加，避免出现 /admin/admin/admin/...
        $siteRoot = rtrim($this->request->domain() . $this->request->root(), '/');
        $adminBase = (str_ends_with(strtolower($siteRoot), '/admin')) ? $siteRoot : ($siteRoot . '/admin');
        // 静态资源始终从站点根取（/assets/...），避免 /admin/assets/... 导致 404
        $config = [
            'site'           => [
                'name'     => 'ThinkMes',
                'cdnurl'   => '',
                'version'  => (string) ($this->app->config->get('app.version') ?: time()),
            ],
            'modulename'     => 'admin',
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'jsname'         => 'backend/' . $controllername,
            'moduleurl'      => $adminBase,
            // 表格/菜单：adminBase + 控制器/index，保证只有两段 admin（应用+控制器）
            'table_index_url' => $adminBase . '/' . $controllername . '/index',
            'menu_url'        => $adminBase . '/index/menu',
        ];

        View::assign('config', $config);
        View::assign('admin', Session::get('admin_info'));
        View::assign('site', $config['site']);
    }

    /** 当前请求的租户 ID（0=平台），由 TenantResolve 中间件设置 */
    protected function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    /**
     * 数据权限：当前管理员可管理的主管员 ID 列表。null=不限制，array=仅允许这些 id
     */
    protected function getDataScopeAdminIds(): ?array
    {
        $admin = Session::get('admin_info');
        if (!$admin || !isset($admin['id'])) {
            return [];
        }
        $auth = new Auth();
        return $auth->getAdminDataScopeIds((int) $admin['id'], $this->getTenantId());
    }

    /** 判断表是否存在 */
    protected function hasTable(string $tableName): bool
    {
        try {
            $prefix = config('database.connections.mysql.prefix', 'fa_');
            $full = $prefix . $tableName;
            $tables = Db::query('SHOW TABLES LIKE ?', [$full]);
            return !empty($tables);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** 判断表是否有某列（用于兼容未执行租户迁移的旧库） */
    protected function hasTableColumn(string $tableName, string $column): bool
    {
        try {
            $prefix = config('database.connections.mysql.prefix', 'fa_');
            $full = $prefix . $tableName;
            $fields = Db::getTableFields($full);
            return in_array($column, $fields, true);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 检查目标管理员 ID 是否在当前数据权限内
     */
    protected function canManageAdminId(int $targetId): bool
    {
        $ids = $this->getDataScopeAdminIds();
        if ($ids === null) {
            return true; // 不限制
        }
        return in_array($targetId, $ids, true);
    }

    /**
     * 检查租户资源限制（管理员数、用户数等）
     * @param string $resourceType 资源类型：admin/user
     * @return array ['allowed' => bool, 'current' => int, 'max' => int, 'msg' => string]
     */
    protected function checkResourceLimit(string $resourceType): array
    {
        $tenantId = $this->getTenantId();
        if ($tenantId === 0) {
            return ['allowed' => true, 'current' => 0, 'max' => 0, 'msg' => ''];
        }

        $package = $this->request->package ?? null;
        if (!$package) {
            return ['allowed' => false, 'current' => 0, 'max' => 0, 'msg' => '套餐信息不存在'];
        }

        $current = 0;
        $max = 0;
        $resourceName = '';

        if ($resourceType === 'admin') {
            $current = (int) ($this->request->adminCount ?? 0);
            $max = (int) $package->max_admin;
            $resourceName = '管理员';
        } elseif ($resourceType === 'user') {
            $current = (int) ($this->request->userCount ?? 0);
            $max = (int) $package->max_user;
            $resourceName = '用户';
        } else {
            return ['allowed' => false, 'current' => 0, 'max' => 0, 'msg' => '未知的资源类型'];
        }

        $allowed = ($max === 0 || $current < $max);
        $msg = $allowed ? '' : "已达到最大{$resourceName}数限制（{$max}人），请升级套餐";

        return [
            'allowed' => $allowed,
            'current' => $current,
            'max' => $max,
            'msg' => $msg
        ];
    }

    /**
     * 检查租户是否有某个功能的权限
     * @param string $featureCode 功能代码
     * @return bool
     */
    protected function checkFeaturePermission(string $featureCode): bool
    {
        $tenantId = $this->getTenantId();
        if ($tenantId === 0) {
            return true; // 平台超管拥有所有功能
        }

        $features = $this->request->packageFeatures ?? [];
        
        // 如果套餐没有配置功能列表，则允许所有功能
        if (empty($features)) {
            return true;
        }

        // 检查功能代码是否在允许列表中
        return in_array($featureCode, $features, true);
    }

    /**
     * 使用公共布局渲染（仿 FastAdmin：meta + 侧栏 + __CONTENT__ + script + 按 jsname 加载页面 JS）
     * 如果页面在 iframe 中加载，使用简化的 iframe 布局
     */
    protected function fetchWithLayout(string $template): string
    {
        $content = View::fetch($template);
        
        // 检测是否在 iframe 中加载
        // 重要：只有当页面确实在 iframe 中加载时才使用简化布局
        // 如果用户直接访问带 iframe=1 参数的 URL，应该使用完整布局（因为这是主页面）
        
        // PHP 无法直接检测是否在 iframe 中，所以我们：
        // 1. 默认使用完整布局
        // 2. 在 iframe.html 布局中添加 JavaScript 检测，如果不在 iframe 中会自动跳转
        // 3. 只有当 Referer 明确显示是从 iframe 系统跳转过来时，才使用简化布局
        
        $isInIframe = false;
        
        // 检查 Referer：如果 Referer 是同一个域名的主页面（包含 iframe-mode），说明是在 iframe 中加载
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
            $currentHost = $this->request->host(true);
            
            // 如果 Referer 是同一个域名
            if (strpos($referer, $currentHost) !== false) {
                // 检查 Referer 是否包含 iframe 相关的标识
                // 如果 Referer 路径包含 /admin/ 且不是当前页面本身，可能是在 iframe 中
                $refererPath = parse_url($referer, PHP_URL_PATH);
                $currentPath = $this->request->pathinfo();
                
                // 如果 Referer 路径与当前路径不同，且 Referer 是主页面路径（如 /admin/index/index）
                // 说明可能是从主页面通过 iframe 加载的
                if ($refererPath !== '/' . trim($currentPath, '/')) {
                    // 进一步检查：Referer 的查询参数
                    $refererQuery = parse_url($referer, PHP_URL_QUERY);
                    // 如果 Referer 没有 iframe=1 参数，说明是从主页面跳转过来的，很可能是在 iframe 中
                    if (!$refererQuery || strpos($refererQuery, 'iframe=1') === false) {
                        // 检查当前 URL 是否有 iframe=1 参数
                        $refParam = strtolower((string) $this->request->get('ref'));
                        if (in_array($this->request->get('iframe'), ['1'], true) || in_array($this->request->get('addtabs'), ['1'], true) || $refParam === 'addtabs') {
                            $isInIframe = true;
                        }
                    }
                }
            }
        }
        
        // FastAdmin 标准：iframe/addtabs 请求使用 iframe 布局（完整 html+head+body+CSS）
        $isAddtabs = ($this->request->get('addtabs') === '1') || (strtolower((string)$this->request->get('ref')) === 'addtabs');
        if ($this->request->get('iframe') === '1' || $isAddtabs) {
            View::assign('__CONTENT__', $content);
            return View::fetch('layout/iframe');
        }

        // 如果在 iframe 中（通过 Referer 检测），使用简化的 iframe 布局
        if ($isInIframe) {
            View::assign('__CONTENT__', $content);
            return View::fetch('layout/iframe');
        }
        
        // 正常页面加载，使用完整布局（与 report FastAdmin 一致：主框架只有 tab+iframe，无内联内容）
        View::assign('__CONTENT__', $content);
        if (!View::get('fixedmenu')) {
            $indexUrl = (string) url('index/index');
            View::assign('fixedmenu', [
                'id'         => 'index',
                'url'        => $indexUrl,
                'iframe_src' => $indexUrl . (strpos($indexUrl, '?') !== false ? '&' : '?') . 'addtabs=1',
                'title'      => '首页',
            ]);
        }
        if (!View::get('referermenu')) {
            // FastAdmin/report 行为：直接访问任意页面时，主框架也会打开“当前页”作为激活 tab（iframe 加载 addtabs=1）
            $pathinfo = trim((string) $this->request->pathinfo(), '/'); // e.g. tenant_package/index
            if ($pathinfo !== '' && $pathinfo !== 'index/index') {
                $currentUrl = (string) $this->request->url(); // keep querystring if any, relative URL is fine for iframe
                $iframeSrc = $currentUrl . (strpos($currentUrl, '?') !== false ? '&' : '?') . 'addtabs=1';
                $id = str_replace(['/', '-', '.'], '_', $pathinfo);

                // 尝试从权限规则表里取标题和 id（与菜单 addtabs id 保持一致）
                $title = '';
                $ruleId = null;
                try {
                    $dotName = str_replace('/', '.', $pathinfo);
                    $candidates = array_values(array_unique([
                        $pathinfo,
                        $dotName,
                        'admin/' . $pathinfo,
                        'admin.' . $dotName,
                    ]));
                    $rule = (new \app\admin\model\AuthRuleModel())
                        ->whereIn('name', $candidates)
                        ->find();
                    if ($rule) {
                        $title = (string) ($rule['title'] ?? '');
                        $ruleId = (int) ($rule['id'] ?? 0);
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
                if ($title === '') {
                    $title = $pathinfo;
                }
                
                // 与菜单 addtabs id 生成逻辑一致：有 id 用 m{id}，否则用 name 转换
                if ($ruleId > 0) {
                    $id = 'm' . $ruleId;
                } else {
                    // 与 backend-loader.js 一致：name.replace(/[\/\.]/g, '_').replace(/^_+|_+$/g, '')
                    $id = preg_replace('/[\/\.]/', '_', $pathinfo);
                    $id = preg_replace('/^_+|_+$/', '', $id);
                    if ($id === '') {
                        $id = 'tab_' . substr(md5($pathinfo), 0, 6);
                    }
                }

                View::assign('referermenu', [
                    'id'         => $id,
                    'url'        => $currentUrl,
                    'iframe_src' => $iframeSrc,
                    'title'      => $title,
                ]);
            } else {
                View::assign('referermenu', null);
            }
        }
        return View::fetch('layout/default');
    }
}
