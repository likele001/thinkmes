<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\AdminModel;
use app\admin\model\RoleModel;
use app\common\lib\Auth;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

class Admin extends Backend
{
    public function index(): string|Response
    {
        // 表格数据请求：带 limit 或 Ajax 时返回 JSON
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '管理员管理');
            return $this->fetchWithLayout('admin/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $username = trim((string) $this->request->get('username'));
        $status = $this->request->get('status');

        $tenantId = $this->getTenantId();
        // 平台管理员可以看到所有租户的管理员
        if ($tenantId === 0) {
            $query = AdminModel::order('id', 'desc');
        } else {
            $query = AdminModel::where('tenant_id', $tenantId)->order('id', 'desc');
        }
        $scopeIds = $this->getDataScopeAdminIds();
        if ($scopeIds !== null) {
            $query->whereIn('id', $scopeIds);
        }
        if ($username !== '') {
            $query->where('username', 'like', '%' . $username . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        $scopeText = [1 => '个人', 2 => '子级', 3 => '全部'];
        foreach ($list as &$row) {
            unset($row['password'], $row['salt']);
            $ts = $row['login_time'] ?? null;
            $row['login_time'] = ($ts !== null && $ts !== '') ? date('Y-m-d H:i', (int) $ts) : '';
            $row['data_scope_text'] = $scopeText[(int) ($row['data_scope'] ?? 1)] ?? '个人';
        }
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /** 可选父级管理员列表（当前数据权限内，用于 pid 下拉） */
    protected function getParentAdminOptions(): array
    {
        $tenantId = $this->getTenantId();
        $scopeIds = $this->getDataScopeAdminIds();
        $query = AdminModel::where('tenant_id', $tenantId)->where('status', 1)->field('id,username,nickname');
        if ($scopeIds !== null) {
            $query->whereIn('id', $scopeIds);
        }
        return $query->order('id')->select()->toArray();
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            return $this->addPost();
        }
        $roles = RoleModel::where('status', 1)->select()->toArray();
        $parents = $this->getParentAdminOptions();
        View::assign('roles', $roles);
        View::assign('parents', $parents);
        View::assign('data', []);
        View::assign('title', '添加管理员');
        return $this->fetchWithLayout('admin/add');
    }

    public function addPost(): Response
    {
        // 检查管理员数限制
        $resourceCheck = $this->checkResourceLimit('admin');
        if (!$resourceCheck['allowed']) {
            return $this->error($resourceCheck['msg']);
        }

        $username = trim((string) $this->request->post('username'));
        $password = (string) $this->request->post('password');
        $nickname = trim((string) $this->request->post('nickname', ''));
        $roleIds = trim((string) $this->request->post('role_ids', ''));
        $status = (int) $this->request->post('status', 1);

        if (strlen($username) < 2 || strlen($username) > 20) {
            return $this->error('账号长度 2-20');
        }
        if (strlen($password) < 6 || strlen($password) > 32) {
            return $this->error('密码长度 6-32');
        }
        if (AdminModel::where('tenant_id', $this->getTenantId())->where('username', $username)->find()) {
            return $this->error('账号已存在');
        }

        $admin = new AdminModel();
        $admin->tenant_id = $this->getTenantId();
        $admin->pid = (int) $this->request->post('pid', $this->request->session('admin_info')['id'] ?? 0);
        $admin->username = $username;
        $admin->password = $password;
        $admin->nickname = $nickname ?: $username;
        $admin->role_ids = $roleIds;
        $admin->status = $status;
        $admin->data_scope = max(1, min(3, (int) $this->request->post('data_scope', 1)));
        $admin->create_time = time();
        $admin->update_time = time();
        $admin->save();

        $this->log('add', '添加管理员:' . $username);
        return $this->success('添加成功', ['id' => $admin->id]);
    }

    public function edit(): string|Response
    {
        if ($this->request->isPost()) {
            return $this->editPost();
        }
        $id = (int) $this->request->get('id');
        if (!$this->canManageAdminId($id)) {
            return $this->error('无权限操作该管理员');
        }
        $data = AdminModel::where('tenant_id', $this->getTenantId())->find($id);
        if (!$data) {
            return $this->error('记录不存在');
        }
        $data = $data->toArray();
        unset($data['password'], $data['salt']);
        $roles = RoleModel::where('status', 1)->select()->toArray();
        $parents = $this->getParentAdminOptions();
        View::assign('roles', $roles);
        View::assign('parents', $parents);
        View::assign('data', $data);
        View::assign('title', '编辑管理员');
        return $this->fetchWithLayout('admin/edit');
    }

    public function editPost(): Response
    {
        $id = (int) $this->request->post('id');
        if (!$this->canManageAdminId($id)) {
            return $this->error('无权限操作该管理员');
        }
        $admin = AdminModel::where('tenant_id', $this->getTenantId())->find($id);
        if (!$admin) {
            return $this->error('记录不存在');
        }
        $nickname = trim((string) $this->request->post('nickname', ''));
        $password = (string) $this->request->post('password', '');
        $roleIds = trim((string) $this->request->post('role_ids', ''));
        $status = (int) $this->request->post('status', 1);
        $pid = (int) $this->request->post('pid', 0);
        $dataScope = max(1, min(3, (int) $this->request->post('data_scope', 1)));

        $admin->nickname = $nickname ?: $admin->username;
        $admin->role_ids = $roleIds;
        $admin->status = $status;
        $admin->pid = $pid;
        $admin->data_scope = $dataScope;
        $admin->update_time = time();
        if ($password !== '') {
            if (strlen($password) < 6 || strlen($password) > 32) {
                return $this->error('密码长度 6-32');
            }
            // 直接赋值原始密码，模型的 setPasswordAttr 会自动加密
            $admin->password = $password;
        }
        $admin->save();

        (new Auth())->clearCache($id);
        $this->log('edit', '编辑管理员:id=' . $id);
        return $this->success('保存成功', ['id' => $id]);
    }

    public function del(): Response
    {
        $id = (int) $this->request->post('id');
        if (!$this->canManageAdminId($id)) {
            return $this->error('无权限操作该管理员');
        }
        $admin = AdminModel::where('tenant_id', $this->getTenantId())->find($id);
        if (!$admin) {
            return $this->error('记录不存在');
        }
        if ($id === 1) {
            return $this->error('不能删除超级管理员');
        }
        $admin->delete();
        (new Auth())->clearCache($id);
        $this->log('del', '删除管理员:id=' . $id);
        return $this->success('删除成功');
    }

    public function resetPwd(): Response
    {
        $id = (int) $this->request->post('id');
        if (!$this->canManageAdminId($id)) {
            return $this->error('无权限操作该管理员');
        }
        $password = (string) $this->request->post('password', '123456');
        $admin = AdminModel::where('tenant_id', $this->getTenantId())->find($id);
        if (!$admin) {
            return $this->error('记录不存在');
        }
        if (strlen($password) < 6 || strlen($password) > 32) {
            return $this->error('密码长度 6-32');
        }
        // 直接赋值原始密码，模型的 setPasswordAttr 会自动加密
        $admin->password = $password;
        $admin->update_time = time();
        $admin->save();
        $this->log('edit', '重置密码:id=' . $id);
        return $this->success('重置成功');
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
