<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\{$TableName}Model;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

class {$TableName} extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '{$TableName}管理');
            return $this->fetchWithLayout('{$table}/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $query = {$TableName}Model::order('id', 'desc');
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            return $this->addPost();
        }
        View::assign('data', []);
        View::assign('title', '添加{$TableName}');
        return $this->fetchWithLayout('{$table}/add');
    }

    public function addPost(): Response
    {
        $data = $this->request->post();
        $data['create_time'] = $data['update_time'] = time();
        {$TableName}Model::create($data);
        $this->log('add', '添加{$TableName}');
        return $this->success('添加成功');
    }

    public function edit(): string|Response
    {
        if ($this->request->isPost()) {
            return $this->editPost();
        }
        $id = (int) $this->request->get('id');
        $data = {$TableName}Model::find($id);
        if (!$data) {
            return $this->error('记录不存在');
        }
        View::assign('data', $data->toArray());
        View::assign('title', '编辑{$TableName}');
        return $this->fetchWithLayout('{$table}/edit');
    }

    public function editPost(): Response
    {
        $id = (int) $this->request->post('id');
        $row = {$TableName}Model::find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $data = $this->request->post();
        unset($data['id']);
        $data['update_time'] = time();
        $row->save($data);
        $this->log('edit', '编辑{$TableName}:id=' . $id);
        return $this->success('保存成功');
    }

    public function del(): Response
    {
        $id = (int) $this->request->post('id');
        $row = {$TableName}Model::find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $row->delete();
        $this->log('del', '删除{$TableName}:id=' . $id);
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
