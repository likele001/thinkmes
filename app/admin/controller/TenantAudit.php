<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\controller\Backend;
use app\admin\model\TenantRegisterModel;
use app\admin\model\TenantModel;
use app\admin\model\AdminModel;
use app\admin\model\TenantPackageModel;
use app\admin\model\TenantPackageFeatureModel;
use app\admin\model\RoleModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 租户注册审核（仅平台超管 tenant_id=0 可访问）
 */
class TenantAudit extends Backend
{
    public function index(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可审核租户注册');
        }

        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '租户注册审核');
            return $this->fetchWithLayout('tenant_audit/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $query = TenantRegisterModel::order('id', 'desc');

        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        // 关联套餐名称
        $pkgIds = array_column($list, 'package_id');
        $pkgs = $pkgIds ? TenantPackageModel::whereIn('id', $pkgIds)->column('name', 'id') : [];
        foreach ($list as &$row) {
            $row['package_name'] = $pkgs[$row['package_id']] ?? '-';
        }

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 审核通过（自动创建租户和管理员）
     */
    public function approve(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可审核租户注册');
        }

        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $id = $this->request->post('id');
        $register = TenantRegisterModel::find($id);
        if (!$register) {
            return $this->error('注册申请不存在');
        }

        if ($register->status != 0) {
            return $this->error('该申请已处理');
        }

        Db::startTrans();
        try {
            // 创建租户
            $tenant = TenantModel::create([
                'name' => $register->company_name,
                'company_name' => $register->company_name,
                'contact_name' => $register->contact_name,
                'contact_phone' => $register->contact_phone,
                'contact_email' => $register->contact_email,
                'domain' => $register->domain,
                'package_id' => $register->package_id,
                'expire_time' => time() + (365 * 86400), // 默认1年
                'status' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ]);

            // 获取或创建套餐对应的永久默认角色
            $roleId = $this->ensureDefaultRoleForPackage((int) $register->package_id);

            // 自动创建租户管理员账号（使用用户注册时填写的账号和密码）
            $salt = substr(md5(uniqid()), 0, 6);

            AdminModel::create([
                'tenant_id' => $tenant->id,
                'pid' => 0,
                'username' => $register->login_account, // 用户自定义的登录账号
                'password' => $register->login_password, // 用户设置的密码（已bcrypt加密）
                'salt' => $salt,
                'nickname' => $register->company_name, // 昵称使用企业名称
                'email' => $register->contact_email,
                'mobile' => $register->contact_phone,
                'avatar' => '/assets/img/avatar.png',
                'role_ids' => (string) $roleId, // 分配套餐永久角色
                'data_scope' => 3, // 全部数据权限
                'status' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ]);

            // 更新注册申请状态
            $register->status = 1; // 已通过
            $register->audit_user_id = $this->auth->id ?? 0;
            $register->audit_time = time();
            $register->save();

            Db::commit();

            // 返回登录信息给审核人员
            $loginInfo = [
                'tenant_id' => $tenant->id,
                'company_name' => $tenant->company_name,
                'login_account' => $register->login_account,
                'login_url' => request()->domain() . '/admin/login', // 登录地址
            ];
            return $this->success('审核通过，已创建租户和管理员账号', $loginInfo);
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('审核失败：' . $e->getMessage());
        }
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
            $features = Db::name('tenant_package_feature')
                ->where('package_id', $packageId)
                ->where('is_enabled', 1)
                ->column('feature_code');
            $authRuleIds = [];
            if (!empty($features)) {
                $authRuleIds = Db::name('auth_rule')
                    ->where('status', 1)
                    ->whereIn('name', $features)
                    ->column('id');
            }
            $baseIds = Db::name('auth_rule')->where('status', 1)->whereIn('name', ['dashboard','admin/index','admin/index/index'])->column('id');
            $authRuleIds = array_values(array_unique(array_merge($authRuleIds, $baseIds, [1])));
            $roleName = '套餐:' . ($pkg['name'] ?? ('#' . $pkg['id'])) . '默认角色';
            $exist = RoleModel::where('name', $roleName)->find();
            $rulesStr = implode(',', array_map('strval', $authRuleIds));
            if ($exist) {
                $exist->rules = $rulesStr;
                $exist->status = 1;
                $exist->update_time = time();
                $exist->save();
                return (int) $exist->id;
            } else {
                $role = RoleModel::create([
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

    /**
     * 审核拒绝
     */
    public function reject(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可审核租户注册');
        }

        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $id = $this->request->post('id');
        $remark = $this->request->post('remark', '');

        $register = TenantRegisterModel::find($id);
        if (!$register) {
            return $this->error('注册申请不存在');
        }

        if ($register->status != 0) {
            return $this->error('该申请已处理');
        }

        try {
            $register->status = 2; // 已拒绝
            $register->audit_user_id = $this->auth->id ?? 0;
            $register->audit_time = time();
            $register->audit_remark = $remark;
            $register->save();

            return $this->success('已拒绝该申请');
        } catch (\Exception $e) {
            return $this->error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 删除申请记录
     */
    public function del(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可审核租户注册');
        }

        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $id = $this->request->post('id');
        $register = TenantRegisterModel::find($id);
        if (!$register) {
            return $this->error('记录不存在');
        }

        try {
            $register->delete();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
