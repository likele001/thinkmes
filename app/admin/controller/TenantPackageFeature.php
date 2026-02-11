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
            View::assign('title', '套餐功能管理 - ' . $package->name);
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
        
        View::assign('package', $package->toArray());
        View::assign('allFeatures', $allFeatures);
        View::assign('existingFeatures', $existingFeatures);
        View::assign('title', '添加功能 - ' . $package->name);
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
            if (!isset($allFeatures[$code])) {
                continue; // 跳过无效的功能代码
            }
            // 检查是否已存在
            $exists = TenantPackageFeatureModel::where('package_id', $packageId)
                ->where('feature_code', $code)
                ->find();
            if (!$exists) {
                TenantPackageFeatureModel::create([
                    'package_id' => $packageId,
                    'feature_code' => $code,
                    'feature_name' => $allFeatures[$code],
                    'create_time' => $now,
                ]);
                $added++;
            }
        }
        
        $this->log('add', '为套餐ID=' . $packageId . '添加' . $added . '个功能');
        return $this->success('添加成功', ['added' => $added]);
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
        $row->delete();
        $this->log('del', '删除套餐功能:id=' . $id);
        return $this->success('删除成功');
    }

    /**
     * 获取所有可用的功能列表（可根据实际业务调整）
     */
    protected function getAllFeatures(): array
    {
        return [
            'order' => '订单管理',
            'product' => '产品管理',
            'inventory' => '库存管理',
            'report' => '报表统计',
            'export' => '数据导出',
            'api' => 'API接口访问',
            'custom_field' => '自定义字段',
            'workflow' => '工作流',
            'notification' => '消息通知',
            'backup' => '数据备份',
        ];
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
