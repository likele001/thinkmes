<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\TenantModel;
use app\admin\model\AdminModel;
use app\admin\model\RoleModel;
use app\admin\model\TenantPackageModel;
use app\admin\model\TenantPackageFeatureModel;
use app\admin\controller\Backend;
use think\facade\Db;
use think\Validate;
use think\facade\Config;
use think\facade\View;

/**
 * 管理员注册（租户注册成为管理员）
 *
 * @icon fa fa-user-plus
 */
class Register extends Backend
{
    protected $noNeedLogin = ['index'];
    protected $layout = '';

    public function initialize(): void
    {
        // 调用父类的 initialize 方法
        parent::initialize();

        // 移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
    }

    /**
     * 注册页面
     */
    public function index()
    {
        // 检查是否允许注册（默认开启）
        $allowRegister = Config::get('fastadmin.admin_register_enable');
        if ($allowRegister === false || $allowRegister === 'false' || $allowRegister === '0') {
            return redirect((string) url('index/login'));
        }

        // 获取套餐列表
        $packages = TenantPackageModel::order('sort')->order('id')->select()->toArray();
        View::assign('packages', $packages);
        View::assign('title', '租户注册');
        return View::fetch();
    }

    /**
     * 提交注册
     */
    public function save()
    {
        if (!$this->request->isPost()) {
            return $this->error('请求方式错误');
        }

        $params = $this->request->post();

        // 验证规则（注册即创建租户 + 管理员，并绑定 tenant_id）
        $rule = [
            'tenant_name' => 'require|length:1,100',
            'username'    => 'require|length:3,30|regex:\w{3,30}',
            'password'    => 'require|length:6,30',
            'email'       => 'require|email',
            'nickname'    => 'require|length:1,50',
            'package_id'  => 'require|integer',
            '__token__'   => 'require|token',
        ];

        $msg = [
            'tenant_name.require' => '租户名称不能为空',
            'tenant_name.length'  => '租户名称长度必须在1-100个字符之间',
            'username.require'    => '登录账号不能为空',
            'username.length'     => '登录账号长度必须在3-30个字符之间',
            'username.regex'      => '登录账号只能包含字母、数字和下划线',
            'password.require'    => '登录密码不能为空',
            'password.length'     => '登录密码长度必须在6-30个字符之间',
            'email.require'       => '联系邮箱不能为空',
            'email.email'         => '邮箱格式不正确',
            'nickname.require'    => '昵称不能为空',
            'nickname.length'     => '昵称长度必须在1-50个字符之间',
            'package_id.require'  => '请选择套餐',
        ];

        // 验证码（如果启用）
        if (Config::get('fastadmin.register_captcha', false)) {
            $rule['captcha'] = 'require|captcha';
            $msg['captcha.require'] = '验证码不能为空';
        }

        $validate = new Validate($rule, $msg);
        $result = $validate->check($params);
        if (!$result) {
            return $this->error($validate->getError());
        }

        // 检查租户名称是否已存在
        $existingTenant = TenantModel::where('name', $params['tenant_name'])->find();
        if ($existingTenant) {
            return $this->error('租户名称已存在，请使用其他名称');
        }

        // 检查用户名是否已存在（检查全局唯一性）
        $existingAdmin = AdminModel::where('username', $params['username'])->find();
        if ($existingAdmin) {
            return $this->error('登录账号已存在，请使用其他账号');
        }

        // 检查邮箱是否已存在
        if (AdminModel::where('email', $params['email'])->find()) {
            return $this->error('邮箱已被使用');
        }

        Db::startTrans();
        try {
            // 获取选择的套餐
            $packageId = (int) $params['package_id'];
            $package = TenantPackageModel::find($packageId);
            if (!$package) {
                return $this->error('选择的套餐不存在');
            }

            // 创建租户
            $tenant = new TenantModel();
            $tenant->name = $params['tenant_name'];
            $tenant->company_name = $params['tenant_name'];
            $tenant->contact_name = $params['nickname'];
            $tenant->contact_phone = $params['mobile'] ?? '';
            $tenant->contact_email = $params['email'];
            $tenant->domain = ''; // 暂时留空
            $tenant->package_id = $packageId;
            $tenant->expire_time = time() + (365 * 86400); // 默认1年
            $tenant->status = 1; // 激活
            $tenant->create_time = time();
            $tenant->update_time = time();
            $tenant->save();

            if (!$tenant || !$tenant->id) {
                return $this->error('创建租户失败');
            }

            // 获取套餐对应的MES功能，创建租户默认角色
            $packageFeatures = TenantPackageFeatureModel::where('package_id', $packageId)
                ->where('is_enabled', 1)
                ->column('feature_code');

            // 根据feature_code查找对应的auth_rule ID
            if (!empty($packageFeatures)) {
                $authRuleIds = Db::name('auth_rule')
                    ->where('status', 1)
                    ->whereIn('name', $packageFeatures)
                    ->column('id');
            } else {
                $authRuleIds = [];
            }

            // 创建租户默认角色（包含套餐所有权限）
            $role = RoleModel::create([
                'name' => $tenant->name . '管理员',
                'rules' => implode(',', $authRuleIds),
                'status' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ]);
            if (!$role) {
                return $this->error('创建角色失败');
            }

            // 创建管理员并绑定 tenant_id
            $adminData = [
                'username'     => $params['username'],
                'nickname'     => $params['nickname'],
                'email'        => $params['email'],
                'mobile'       => $params['mobile'] ?? '',
                'password'     => $params['password'], // 模型会自动哈希
                'salt'         => '', // 登录时使用 password_verify，不需要 salt
                'avatar'       => '/assets/img/avatar.png',
                'tenant_id'    => $tenant->id, // 绑定租户
                'role_ids'     => (string) $role->id, // 分配角色
                'data_scope'   => 3, // 全部数据权限
                'status'       => 1, // 启用状态
                'create_time'   => time(),
                'update_time'   => time(),
            ];

            $admin = AdminModel::create($adminData);
            if (!$admin) {
                return $this->error('创建管理员账号失败');
            }

            Db::commit();

            // 记录日志
            Db::name('log')->insert([
                'tenant_id' => $tenant->id,
                'admin_id' => $admin->id,
                'type' => 'add',
                'content' => '添加管理员:' . $params['username'],
                'url' => $this->request->url(),
                'ip' => $this->request->ip(),
                'create_time' => time(),
            ]);

            return $this->success('注册成功！请使用该账号登录', ['url' => (string) url('index/login')]);

        } catch (\Exception $e) {
            Db::rollback();
            $msg = $e->getMessage();
            if (strpos($msg, 'Duplicate entry') !== false || strpos($msg, '1062') !== false) {
                if (strpos($msg, "key 'username'") !== false) {
                    return $this->error('登录账号已存在，请使用其他账号');
                } elseif (strpos($msg, "key 'email'") !== false) {
                    return $this->error('邮箱已被使用，请使用其他邮箱');
                }
                return $this->error('数据已存在，请更换后重试');
            }
            return $this->error('注册失败：' . $msg);
        }
    }

    /**
     * 检查租户名称是否可用（AJAX）
     */
    public function check()
    {
        if (!$this->request->isAjax()) {
            return $this->error('请求方式错误');
        }

        $tenantName = $this->request->get('tenant_name');
        if (empty($tenantName)) {
            return json(['data' => 1]);
        }

        $existing = TenantModel::where('name', $tenantName)->find();
        if ($existing) {
            return json(['data' => 0]);
        }

        return json(['data' => 1]);
    }

    /**
     * 检查登录账号是否可用（AJAX）
     */
    public function checkUsername()
    {
        if (!$this->request->isAjax()) {
            return $this->error('请求方式错误');
        }

        $username = $this->request->get('username');
        if (empty($username)) {
            return json(['data' => 1]);
        }

        $existing = AdminModel::where('username', $username)->find();
        if ($existing) {
            return json(['data' => 0]);
        }

        return json(['data' => 1]);
    }
}
