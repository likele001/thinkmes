<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\AdminModel;
use app\admin\model\TenantModel;
use app\common\lib\Auth;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

class Profile extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $admin = Session::get('admin_info');
            $tenantId = $this->getTenantId();
            $adminId = (int) ($admin['id'] ?? 0);
            $adminRow = $adminId > 0 ? AdminModel::find($adminId) : null;
            $adminData = $adminRow ? $adminRow->toArray() : [];
            unset($adminData['password'], $adminData['salt']);
            $tenantRow = $tenantId > 0 ? TenantModel::find($tenantId) : null;
            $tenantData = $tenantRow ? $tenantRow->toArray() : null;
            if ($tenantData) {
                $ts = $tenantData['expire_time'] ?? null;
                $tenantData['expire_time_text'] = ($ts !== null && $ts > 0) ? date('Y-m-d', (int) $ts) : '永久';
            }
            View::assign('admin', $adminData);
            View::assign('tenant', $tenantData);
            View::assign('title', '个人中心');
            return $this->fetchWithLayout('profile/index');
        }
        return $this->success('', []);
    }

    public function updateProfile(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $admin = Session::get('admin_info');
        $id = (int) ($admin['id'] ?? 0);
        if ($id <= 0) {
            return $this->error('未登录');
        }
        $nickname = trim((string) $this->request->post('nickname', ''));
        $password = (string) $this->request->post('password', '');
        $row = AdminModel::find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        if ($nickname !== '') {
            $row->nickname = $nickname;
        }
        if ($password !== '') {
            if (strlen($password) < 6 || strlen($password) > 32) {
                return $this->error('密码长度 6-32');
            }
            $row->password = $password;
        }
        $row->update_time = time();
        $row->save();
        (new Auth())->clearCache($id);
        $this->log('edit', '更新个人资料:id=' . $id);
        return $this->success('保存成功');
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
