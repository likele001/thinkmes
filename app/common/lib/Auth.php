<?php
declare(strict_types=1);

namespace app\common\lib;

use think\facade\Cache;
use think\facade\Db;
use think\facade\Session;

/**
 * RBAC 权限校验（对标 FastAdmin think-auth）
 */
class Auth
{
    protected string $cachePrefix = 'auth_rules:';
    protected int $cacheTtl = 3600;

    public function __construct()
    {
        $config = config('auth');
        if ($config) {
            $this->cachePrefix = $config['cache_prefix'] ?? $this->cachePrefix;
            $this->cacheTtl = (int) ($config['cache_ttl'] ?? $this->cacheTtl);
        }
    }

    /**
     * 检查当前管理员是否有某节点权限
     * @param string $node 节点标识，如 admin/index/index
     * @param int $adminId 管理员ID
     */
    public function check(string $node, int $adminId): bool
    {
        $rules = $this->getRuleIds($adminId);
        if (empty($rules)) {
            return false;
        }
        // 超级管理员或规则含 * 表示全部
        if (in_array('*', $rules, true)) {
            return true;
        }
        $node = strtolower(trim($node));
        foreach ($rules as $ruleName) {
            $ruleNameLower = strtolower(trim((string) $ruleName));
            if ($ruleNameLower === $node) {
                return true;
            }
            // 支持父级：如 admin/auth_rule 可匹配 admin/auth_rule/index
            if (str_starts_with($node, $ruleNameLower . '/')) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取管理员拥有的规则 name 列表（含父级继承）
     */
    public function getRuleIds(int $adminId): array
    {
        $cacheKey = $this->cachePrefix . $adminId;
        $rules = Cache::get($cacheKey);
        if ($rules !== null && is_array($rules)) {
            return $rules;
        }
        $admin = Db::name('admin')->where('id', $adminId)->where('status', 1)->find();
        if (!$admin) {
            return [];
        }
        $roleIds = array_filter(array_map('intval', explode(',', (string) ($admin['role_ids'] ?? ''))));
        if (empty($roleIds)) {
            Cache::set($cacheKey, [], $this->cacheTtl);
            return [];
        }
        $roleRules = Db::name('role')->whereIn('id', $roleIds)->where('status', 1)->column('rules');
        $ruleIds = [];
        foreach ($roleRules as $rulesStr) {
            if ($rulesStr === '*' || $rulesStr === null) {
                Cache::set($cacheKey, ['*'], $this->cacheTtl);
                return ['*'];
            }
            foreach (array_filter(explode(',', (string) $rulesStr)) as $id) {
                $ruleIds[] = (int) $id;
            }
        }
        $ruleIds = array_unique($ruleIds);
        if (empty($ruleIds)) {
            Cache::set($cacheKey, [], $this->cacheTtl);
            return [];
        }
        $names = Db::name('auth_rule')->whereIn('id', $ruleIds)->where('status', 1)->column('name');
        $allNames = $this->expandRuleNames($ruleIds);
        $result = array_values(array_unique(array_merge($names, $allNames)));
        Cache::set($cacheKey, $result, $this->cacheTtl);
        return $result;
    }

    /**
     * 根据规则ID展开所有父级规则 name
     */
    protected function expandRuleNames(array $ruleIds): array
    {
        if (empty($ruleIds)) {
            return [];
        }
        $rules = Db::name('auth_rule')->whereIn('id', $ruleIds)->where('status', 1)->select()->toArray();
        $names = array_column($rules, 'name');
        $pids = array_unique(array_column($rules, 'pid'));
        $pids = array_filter($pids, fn($v) => (int) $v > 0);
        if (empty($pids)) {
            return $names;
        }
        $parentRules = Db::name('auth_rule')->whereIn('id', $pids)->where('status', 1)->column('name');
        $parentIds = Db::name('auth_rule')->whereIn('id', $pids)->where('status', 1)->column('id');
        $more = $this->expandRuleNames($parentIds);
        return array_values(array_unique(array_merge($names, $parentRules, $more)));
    }

    /**
     * 清除管理员权限缓存
     */
    public function clearCache(int $adminId): void
    {
        Cache::delete($this->cachePrefix . $adminId);
    }

    /**
     * 清除所有权限缓存（角色/规则变更时调用）
     */
    public function clearAllCache(): void
    {
        try {
            Cache::clear();
        } catch (\Throwable $e) {
            // 部分驱动无 clear，忽略
        }
    }

    /**
     * 数据权限：获取当前管理员可管理的主管员 ID 列表
     * @param int $adminId 当前管理员ID
     * @param int $tenantId 当前租户ID
     * @return null 表示不限制（全部）；array 表示仅允许这些 id
     */
    public function getAdminDataScopeIds(int $adminId, int $tenantId): ?array
    {
        $superId = (int) (config('auth.super_admin_id') ?? 1);
        if ($adminId === $superId || $tenantId === 0) {
            return null; // 超管不限制
        }
        $row = Db::name('admin')->where('id', $adminId)->where('tenant_id', $tenantId)->find();
        if (!$row) {
            return [];
        }
        $scope = (int) ($row['data_scope'] ?? 1);
        if ($scope === 3) {
            return null; // 全部
        }
        if ($scope === 1) {
            return [$adminId];
        }
        // data_scope = 2：自己 + 所有子级（递归）
        return $this->getSelfAndDescendantAdminIds($adminId);
    }

    /**
     * 递归获取自己及所有下级管理员 ID
     */
    protected function getSelfAndDescendantAdminIds(int $adminId): array
    {
        $ids = [$adminId];
        $children = Db::name('admin')->where('pid', $adminId)->column('id');
        foreach ($children as $cid) {
            $ids = array_merge($ids, $this->getSelfAndDescendantAdminIds((int) $cid));
        }
        return array_values(array_unique($ids));
    }

    /**
     * 获取左侧和顶部菜单栏
     *
     * @param array  $params    URL对应的badge数据
     * @param string $fixedPage 默认页
     * @return array
     */
    public function getSidebar($params = [], $fixedPage = 'dashboard')
    {
        $colorArr = ['red', 'green', 'yellow', 'blue', 'teal', 'orange', 'purple'];
        $colorNums = count($colorArr);
        $badgeList = [];
        
        // 生成菜单的badge
        foreach ($params as $k => $v) {
            $url = $k;
            if (is_array($v)) {
                $nums = $v[0] ?? 0;
                $color = $v[1] ?? $colorArr[(is_numeric($nums) ? $nums : strlen($nums)) % $colorNums];
                $class = $v[2] ?? 'label';
            } else {
                $nums = $v;
                $color = $colorArr[(is_numeric($nums) ? $nums : strlen($nums)) % $colorNums];
                $class = 'label';
            }
            //必须nums大于0才显示
            if ($nums) {
                $badgeList[$url] = '<small class="' . $class . ' pull-right bg-' . $color . '">' . $nums . '</small>';
            }
        }

        // 读取管理员当前拥有的权限节点
        $admin = Session::get('admin_info');
        $userRule = $this->getRuleList((int) $admin['id']);
        $selected = $referer = [];
        $refererUrl = Session::get('referer');
        
        // 读取所有菜单项
        $ruleList = Db::name('auth_rule')->where('status', 1)->where('ismenu', 1)->order('sort', 'desc')->order('id')->select()->toArray();
        
        // 过滤菜单项，只保留用户有权限的
        foreach ($ruleList as $k => &$v) {
            if (!in_array(strtolower($v['name'] ?? ''), $userRule)) {
                unset($ruleList[$k]);
                continue;
            }
            $v['icon'] = ($v['icon'] ?? '') . ' fa-fw';
            $v['url'] = isset($v['url']) && $v['url'] ? $v['url'] : '/admin/' . ($v['name'] ?? '');
            $v['badge'] = $badgeList[$v['name']] ?? '';
            $v['title'] = $v['title'] ?? '';
            $v['menuclass'] = '';
            $v['menutabs'] = 'addtabs="' . ($v['id'] ?? '') . '"';
            $selected = $v['name'] == $fixedPage ? $v : $selected;
            $referer = $v['url'] == $refererUrl ? $v : $referer;
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
        
        if ($selected == $referer) {
            $referer = [];
        }

        $select_id = $referer ? $referer['id'] : ($selected ? $selected['id'] : 0);
        $menu = $nav = '';
        
        // 构造菜单数据
        $tree = [];
        foreach ($ruleList as $item) {
            if ((int) ($item['pid'] ?? 0) === 0) {
                $children = [];
                foreach ($ruleList as $child) {
                    if ((int) ($child['pid'] ?? 0) === (int) ($item['id'] ?? 0)) {
                        $children[] = $child;
                    }
                }
                $item['children'] = $children;
                $tree[] = $item;
            }
        }
        
        // 生成菜单HTML
        foreach ($tree as $item) {
            $childList = '';
            if (!empty($item['children'])) {
                $childList .= '<ul class="treeview-menu">';
                foreach ($item['children'] as $child) {
                    $childList .= '<li><a href="' . $child['url'] . '" ' . $child['menutabs'] . ' class="' . $child['menuclass'] . '"><i class="' . $child['icon'] . '"></i> <span>' . $child['title'] . '</span> ' . $child['badge'] . '</a></li>';
                }
                $childList .= '</ul>';
            }
            $url = !empty($item['children']) ? 'javascript:;' : $item['url'];
            $menu .= '<li class="treeview"><a href="' . $url . '"><i class="' . $item['icon'] . '"></i> <span>' . $item['title'] . '</span> ' . $item['badge'] . '<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>' . $childList . '</li>';
        }
        
        // 生成导航HTML
        if ($selected) {
            $nav .= '<li role="presentation" id="tab_' . $selected['id'] . '" class="' . ($referer ? '' : 'active') . '"><a href="#con_' . $selected['id'] . '" node-id="' . $selected['id'] . '" aria-controls="' . $selected['id'] . '" role="tab" data-toggle="tab"><i class="' . $selected['icon'] . ' fa-fw"></i> <span>' . $selected['title'] . '</span> </a></li>';
        }
        if ($referer) {
            $nav .= '<li role="presentation" id="tab_' . $referer['id'] . '" class="active"><a href="#con_' . $referer['id'] . '" node-id="' . $referer['id'] . '" aria-controls="' . $referer['id'] . '" role="tab" data-toggle="tab"><i class="' . $referer['icon'] . ' fa-fw"></i> <span>' . $referer['title'] . '</span> </a> <i class="close-tab fa fa-remove"></i></li>';
        }

        return [$menu, $nav, $selected, $referer];
    }

    /**
     * 获取规则列表
     */
    public function getRuleList(int $adminId): array
    {
        return $this->getRuleIds($adminId);
    }
}
