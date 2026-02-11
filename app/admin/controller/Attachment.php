<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

/**
 * 文件管理（fa_upload 列表与删除）
 */
class Attachment extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '文件管理');
            return $this->fetchWithLayout('attachment/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $query = Db::name('upload')->order('id', 'desc');
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $ts = $row['create_time'] ?? null;
            $row['create_time'] = ($ts !== null && $ts !== '') ? date('Y-m-d H:i', (int) $ts) : '';
        }
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function del(): Response
    {
        $id = (int) $this->request->post('id');
        $row = Db::name('upload')->where('id', $id)->find();
        if (!$row) {
            return $this->error('记录不存在');
        }
        Db::name('upload')->where('id', $id)->delete();
        $admin = Session::get('admin_info');
        Db::name('log')->insert([
            'tenant_id' => $this->getTenantId(),
            'admin_id' => $admin['id'] ?? 0,
            'type' => 'del',
            'content' => '删除附件:id=' . $id,
            'url' => $this->request->url(),
            'ip' => $this->request->ip(),
            'create_time' => time(),
        ]);
        return $this->success('删除成功');
    }
}
