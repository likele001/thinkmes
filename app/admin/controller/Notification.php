<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

class Notification extends Backend
{
    protected function ensureTable(): void
    {
        $exists = false;
        try {
            $exists = $this->hasTableColumn('admin_notification', 'id');
        } catch (\Throwable $e) {
            $exists = false;
        }
        if ($exists) return;

        Db::execute(
            'CREATE TABLE IF NOT EXISTS `fa_admin_notification` ('
            . '`id` int(10) unsigned NOT NULL AUTO_INCREMENT,'
            . '`tenant_id` int(10) unsigned NOT NULL DEFAULT 0,'
            . '`admin_id` int(10) unsigned NOT NULL DEFAULT 0,'
            . '`title` varchar(255) NOT NULL DEFAULT "",'
            . '`content` text NULL,'
            . '`level` varchar(20) NOT NULL DEFAULT "info",'
            . '`is_read` tinyint(1) NOT NULL DEFAULT 0,'
            . '`read_time` int(10) unsigned NOT NULL DEFAULT 0,'
            . '`create_time` int(10) unsigned NOT NULL DEFAULT 0,'
            . 'PRIMARY KEY (`id`),'
            . 'KEY `idx_tenant_admin` (`tenant_id`,`admin_id`),'
            . 'KEY `idx_read` (`tenant_id`,`is_read`,`create_time`)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
        );
    }

    public function index(): string|Response
    {
        $this->ensureTable();
        $limitParam = $this->request->get('limit');
        $offsetParam = $this->request->get('offset');
        $isDataRequest = ($limitParam !== null && $limitParam !== '') || ($offsetParam !== null && $offsetParam !== '');
        if (!$isDataRequest && !$this->request->isAjax()) {
            View::assign('title', '消息通知');
            return $this->fetchWithLayout('notification/index');
        }

        $tenantId = $this->getTenantId();
        $admin = Session::get('admin_info');
        $adminId = (int) ($admin['id'] ?? 0);

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $isRead = $this->request->get('is_read', '');
        $level = trim((string) $this->request->get('level', ''));
        $kw = trim((string) $this->request->get('kw', ''));

        $query = Db::name('admin_notification')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($adminId) {
                $q->where('admin_id', 0)->whereOr('admin_id', $adminId);
            })
            ->order('id', 'desc');

        if ($isRead !== '' && $isRead !== null) {
            $query->where('is_read', (int) $isRead);
        }
        if ($level !== '') {
            $query->where('level', $level);
        }
        if ($kw !== '') {
            $query->whereLike('title|content', '%' . $kw . '%');
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['create_time_text'] = !empty($row['create_time']) ? date('Y-m-d H:i:s', (int) $row['create_time']) : '';
            $row['read_time_text'] = !empty($row['read_time']) ? date('Y-m-d H:i:s', (int) $row['read_time']) : '';
        }
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function read(): Response
    {
        $this->ensureTable();
        if (!$this->request->isPost()) return $this->error('非法请求');
        $tenantId = $this->getTenantId();
        $admin = Session::get('admin_info');
        $adminId = (int) ($admin['id'] ?? 0);
        $ids = $this->request->post('ids');
        $ids = is_array($ids) ? $ids : explode(',', (string) $ids);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) return $this->error('请选择记录');
        $now = time();
        $affected = Db::name('admin_notification')
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $ids)
            ->where(function ($q) use ($adminId) {
                $q->where('admin_id', 0)->whereOr('admin_id', $adminId);
            })
            ->update(['is_read' => 1, 'read_time' => $now]);
        return $this->success('已标记已读', ['affected' => $affected]);
    }

    public function del(): Response
    {
        $this->ensureTable();
        if (!$this->request->isPost()) return $this->error('非法请求');
        $tenantId = $this->getTenantId();
        $admin = Session::get('admin_info');
        $adminId = (int) ($admin['id'] ?? 0);
        $ids = $this->request->post('ids');
        $ids = is_array($ids) ? $ids : explode(',', (string) $ids);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) return $this->error('请选择记录');
        $deleted = Db::name('admin_notification')
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $ids)
            ->where(function ($q) use ($adminId) {
                $q->where('admin_id', 0)->whereOr('admin_id', $adminId);
            })
            ->delete();
        return $this->success('已删除', ['deleted' => $deleted]);
    }

    public function pushTest(): Response
    {
        $this->ensureTable();
        if (!$this->request->isPost()) return $this->error('非法请求');
        $tenantId = $this->getTenantId();
        $admin = Session::get('admin_info');
        $adminId = (int) ($admin['id'] ?? 0);
        $title = trim((string) $this->request->post('title', ''));
        $content = trim((string) $this->request->post('content', ''));
        $level = trim((string) $this->request->post('level', 'info'));
        if ($title === '') return $this->error('标题不能为空');
        if ($level === '') $level = 'info';
        Db::name('admin_notification')->insert([
            'tenant_id' => $tenantId,
            'admin_id' => $adminId,
            'title' => $title,
            'content' => $content,
            'level' => $level,
            'is_read' => 0,
            'read_time' => 0,
            'create_time' => time(),
        ]);
        return $this->success('已发送');
    }
}
