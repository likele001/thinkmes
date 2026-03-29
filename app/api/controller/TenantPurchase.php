<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\admin\model\TenantModel;
use app\admin\model\TenantPackageModel;
use app\admin\model\TenantOrderModel;
use app\common\lib\payment\PaymentService;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 租户自助购买API
 */
class TenantPurchase extends BaseController
{
    /**
     * 获取套餐列表（公开访问）
     */
    public function packageList(): Response
    {
        $packages = TenantPackageModel::order('sort')
            ->order('id')
            ->select()
            ->toArray();

        foreach ($packages as &$pkg) {
            $pkg['expire_days_text'] = ($pkg['expire_days'] ?? null) && $pkg['expire_days'] > 0
                ? $pkg['expire_days'] . '天'
                : '永久';
            $pkg['price_text'] = number_format($pkg['price'] ?? 0, 2);
            // 获取套餐功能列表
            $features = Db::name('tenant_package_feature')
                ->where('package_id', $pkg['id'])
                ->where('is_enabled', 1)
                ->column('feature_name');
            $pkg['features'] = $features;
        }

        return $this->success('获取成功', $packages);
    }

    /**
     * 创建租户购买订单
     */
    public function createOrder(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $packageId = (int) $this->request->post('package_id', 0);
        $companyId = trim($this->request->post('company_name', ''));
        $contactName = trim($this->request->post('contact_name', ''));
        $contactPhone = trim($this->request->post('contact_phone', ''));
        $contactEmail = trim($this->request->post('contact_email', ''));
        $domain = trim($this->request->post('domain', ''));
        $loginAccount = trim($this->request->post('login_account', ''));
        $loginPassword = trim($this->request->post('login_password', ''));

        // 验证必填项
        if ($packageId <= 0) {
            return $this->error('请选择套餐');
        }
        if ($companyId === '') {
            return $this->error('请填写企业名称');
        }
        if ($contactName === '') {
            return $this->error('请填写联系人');
        }
        if ($contactPhone === '') {
            return $this->error('请填写联系电话');
        }
        if ($contactEmail === '') {
            return $this->error('请填写联系邮箱');
        }
        if ($domain === '') {
            return $this->error('请填写绑定域名');
        }
        if ($loginAccount === '') {
            return $this->error('请填写登录账号');
        }
        if ($loginPassword === '') {
            return $this->error('请填写登录密码');
        }

        // 验证邮箱格式
        if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->error('邮箱格式不正确');
        }

        // 验证域名是否已被占用
        $existTenant = TenantModel::where('domain', 'like', '%' . $domain . '%')->find();
        if ($existTenant) {
            return $this->error('域名已被占用');
        }

        // 获取套餐信息
        $package = TenantPackageModel::find($packageId);
        if (!$package) {
            return $this->error('套餐不存在');
        }

        // 验证登录账号是否已存在
        $existAdmin = Db::name('admin')->where('username', $loginAccount)->find();
        if ($existAdmin) {
            return $this->error('登录账号已存在');
        }

        // 生成订单号
        $orderNo = 'TO' . date('YmdHis') . rand(1000, 9999);

