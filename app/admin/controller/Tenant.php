<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\TenantModel;
use app\admin\model\TenantPackageModel;
use app\admin\model\AdminModel;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

/**
 * 租户管理（仅平台超管 tenant_id=0 可访问）
 */
class Tenant extends Backend
{
    public function index(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理租户');
        }
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '租户管理');
            return $this->fetchWithLayout('tenant/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $name = trim((string) $this->request->get('name'));
        $status = $this->request->get('status');

        $query = TenantModel::order('id', 'desc');
        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        $pkgIds = array_unique(array_column($list, 'package_id'));
        $pkgs = $pkgIds ? TenantPackageModel::whereIn('id', $pkgIds)->column('name', 'id') : [];
        foreach ($list as &$row) {
            $row['package_name'] = $pkgs[$row['package_id'] ?? 0] ?? '-';
            $ts = $row['expire_time'] ?? null;
            $row['expire_time_text'] = ($ts !== null && $ts > 0) ? date('Y-m-d', (int) $ts) : '永久';
        }
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理租户');
        }
        if ($this->request->isPost()) {
            return $this->addPost();
        }
        $packages = TenantPackageModel::order('sort')->order('id')->select()->toArray();
        View::assign('packages', $packages);
        View::assign('data', []);
        View::assign('title', '添加租户');
        return $this->fetchWithLayout('tenant/add');
    }

    public function addPost(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理租户');
        }
        $name = trim((string) $this->request->post('name'));
        $domain = trim((string) $this->request->post('domain', ''));
        $packageId = (int) $this->request->post('package_id', 0);
        $expireTime = $this->request->post('expire_time');
        $status = (int) $this->request->post('status', 1);
        if (strlen($name) < 1) {
            return $this->error('租户名称不能为空');
        }
        $now = time();
        $expire = null;
        if ($expireTime !== '' && $expireTime !== null) {
            $expire = strtotime((string) $expireTime);
            if ($expire === false) {
                $expire = null;
            }
        }
        $tenant = TenantModel::create([
            'name' => $name,
            'domain' => $domain,
            'package_id' => $packageId,
            'expire_time' => $expire,
            'status' => $status,
            'create_time' => $now,
            'update_time' => $now,
        ]);
        
        // 自动创建租户管理员账号（默认账号：admin，密码：123456）
        $adminUsername = 'admin';
        $adminPassword = password_hash('123456', PASSWORD_BCRYPT);
        $adminNickname = $name . '管理员';
        
        // 检查是否已存在该租户的管理员
        $existAdmin = AdminModel::where('tenant_id', $tenant->id)->where('username', $adminUsername)->find();
        if (!$existAdmin) {
            AdminModel::create([
                'tenant_id' => $tenant->id,
                'pid' => 0,
                'username' => $adminUsername,
                'password' => $adminPassword,
                'salt' => 'fast',
                'nickname' => $adminNickname,
                'role_ids' => '', // 需要租户管理员自己分配角色
                'data_scope' => 3, // 全部数据权限
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now,
            ]);
        }
        
        $this->log('add', '添加租户:' . $name . '，已自动创建管理员账号：' . $adminUsername);
        return $this->success('添加成功，已自动创建管理员账号（admin/123456）', ['id' => $tenant->id]);
    }

    public function edit(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理租户');
        }
        if ($this->request->isPost()) {
            return $this->editPost();
        }
        $id = (int) $this->request->get('id');
        $data = TenantModel::find($id);
        if (!$data) {
            return $this->error('记录不存在');
        }
        $data = $data->toArray();
        $data['expire_time'] = ($data['expire_time'] ?? null) && $data['expire_time'] > 0 ? date('Y-m-d', (int) $data['expire_time']) : '';
        $packages = TenantPackageModel::order('sort')->order('id')->select()->toArray();
        View::assign('packages', $packages);
        View::assign('data', $data);
        View::assign('title', '编辑租户');
        return $this->fetchWithLayout('tenant/edit');
    }

    public function editPost(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理租户');
        }
        $id = (int) $this->request->post('id');
        $row = TenantModel::find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $name = trim((string) $this->request->post('name'));
        $domain = trim((string) $this->request->post('domain', ''));
        $packageId = (int) $this->request->post('package_id', 0);
        $expireTime = $this->request->post('expire_time');
        $status = (int) $this->request->post('status', 1);
        if (strlen($name) < 1) {
            return $this->error('租户名称不能为空');
        }
        $expire = null;
        if ($expireTime !== '' && $expireTime !== null) {
            $expire = strtotime((string) $expireTime);
            if ($expire === false) {
                $expire = null;
            }
        }
        $row->name = $name;
        $row->domain = $domain;
        $row->package_id = $packageId;
        $row->expire_time = $expire;
        $row->status = $status;
        $row->update_time = time();
        $row->save();
        $this->log('edit', '编辑租户:id=' . $id);
        return $this->success('保存成功', ['id' => $id]);
    }

    public function del(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理租户');
        }
        $id = (int) $this->request->post('id');
        $row = TenantModel::find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $row->delete();
        $this->log('del', '删除租户:id=' . $id);
        return $this->success('删除成功');
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
