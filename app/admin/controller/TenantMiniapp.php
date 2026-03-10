<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\TenantMiniappModel;
use app\admin\model\TenantModel;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

class TenantMiniapp extends Backend
{
    public function index(): string|Response
    {
        $admin = Session::get('admin_info');
        $tenantId = (int) ($admin['tenant_id'] ?? 0);
        $adminId = (int) ($admin['id'] ?? 0);
        $superId = (int) (config('auth.super_admin_id') ?? 1);
        if ($tenantId <= 0 && $adminId !== $superId) {
            return $this->error('仅租户管理员可配置小程序信息');
        }
        // 租户需具备套餐中的「小程序」功能才可访问
        if ($tenantId > 0) {
            $tenant = TenantModel::where('id', $tenantId)->field('package_id')->find();
            $packageId = $tenant ? (int) ($tenant['package_id'] ?? 0) : 0;
            if ($packageId <= 0) {
                return $this->error('当前租户未分配套餐或套餐未包含小程序功能');
            }
            $hasMiniapp = Db::name('tenant_package_feature')
                ->where('package_id', $packageId)
                ->where('feature_code', 'admin/tenant/miniapp')
                ->find();
            if (!$hasMiniapp) {
                return $this->error('当前套餐未开通小程序功能，请联系平台在「租户套餐」中为该套餐分配「小程序」功能');
            }
        }

        $model = new TenantMiniappModel();

        if (!$this->request->isAjax() && !$this->request->isPost()) {
            $row = $model
                ->where('tenant_id', $tenantId)
                ->where('type', 'wechat')
                ->find();
            if (!$row) {
                $row = [
                    'name'       => '',
                    'app_id'     => '',
                    'app_secret' => '',
                    'status'     => 1,
                ];
            }
            View::assign('row', $row);
            View::assign('title', '租户小程序配置');
            return $this->fetchWithLayout('tenant_miniapp/index');
        }

        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $params = $this->request->post();
        $row = $model
            ->where('tenant_id', $tenantId)
            ->where('type', 'wechat')
            ->find();
        $data = [
            'tenant_id'   => $tenantId,
            'type'        => 'wechat',
            'name'        => (string) ($params['name'] ?? ''),
            'app_id'      => (string) ($params['app_id'] ?? ''),
            'app_secret'  => (string) ($params['app_secret'] ?? ''),
            'status'      => isset($params['status']) ? (int) $params['status'] : 1,
            'update_time' => time(),
        ];
        if ($row) {
            $row->save($data);
        } else {
            $data['create_time'] = time();
            $model->create($data);
        }
        return $this->success('保存成功');
    }
}