        // 开启事务
        Db::startTrans();
        try {
            // 创建租户订单
            $order = TenantOrderModel::create([
                'tenant_id' => 0, // 租户ID待支付后创建
                'order_no' => $orderNo,
                'package_id' => $packageId,
                'type' => 1, // 购买
                'amount' => $package['price'] ?? 0,
                'status' => 0, // 待支付
                'expire_days' => $package['expire_days'],
                'remark' => json_encode([
                    'company_name' => $companyId,
                    'contact_name' => $contactName,
                    'contact_phone' => $contactPhone,
                    'contact_email' => $contactEmail,
                    'domain' => $domain,
                    'login_account' => $loginAccount,
                    'login_password' => password_hash($loginPassword, PASSWORD_BCRYPT),
                ], JSON_UNESCAPED_UNICODE),
                'create_time' => time(),
                'update_time' => time(),
            ]);

            Db::commit();

            return $this->success('订单创建成功', [
                'order_no' => $orderNo,
                'order_id' => $order->id,
                'amount' => $package['price'] ?? 0,
            ]);
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('创建订单失败：' . $e->getMessage());
        }
    }

    /**
     * 获取可用支付方式
     */
    public function paymentMethods(): Response
    {
        $gateways = Db::name('payment_gateway')
            ->where('tenant_id', 0) // 平台支付网关
            ->where('enabled', 1)
            ->order('sort')
            ->select()
            ->toArray();

        return $this->success('获取成功', $gateways);
    }

    /**
     * 发起支付
     */
    public function pay(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $orderNo = trim($this->request->post('order_no', ''));
        $gatewayId = (int) $this->request->post('gateway_id', 0);

        if ($orderNo === '') {
            return $this->error('订单号不能为空');
        }
        if ($gatewayId <= 0) {
            return $this->error('请选择支付方式');
        }

        // 查询订单
        $order = TenantOrderModel::where('order_no', $orderNo)->find();
        if (!$order) {
            return $this->error('订单不存在');
        }
        if ($order->status != 0) {
            return $this->error('订单状态不允许支付');
        }

        // 查询套餐
        $package = TenantPackageModel::find($order->package_id);
        if (!$package) {
            return $this->error('套餐不存在');
        }

        // 生成回调地址
        $notifyUrl = $this->request->domain() . '/api/tenant_purchase/notify';
        $returnUrl = $this->request->domain() . '/api/tenant_purchase/return?order_no=' . $orderNo;

        // 调用支付服务
        $result = PaymentService::create(
            $gatewayId,
            $orderNo,
            (float) ($order->amount ?? 0),
            '购买套餐：' . ($package['name'] ?? ''),
            $notifyUrl,
            $returnUrl,
            0 // 租户ID为0，创建订单后再更新
        );

        if (!empty($result['error'])) {
            return $this->error($result['error']);
        }

        return $this->success('发起支付成功', $result);
    }

    /**
     * 支付同步回调（页面跳转）
     */
    public function return(): string
    {
        $orderNo = trim($this->request->get('order_no', ''));
        $order = TenantOrderModel::where('order_no', $orderNo)->find();

        View::assign('order', $order ? $order->toArray() : null);
        View::assign('order_no', $orderNo);
        return $this->fetch('tenant_purchase/return');
    }

    /**
     * 支付异步回调
     */
    public function notify(): Response
    {
        $gatewayId = (int) $this->request->post('gateway_id', 0);
        if ($gatewayId <= 0) {
            return $this->error('参数错误');
        }

        $result = PaymentService::handleNotify($gatewayId, $this->request->post());

        if ($result['handled'] && $result['message'] === 'success') {
            // 支付成功后创建租户
            $this->activateTenant($result['order_no']);
            echo 'success';
        } else {
            echo $result['message'];
        }

        exit;
    }

    /**
     * 激活租户（支付成功后调用）
     */
    private function activateTenant(string $orderNo): void
    {
        $order = TenantOrderModel::where('order_no', $orderNo)->find();
        if (!$order || $order->status != 1) {
            return;
        }

        // 如果租户已创建，直接返回
        if ($order->tenant_id > 0) {
            return;
        }

        Db::startTrans();
        try {
            // 解析订单备注
            $remark = json_decode($order->remark ?? '{}', true);
            $remark = is_array($remark) ? $remark : [];

            // 创建租户
            $tenant = TenantModel::create([
                'name' => $remark['company_name'] ?? '',
                'company_name' => $remark['company_name'] ?? '',
                'contact_name' => $remark['contact_name'] ?? '',
                'contact_phone' => $remark['contact_phone'] ?? '',
                'contact_email' => $remark['contact_email'] ?? '',
                'domain' => $remark['domain'] ?? '',
                'package_id' => $order->package_id,
                'expire_time' => ($order->expire_days ?? 365) > 0
                    ? time() + ($order->expire_days ?? 365) * 86400
                    : null,
                'status' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ]);

            // 创建套餐默认角色
            $roleId = $this->ensureDefaultRoleForPackage((int) $order->package_id);

            // 创建管理员账号
            $salt = substr(md5(uniqid()), 0, 6);
            Db::name('admin')->insert([
                'tenant_id' => $tenant->id,
                'pid' => 0,
                'username' => $remark['login_account'] ?? '',
                'password' => $remark['login_password'] ?? '',
                'salt' => $salt,
                'nickname' => $remark['company_name'] ?? '',
                'email' => $remark['contact_email'] ?? '',
                'mobile' => $remark['contact_phone'] ?? '',
                'avatar' => '/assets/img/avatar.png',
                'role_ids' => (string) $roleId,
                'data_scope' => 3,
                'status' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ]);

            // 更新订单的租户ID
            $order->tenant_id = $tenant->id;
            $order->save();

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

    /**
     * 确保套餐有默认角色
     */
    private function ensureDefaultRoleForPackage(int $packageId): int
    {
        if ($packageId <= 0) {
            return 0;
        }

        try {
            $pkg = TenantPackageModel::find($packageId);
            if (!$pkg) {
                return 0;
            }

            $features = Db::name('tenant_package_feature')
                ->where('package_id', $packageId)
                ->where('is_enabled', 1)
                ->column('feature_code');

            $authRuleIds = [];
            if (!empty($features)) {
                foreach ($features as $code) {
                    $codeSlash = str_replace('.', '/', $code);
                    $idsExact = Db::name('auth_rule')->where('status', 1)->where('name', $codeSlash)->column('id');
                    $idsChildren = Db::name('auth_rule')->where('status', 1)->where('name', 'like', $codeSlash . '/%')->column('id');
                    $authRuleIds = array_merge($authRuleIds, $idsExact, $idsChildren);
                }
            }

            $baseIds = Db::name('auth_rule')->where('status', 1)->whereIn('name', ['dashboard', 'admin/index', 'admin/index/index'])->column('id');
            $authRuleIds = array_values(array_unique(array_merge($authRuleIds, $baseIds, [1])));

            $roleName = '套餐:' . ($pkg['name'] ?? ('#' . $pkg['id'])) . '默认角色';
            $exist = Db::name('role')->where('name', $roleName)->find();
            $rulesStr = implode(',', array_map('strval', $authRuleIds));

            if ($exist) {
                Db::name('role')->where('id', $exist['id'])->update([
                    'rules' => $rulesStr,
                    'status' => 1,
                    'update_time' => time(),
                ]);
                return (int) $exist['id'];
            } else {
                $role = Db::name('role')->insertGetId([
                    'name' => $roleName,
                    'rules' => $rulesStr,
                    'status' => 1,
                    'create_time' => time(),
                    'update_time' => time(),
                ]);
                return $role;
            }
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * 查询订单状态
     */
    public function orderStatus(): Response
    {
        $orderNo = trim($this->request->get('order_no', ''));
        if ($orderNo === '') {
            return $this->error('订单号不能为空');
        }

        $order = TenantOrderModel::where('order_no', $orderNo)->find();
        if (!$order) {
            return $this->error('订单不存在');
        }

        $statusMap = [0 => '待支付', 1 => '已支付', 2 => '已取消', 3 => '已退款'];
        $data = $order->toArray();
        $data['status_text'] = $statusMap[$order->status] ?? '未知';

        // 如果已支付且有租户ID，返回登录信息
        if ($order->status == 1 && $order->tenant_id > 0) {
            $tenant = TenantModel::find($order->tenant_id);
            if ($tenant) {
                $remark = json_decode($order->remark ?? '{}', true);
                $data['tenant'] = [
                    'tenant_id' => $tenant->id,
                    'company_name' => $tenant->company_name,
                    'login_account' => $remark['login_account'] ?? '',
                    'login_url' => $this->request->domain() . '/admin/login',
                ];
            }
        }

        return $this->success('获取成功', $data);
    }
}
