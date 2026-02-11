<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\common\model\UserModel;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

/**
 * C端用户管理（后台查看/禁用 fa_user，注册登录等由前端 api/user 完成）
 */
class Member extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '用户管理');
            return $this->fetchWithLayout('member/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $username = trim((string) $this->request->get('username'));
        $mobile = trim((string) $this->request->get('mobile'));
        $status = $this->request->get('status');

        $tenantId = $this->getTenantId();
        $query = UserModel::where('tenant_id', $tenantId)->order('id', 'desc');
        if ($username !== '') {
            $query->where('username', 'like', '%' . $username . '%');
        }
        if ($mobile !== '') {
            $query->where('mobile', 'like', '%' . $mobile . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            unset($row['password']);
            $ts = $row['login_time'] ?? null;
            $row['login_time'] = ($ts !== null && $ts !== '') ? date('Y-m-d H:i', (int) $ts) : '';
        }
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            return $this->addPost();
        }
        View::assign('data', []);
        View::assign('title', '添加用户');
        return $this->fetchWithLayout('member/add');
    }

    public function addPost(): Response
    {
        // 检查用户数限制
        $resourceCheck = $this->checkResourceLimit('user');
        if (!$resourceCheck['allowed']) {
            return $this->error($resourceCheck['msg']);
        }

        $tenantId = $this->getTenantId();
        $username = trim((string) $this->request->post('username', ''));
        $password = (string) $this->request->post('password', '');
        $nickname = trim((string) $this->request->post('nickname', ''));
        $mobile = trim((string) $this->request->post('mobile', ''));
        $email = trim((string) $this->request->post('email', ''));
        $status = (int) $this->request->post('status', 1);

        if (strlen($username) < 2 || strlen($username) > 50) {
            return $this->error('用户名长度 2-50');
        }
        if (strlen($password) < 6 || strlen($password) > 32) {
            return $this->error('密码长度 6-32');
        }
        if (UserModel::where('tenant_id', $tenantId)->where('username', $username)->find()) {
            return $this->error('该用户名已存在');
        }
        $now = time();
        UserModel::create([
            'tenant_id' => $tenantId,
            'username' => $username,
            'password' => $password,
            'nickname' => $nickname ?: $username,
            'mobile' => $mobile,
            'email' => $email,
            'status' => $status,
            'create_time' => $now,
            'update_time' => $now,
        ]);
        $user = UserModel::where('tenant_id', $tenantId)->where('username', $username)->find();
        $this->log('add', '添加C端用户:' . $username);
        return $this->success('添加成功', ['id' => $user->id]);
    }

    public function edit(): string|Response
    {
        if ($this->request->isPost()) {
            return $this->editPost();
        }
        $id = (int) $this->request->get('id');
        $data = UserModel::where('tenant_id', $this->getTenantId())->find($id);
        if (!$data) {
            return $this->error('记录不存在');
        }
        $data = $data->toArray();
        unset($data['password']);
        View::assign('data', $data);
        View::assign('title', '编辑用户');
        return $this->fetchWithLayout('member/edit');
    }

    public function editPost(): Response
    {
        $id = (int) $this->request->post('id');
        $row = UserModel::where('tenant_id', $this->getTenantId())->find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $nickname = trim((string) $this->request->post('nickname', ''));
        $mobile = trim((string) $this->request->post('mobile', ''));
        $email = trim((string) $this->request->post('email', ''));
        $status = (int) $this->request->post('status', 1);
        $password = (string) $this->request->post('password', '');
        $row->nickname = $nickname ?: $row->username;
        $row->mobile = $mobile;
        $row->email = $email;
        $row->status = $status;
        $row->update_time = time();
        if ($password !== '') {
            if (strlen($password) < 6 || strlen($password) > 32) {
                return $this->error('密码长度 6-32');
            }
            $row->password = password_hash($password, PASSWORD_BCRYPT);
        }
        $row->save();
        $this->log('edit', '编辑C端用户:id=' . $id);
        return $this->success('保存成功', ['id' => $id]);
    }

    public function del(): Response
    {
        $id = (int) $this->request->post('id');
        $row = UserModel::where('tenant_id', $this->getTenantId())->find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $row->delete();
        $this->log('del', '删除C端用户:id=' . $id);
        return $this->success('删除成功');
    }

    public function resetPwd(): Response
    {
        $id = (int) $this->request->post('id');
        $password = (string) $this->request->post('password', '123456');
        $row = UserModel::where('tenant_id', $this->getTenantId())->find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        if (strlen($password) < 6 || strlen($password) > 32) {
            return $this->error('密码长度 6-32');
        }
        $row->password = password_hash($password, PASSWORD_BCRYPT);
        $row->update_time = time();
        $row->save();
        $this->log('edit', '重置C端用户密码:id=' . $id);
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
