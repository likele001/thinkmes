<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\TenantOrderModel;
use app\admin\model\TenantModel;
use app\admin\model\TenantPackageModel;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

/**
 * 租户订单管理（仅平台超管 tenant_id=0 可访问）
 */
class TenantOrder extends Backend
{
    public function index(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理订单');
        }
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '租户订单管理');
            return $this->fetchWithLayout('tenant_order/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $orderNo = trim((string) $this->request->get('order_no'));
        $tenantId = (int) $this->request->get('tenant_id', 0);
        $status = $this->request->get('status');

        $query = TenantOrderModel::order('id', 'desc');
        if ($orderNo !== '') {
            $query->where('order_no', 'like', '%' . $orderNo . '%');
        }
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        
        // 关联租户和套餐信息
        $tenantIds = array_unique(array_column($list, 'tenant_id'));
        $packageIds = array_unique(array_column($list, 'package_id'));
        $tenants = $tenantIds ? TenantModel::whereIn('id', $tenantIds)->column('name', 'id') : [];
        $packages = $packageIds ? TenantPackageModel::whereIn('id', $packageIds)->column('name', 'id') : [];
        
        $typeMap = [1 => '购买', 2 => '续费', 3 => '升级'];
        $statusMap = [0 => '待支付', 1 => '已支付', 2 => '已取消', 3 => '已退款'];
        
        foreach ($list as &$row) {
            $row['tenant_name'] = $tenants[$row['tenant_id']] ?? '-';
            $row['package_name'] = $packages[$row['package_id']] ?? '-';
            $row['type_text'] = $typeMap[$row['type']] ?? '-';
            $row['status_text'] = $statusMap[$row['status']] ?? '-';
            $row['pay_time_text'] = ($row['pay_time'] ?? null) && $row['pay_time'] > 0 ? date('Y-m-d H:i:s', (int) $row['pay_time']) : '-';
            $row['create_time_text'] = ($row['create_time'] ?? null) && $row['create_time'] > 0 ? date('Y-m-d H:i:s', (int) $row['create_time']) : '-';
        }
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理订单');
        }
        if ($this->request->isPost()) {
            return $this->addPost();
        }
        $tenants = TenantModel::where('status', 1)->order('id')->select()->toArray();
        $packages = TenantPackageModel::order('sort')->order('id')->select()->toArray();
        $defaultTenantId = (int) $this->request->get('tenant_id', 0);
        View::assign('tenants', $tenants);
        View::assign('packages', $packages);
        View::assign('data', []);
        View::assign('title', '创建订单');
        return $this->fetchWithLayout('tenant_order/add');
    }

    public function addPost(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理订单');
        }
        $tenantId = (int) $this->request->post('tenant_id', 0);
        $packageId = (int) $this->request->post('package_id', 0);
        $type = (int) $this->request->post('type', 1);
        $amount = (float) $this->request->post('amount', 0);
        $expireDays = $this->request->post('expire_days');
        $remark = trim((string) $this->request->post('remark', ''));
        
        if ($tenantId <= 0) {
            return $this->error('请选择租户');
        }
        if ($packageId <= 0) {
            return $this->error('请选择套餐');
        }
        if (!in_array($type, [1, 2, 3], true)) {
            return $this->error('订单类型错误');
        }
        
        $tenant = TenantModel::find($tenantId);
        if (!$tenant) {
            return $this->error('租户不存在');
        }
        $package = TenantPackageModel::find($packageId);
        if (!$package) {
            return $this->error('套餐不存在');
        }
        
        $expireDaysInt = null;
        if ($expireDays !== '' && $expireDays !== null) {
            $expireDaysInt = max(1, (int) $expireDays);
        }
        
        $now = time();
        $order = TenantOrderModel::create([
            'tenant_id' => $tenantId,
            'order_no' => TenantOrderModel::generateOrderNo(),
            'package_id' => $packageId,
            'type' => $type,
            'amount' => $amount,
            'status' => 0, // 待支付
            'expire_days' => $expireDaysInt,
            'remark' => $remark,
            'create_time' => $now,
            'update_time' => $now,
        ]);
        
        $this->log('add', '创建租户订单:' . $order->order_no);
        return $this->success('创建成功', ['id' => $order->id]);
    }

    public function pay(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可操作');
        }
        $id = (int) $this->request->post('id');
        $payMethod = trim((string) $this->request->post('pay_method', 'manual'));
        
        $order = TenantOrderModel::find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }
        if ($order->status !== 0) {
            return $this->error('订单状态不允许支付');
        }
        
        // 更新订单状态
        $order->status = 1; // 已支付
        $order->pay_method = $payMethod;
        $order->pay_time = time();
        $order->update_time = time();
        $order->save();
        
        // 如果是续费或升级，更新租户信息
        if ($order->type === 2) { // 续费
            $tenant = TenantModel::find($order->tenant_id);
            if ($tenant) {
                $currentExpire = $tenant->expire_time ?? time();
                $newExpire = $currentExpire + ($order->expire_days ?? 365) * 86400;
                $tenant->expire_time = $newExpire;
                $tenant->update_time = time();
                $tenant->save();
            }
        } elseif ($order->type === 3) { // 升级
            $tenant = TenantModel::find($order->tenant_id);
            if ($tenant) {
                $tenant->package_id = $order->package_id;
                $tenant->update_time = time();
                $tenant->save();
            }
        }
        
        $this->log('edit', '订单支付:id=' . $id . ', order_no=' . $order->order_no);
        return $this->success('支付成功');
    }

    public function cancel(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可操作');
        }
        $id = (int) $this->request->post('id');
        $order = TenantOrderModel::find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }
        if ($order->status !== 0) {
            return $this->error('只能取消待支付订单');
        }
        
        $order->status = 2; // 已取消
        $order->update_time = time();
        $order->save();
        
        $this->log('edit', '取消订单:id=' . $id);
        return $this->success('取消成功');
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
