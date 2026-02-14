<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\RoleModel;
use app\admin\model\AuthRuleModel;
use app\common\lib\Auth;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

class Role extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '角色管理');
            return $this->fetchWithLayout('role/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $query = RoleModel::order('id', 'asc');
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        try {
            $titleMap = Db::name('auth_rule')->column('title', 'id');
        } catch (\Throwable $e) {
            $titleMap = [];
        }
        foreach ($list as &$row) {
            $ids = array_filter(array_map('intval', explode(',', (string) ($row['rules'] ?? ''))));
            $names = array_map(function($id) use ($titleMap) { return $titleMap[$id] ?? ('#' . $id); }, $ids);
            $row['rules_names'] = $names;
        }
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            return $this->addPost();
        }
        $rules = (new AuthRuleModel())->getTree(0, false);
        View::assign('rules', $rules);
        View::assign('rulesJson', json_encode($rules, JSON_UNESCAPED_UNICODE));
        View::assign('data', []);
        View::assign('title', '添加角色');
        return $this->fetchWithLayout('role/add');
    }

    public function addPost(): Response
    {
        $name = trim((string) $this->request->post('name'));
        $rules = trim((string) $this->request->post('rules', ''));
        $status = (int) $this->request->post('status', 1);
        if ($name === '') {
            return $this->error('角色名称不能为空');
        }
        $role = new RoleModel();
        $role->name = $name;
        $role->rules = $rules;
        $role->status = $status;
        $role->create_time = time();
        $role->update_time = time();
        $role->save();
        $this->log('add', '添加角色:' . $name);
        return $this->success('添加成功', ['id' => $role->id]);
    }

    public function edit(): string|Response
    {
        if ($this->request->isPost()) {
            return $this->editPost();
        }
        $id = (int) $this->request->get('id');
        $data = RoleModel::find($id);
        if (!$data) {
            return $this->error('记录不存在');
        }
        $rules = (new AuthRuleModel())->getTree(0, false);
        View::assign('rules', $rules);
        View::assign('rulesJson', json_encode($rules, JSON_UNESCAPED_UNICODE));
        View::assign('data', $data->toArray());
        View::assign('title', '编辑角色');
        return $this->fetchWithLayout('role/edit');
    }

    public function editPost(): Response
    {
        $id = (int) $this->request->post('id');
        $role = RoleModel::find($id);
        if (!$role) {
            return $this->error('记录不存在');
        }
        $role->name = trim((string) $this->request->post('name'));
        $role->rules = trim((string) $this->request->post('rules', ''));
        $role->status = (int) $this->request->post('status', 1);
        $role->update_time = time();
        $role->save();
        (new Auth())->clearAllCache();
        $this->log('edit', '编辑角色:id=' . $id);
        return $this->success('保存成功', ['id' => $id]);
    }

    public function del(): Response
    {
        $id = (int) $this->request->post('id');
        if ($id === 1) {
            return $this->error('不能删除超级管理员角色');
        }
        $role = RoleModel::find($id);
        if (!$role) {
            return $this->error('记录不存在');
        }
        $role->delete();
        (new Auth())->clearAllCache();
        $this->log('del', '删除角色:id=' . $id);
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
