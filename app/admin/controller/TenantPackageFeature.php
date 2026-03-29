<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\TenantPackageFeatureModel;
use app\admin\model\TenantPackageModel;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

/**
 * 套餐功能管理（仅平台超管 tenant_id=0 可访问）
 */
class TenantPackageFeature extends Backend
{
    public function index(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理套餐功能');
        }
        $packageId = (int) $this->request->get('package_id', 0);
        if ($packageId <= 0) {
            return $this->error('请选择套餐');
        }
        
        $package = TenantPackageModel::find($packageId);
        if (!$package) {
            return $this->error('套餐不存在');
        }
        
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('package', $package->toArray());
            View::assign('title', __("title") . ' - ' . $package->name);
            return $this->fetchWithLayout('tenant_package_feature/index');
        }
        
        $list = TenantPackageFeatureModel::where('package_id', $packageId)
            ->order('id')
            ->select()
            ->toArray();
        
        return $this->success('', ['total' => count($list), 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理套餐功能');
        }
        if ($this->request->isPost()) {
            return $this->addPost();
        }
        $packageId = (int) $this->request->get('package_id', 0);
        if ($packageId <= 0) {
            return $this->error('请选择套餐');
        }
        $package = TenantPackageModel::find($packageId);
        if (!$package) {
            return $this->error('套餐不存在');
        }
        
        // 预定义的功能列表（可根据实际业务调整）
        $allFeatures = $this->getAllFeatures();
        $existingFeatures = TenantPackageFeatureModel::where('package_id', $packageId)->column('feature_code');
        // 构建树形结构：一级菜单 => 二级菜单列表
        $featureTree = $this->buildFeatureTree($allFeatures, $existingFeatures);

        View::assign('package', $package->toArray());
        View::assign('featureTree', $featureTree);
        View::assign('existingFeatures', $existingFeatures);
        View::assign('title', __("add_feature") . ' - ' . $package->name);
        return $this->fetchWithLayout('tenant_package_feature/add');
    }

    public function addPost(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理套餐功能');
        }
        $packageId = (int) $this->request->post('package_id', 0);
        $featureCodes = $this->request->post('feature_codes', []);
        
        if ($packageId <= 0) {
            return $this->error('请选择套餐');
        }
        if (!is_array($featureCodes) || empty($featureCodes)) {
            return $this->error('请选择至少一个功能');
        }
        
        $package = TenantPackageModel::find($packageId);
        if (!$package) {
            return $this->error('套餐不存在');
        }
        
        $allFeatures = $this->getAllFeatures();
        $now = time();
        $added = 0;
        
        foreach ($featureCodes as $code) {
            $code = is_string($code) ? trim($code) : '';
            if ($code === '') {
                continue;
            }
            // 兼容：部分环境会把 POST 里的 admin/tenant/miniapp 转成 admin_tenant_miniapp
            if (!isset($allFeatures[$code])) {
                $codeWithSlash = str_replace('_', '/', $code);
                if (isset($allFeatures[$codeWithSlash])) {
                    $code = $codeWithSlash;
                } else {
                    continue;
                }
            }
            $featureName = $allFeatures[$code];
            // 检查是否已存在
            $exists = TenantPackageFeatureModel::where('package_id', $packageId)
                ->where('feature_code', $code)
                ->find();
            if (!$exists) {
                TenantPackageFeatureModel::create([
                    'package_id' => $packageId,
                    'feature_code' => $code,
                    'feature_name' => $featureName,
                    'create_time' => $now,
                ]);
                $added++;
            }
        }
        
        $this->log('add', '为套餐ID=' . $packageId . '添加' . $added . '个功能');
        $this->ensureDefaultRoleForPackage($packageId);
        return $this->success('添加成功', ['added' => $added, 'received' => $featureCodes]);
    }

    protected function ensureDefaultRoleForPackage(int $packageId): int
    {
        if ($packageId <= 0) {
            return 0;
        }
        try {
            $pkg = \app\admin\model\TenantPackageModel::find($packageId);
            if (!$pkg) {
                return 0;
            }
            $features = \think\facade\Db::name('tenant_package_feature')
                ->where('package_id', $packageId)
                ->where('is_enabled', 1)
                ->column('feature_code');
            $authRuleIds = [];
            if (!empty($features)) {
                foreach ($features as $code) {
                    $codeSlash = str_replace('.', '/', $code);
                    $idsExact = \think\facade\Db::name('auth_rule')->where('status', 1)->where('name', $codeSlash)->column('id');
                    $idsChildren = \think\facade\Db::name('auth_rule')->where('status', 1)->where('name', 'like', $codeSlash . '/%')->column('id');
                    $authRuleIds = array_merge($authRuleIds, $idsExact, $idsChildren);
                    // 有分工权限时同时带上分工二维码（不单独占套餐功能，与分工一体）
                    if ($codeSlash === 'mes/allocation') {
                        $qrExact = \think\facade\Db::name('auth_rule')->where('status', 1)->where('name', 'mes/allocation_qrcode')->column('id');
                        $qrChildren = \think\facade\Db::name('auth_rule')->where('status', 1)->where('name', 'like', 'mes/allocation_qrcode/%')->column('id');
                        $authRuleIds = array_merge($authRuleIds, $qrExact, $qrChildren);
                    }
                }
            }
            $baseIds = \think\facade\Db::name('auth_rule')->where('status', 1)->whereIn('name', ['dashboard','admin/index','admin/index/index'])->column('id');
            $authRuleIds = array_values(array_unique(array_merge($authRuleIds, $baseIds, [1])));
            $roleName = '套餐:' . ($pkg['name'] ?? ('#' . $pkg['id'])) . '默认角色';
            $exist = \app\admin\model\RoleModel::where('name', $roleName)->find();
            $rulesStr = implode(',', array_map('strval', $authRuleIds));
            if ($exist) {
                $exist->rules = $rulesStr;
                $exist->status = 1;
                $exist->update_time = time();
                $exist->save();
                return (int) $exist->id;
            } else {
                $role = \app\admin\model\RoleModel::create([
                    'name' => $roleName,
                    'rules' => $rulesStr,
                    'status' => 1,
                    'create_time' => time(),
                    'update_time' => time(),
                ]);
                return (int) ($role->id ?? 0);
            }
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function multi(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理套餐功能');
        }
        $ids = $this->request->post('ids');
        $action = $this->request->post('action');
        if (!$ids || !$action) {
            return $this->error('参数错误');
        }
        $ids = array_map('intval', is_array($ids) ? $ids : explode(',', $ids));

        if ($action === 'enable') {
            TenantPackageFeatureModel::whereIn('id', $ids)->update(['is_enabled' => 1]);
            $this->log('enable', '批量启用套餐功能:' . implode(',', $ids));
        } elseif ($action === 'disable') {
            TenantPackageFeatureModel::whereIn('id', $ids)->update(['is_enabled' => 0]);
            $this->log('disable', '批量禁用套餐功能:' . implode(',', $ids));
        } elseif ($action === 'delete') {
            $rows = TenantPackageFeatureModel::whereIn('id', $ids)->select();
            $packageIds = array_unique(array_column($rows->toArray(), 'package_id'));
            TenantPackageFeatureModel::whereIn('id', $ids)->delete();
            $this->log('del', '批量删除套餐功能:' . implode(',', $ids));
            // 更新所有受影响的套餐角色
            foreach ($packageIds as $packageId) {
                $this->ensureDefaultRoleForPackage((int) $packageId);
            }
            return $this->success('操作成功');
        }

        // 获取受影响的套餐ID并更新角色
        $features = TenantPackageFeatureModel::whereIn('id', $ids)->select();
        $packageIds = array_unique(array_column($features->toArray(), 'package_id'));
        foreach ($packageIds as $packageId) {
            $this->ensureDefaultRoleForPackage((int) $packageId);
        }

        return $this->success('操作成功');
    }

    public function del(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理套餐功能');
        }
        $id = (int) $this->request->post('id');
        $row = TenantPackageFeatureModel::find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $packageId = $row->package_id;
        $row->delete();
        $this->log('del', '删除套餐功能:id=' . $id);
        // 同步更新对应角色的权限
        $this->ensureDefaultRoleForPackage($packageId);
        return $this->success('删除成功');
    }

    /**
     * 获取所有可用的功能列表（完整版）
     */
    protected function getAllFeatures(): array
    {
        return [
            // 核心模块
            'dashboard' => '控制台',
            'admin' => '系统管理',

            // MES 生产管理
            'mes' => 'MES生产管理',
            'mes/order' => 'MES订单管理',
            'mes/product' => 'MES产品管理',
            'mes/product_model' => 'MES产品型号',
            'mes/customer' => 'MES客户管理',
            'mes/customer_product' => 'MES客户产品',
            'mes/supplier' => 'MES供应商管理',
            'mes/material' => 'MES物料管理',
            'mes/material_category' => 'MES物料分类',
            'mes/warehouse' => 'MES仓库管理',
            'mes/bom' => 'MES BOM配方',
            'mes/process' => 'MES工序管理',
            'mes/process_route' => 'MES工艺路线',
            'mes/process_price' => 'MES工序价格',
            'mes/allocation' => 'MES生产分工',
            'mes/allocation_qrcode' => 'MES分工二维码',
            'mes/report' => 'MES生产报工',
            'mes/production_plan' => 'MES生产计划',
            'mes/purchase' => 'MES采购管理',
            'mes/quality' => 'MES质量检验',
            'mes/stock' => 'MES库存管理',
            'mes/wage' => 'MES工资管理',
            'mes/trace_code' => 'MES追溯码',
            'mes/after_sales' => 'MES售后管理',
            'mes/shipment' => 'MES发货管理',
            'mes/bi' => 'MES BI报表',
            'mes/mrp' => 'MES MRP运算',

            // 餐饮管理
            'restaurant' => '餐饮管理',
            'restaurant/store' => '餐饮门店管理',
            'restaurant/area' => '餐饮区域管理',
            'restaurant/table' => '餐饮桌台管理',
            'restaurant/category' => '餐饮菜品分类',
            'restaurant/item' => '餐饮菜品管理',
            'restaurant/combo' => '餐饮套餐管理',
            'restaurant/option_group' => '餐饮规格组',
            'restaurant/option' => '餐饮规格项',
            'restaurant/order' => '餐饮订单管理',
            'restaurant/kds' => '餐饮后厨显示',
            'restaurant/ai' => '餐饮AI助手',
            'restaurant/report' => '餐饮报表统计',
            'restaurant/wxa_setting' => '餐饮小程序设置',

            // CRM 客户关系管理
            'crm' => 'CRM客户关系管理',
            'crm/customer' => 'CRM客户管理',
            'crm/customer_tag' => 'CRM客户标签',
            'crm/contact' => 'CRM联系人',
            'crm/opportunity' => 'CRM销售机会',
            'crm/contract' => 'CRM合同管理',
            'crm/follow' => 'CRM跟进记录',
            'crm/payment' => 'CRM回款管理',
            'crm/product' => 'CRM产品管理',
            'crm/sales_order' => 'CRM销售订单',
            'crm/report' => 'CRM报表',

            // 自媒体工作流
            'wemedia' => '自媒体工作流',
            'wemedia/topic' => '话题管理',
            'wemedia/copy' => '文案管理',
            'wemedia/material' => '素材管理',
            'wemedia/video' => '视频管理',
            'wemedia/schedule' => '发布计划',
            'wemedia/report' => '数据报表',
            'wemedia/compliance' => '合规检查',

            // 支付管理
            'payment' => '支付管理',
            'payment/config' => '支付配置',
            'payment/order' => '支付订单',
            'payment/callback_log' => '回调日志',
            'payment/stats' => '支付统计',

            // 财务管理
            'finance' => '财务管理',
            'finance/receivable' => '应收管理',
            'finance/payable' => '应付管理',
            'finance/invoice' => '发票管理',
            'finance/reconciliation' => '财务对账',

            // 人力资源
            'hr' => '人力资源',
            'hr/employee' => '员工档案',
            'hr/attendance' => '考勤管理',
            'hr/salary' => '薪资管理',
            'hr/performance' => '绩效考核',

            // 设备管理
            'equipment' => '设备管理',
            'equipment/device' => '设备台账',
            'equipment/maintenance' => '设备保养',
            'equipment/repair' => '设备维修',

            // AI 功能
            'ai' => 'AI智能助手（附加收费）',
            'ai/config' => 'AI配置',
            'ai/voice_report' => 'AI语音报工',
            'ai/anomaly' => 'AI异常检测',
            'ai/qa' => 'AI智能问答',
            'ai/daily_report' => 'AI经营日报',
            'ai/crm_follow' => 'AI跟单建议',
            'ai/cockpit' => 'AI驾驶舱',

            // 扩展功能
            'custom_field' => '自定义字段',
            'workflow' => '工作流',
            'addon' => '插件管理',
            'extension' => '扩展模块',
            'market' => '插件市场',

            // 数据功能
            'api' => 'API接口访问',
            'export' => '数据导出',
            'import' => '数据导入',
            'backup' => '数据备份',
            'notification' => '消息通知',
        ];
    }

    /**
     * 构建功能树形结构
     */
    protected function buildFeatureTree(array $allFeatures, array $existingFeatures): array
    {
        $tree = [];
        $childrenMap = [];

        // 分离一级和二级功能
        foreach ($allFeatures as $code => $name) {
            if (strpos($code, '/') === false) {
                // 一级功能
                $tree[$code] = [
                    'code' => $code,
                    'name' => $name,
                    'children' => [],
                    'checked' => in_array($code, $existingFeatures),
                    'indeterminate' => false,
                    'child_count' => 0,
                ];
            } else {
                // 二级功能
                [$parent, $child] = explode('/', $code, 2);
                if (!isset($childrenMap[$parent])) {
                    $childrenMap[$parent] = [];
                }
                $childrenMap[$parent][] = [
                    'code' => $code,
                    'name' => $name,
                    'checked' => in_array($code, $existingFeatures),
                ];
            }
        }

        // 将子功能挂载到父功能下
        foreach ($childrenMap as $parent => $children) {
            if (isset($tree[$parent])) {
                $tree[$parent]['children'] = $children;
                $tree[$parent]['child_count'] = count($children);
                // 计算父级的选中状态
                $checkedCount = count(array_filter($children, fn($c) => $c['checked']));
                $totalCount = count($children);
                if ($checkedCount === 0) {
                    $tree[$parent]['checked'] = false;
                    $tree[$parent]['indeterminate'] = false;
                } elseif ($checkedCount === $totalCount) {
                    $tree[$parent]['checked'] = true;
                    $tree[$parent]['indeterminate'] = false;
                } else {
                    $tree[$parent]['checked'] = false;
                    $tree[$parent]['indeterminate'] = true;
                }
            }
        }

        return array_values($tree);
    }

    protected function log(string $type, string $content): void
    {
        $admin = Session::get('admin_info');
        Db::name('log')->insert([
            'tenant_id' => $this->getTenantId(),
            'admin_id' => $admin['id'] ?? 0,
            'type' => $type,
            'content' => $content,
            'url' => $this->request->url(),
            'ip' => $this->request->ip(),
            'create_time' => time(),
        ]);
    }
}
