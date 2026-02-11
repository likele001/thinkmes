<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\TenantPackageModel;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

/**
 * 租户套餐管理（仅平台超管 tenant_id=0 可访问）
 */
class TenantPackage extends Backend
{
    public function index(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理套餐');
        }
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '套餐管理');
            return $this->fetchWithLayout('tenant_package/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $name = trim((string) $this->request->get('name'));

        $query = TenantPackageModel::order('sort')->order('id');
        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['expire_days_text'] = ($row['expire_days'] ?? null) && $row['expire_days'] > 0 ? $row['expire_days'] . '天' : '永久';
        }
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理套餐');
        }
        if ($this->request->isPost()) {
            return $this->addPost();
        }
        View::assign('data', []);
        View::assign('title', '添加套餐');
        return $this->fetchWithLayout('tenant_package/add');
    }

    public function addPost(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理套餐');
        }
        $name = trim((string) $this->request->post('name'));
        $maxAdmin = max(0, (int) $this->request->post('max_admin', 10));
        $maxUser = max(0, (int) $this->request->post('max_user', 1000));
        $expireDays = $this->request->post('expire_days');
        $sort = (int) $this->request->post('sort', 0);
        
        if (strlen($name) < 1) {
            return $this->error('套餐名称不能为空');
        }
        if (TenantPackageModel::where('name', $name)->find()) {
            return $this->error('套餐名称已存在');
        }
        
        $expireDaysInt = null;
        if ($expireDays !== '' && $expireDays !== null) {
            $expireDaysInt = max(1, (int) $expireDays);
        }
        
        $now = time();
        $pkg = TenantPackageModel::create([
            'name' => $name,
            'max_admin' => $maxAdmin,
            'max_user' => $maxUser,
            'expire_days' => $expireDaysInt,
            'sort' => $sort,
            'create_time' => $now,
            'update_time' => $now,
        ]);
        $this->log('add', '添加套餐:' . $name);
        return $this->success('添加成功', ['id' => $pkg->id]);
    }

    public function edit(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理套餐');
        }
        if ($this->request->isPost()) {
            return $this->editPost();
        }
        $id = (int) $this->request->get('id');
        $data = TenantPackageModel::find($id);
        if (!$data) {
            return $this->error('记录不存在');
        }
        View::assign('data', $data->toArray());
        View::assign('title', '编辑套餐');
        return $this->fetchWithLayout('tenant_package/edit');
    }

    public function editPost(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理套餐');
        }
        $id = (int) $this->request->post('id');
        $row = TenantPackageModel::find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $name = trim((string) $this->request->post('name'));
        $maxAdmin = max(0, (int) $this->request->post('max_admin', 10));
        $maxUser = max(0, (int) $this->request->post('max_user', 1000));
        $expireDays = $this->request->post('expire_days');
        $sort = (int) $this->request->post('sort', 0);
        
        if (strlen($name) < 1) {
            return $this->error('套餐名称不能为空');
        }
        if (TenantPackageModel::where('name', $name)->where('id', '<>', $id)->find()) {
            return $this->error('套餐名称已存在');
        }
        
        $expireDaysInt = null;
        if ($expireDays !== '' && $expireDays !== null) {
            $expireDaysInt = max(1, (int) $expireDays);
        }
        
        $row->name = $name;
        $row->max_admin = $maxAdmin;
        $row->max_user = $maxUser;
        $row->expire_days = $expireDaysInt;
        $row->sort = $sort;
        $row->update_time = time();
        $row->save();
        $this->log('edit', '编辑套餐:id=' . $id);
        return $this->success('保存成功', ['id' => $id]);
    }

    public function del(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理套餐');
        }
        $id = (int) $this->request->post('id');
        $row = TenantPackageModel::find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        // 检查是否有租户使用此套餐
        $tenantCount = Db::name('tenant')->where('package_id', $id)->count();
        if ($tenantCount > 0) {
            return $this->error('该套餐正在被 ' . $tenantCount . ' 个租户使用，无法删除');
        }
        $row->delete();
        $this->log('del', '删除套餐:id=' . $id);
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
