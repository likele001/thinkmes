<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\LogModel;
use think\facade\Session;
use think\facade\View;
use think\Response;

class Log extends Backend
{
    public function index(): string|Response
    {
        // 带 limit 或 offset 的请求一律当表格数据接口，返回 JSON（避免列表页拿到 HTML 显示“无数据”）
        $limitParam = $this->request->get('limit');
        $offsetParam = $this->request->get('offset');
        $isDataRequest = ($limitParam !== null && $limitParam !== '') || ($offsetParam !== null && $offsetParam !== '');
        if (!$isDataRequest && !$this->request->isAjax()) {
            View::assign('title', '操作日志');
            return $this->fetchWithLayout('log/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $type = trim((string) $this->request->get('type'));
        $adminId = $this->request->get('admin_id');

        $tenantId = $this->getTenantId();
        $query = LogModel::where('tenant_id', $tenantId)->order('id', 'desc');
        $scopeIds = $this->getDataScopeAdminIds();
        if ($scopeIds !== null) {
            $query->whereIn('admin_id', $scopeIds);
        }
        if ($type !== '') {
            $query->where('type', $type);
        }
        if ($adminId !== '' && $adminId !== null) {
            $query->where('admin_id', (int) $adminId);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $ts = $row['create_time'] ?? null;
            $row['create_time'] = ($ts !== null && $ts !== '') ? date('Y-m-d H:i:s', (int) $ts) : '';
        }
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 导出日志为 CSV（按当前筛选条件，最多 10000 条）
     */
    public function export(): Response
    {
        $tenantId = $this->getTenantId();
        $query = LogModel::where('tenant_id', $tenantId)->order('id', 'desc');
        $scopeIds = $this->getDataScopeAdminIds();
        if ($scopeIds !== null) {
            $query->whereIn('admin_id', $scopeIds);
        }
        $type = trim((string) $this->request->get('type'));
        $adminId = $this->request->get('admin_id');
        if ($type !== '') {
            $query->where('type', $type);
        }
        if ($adminId !== '' && $adminId !== null) {
            $query->where('admin_id', (int) $adminId);
        }
        $list = $query->limit(10000)->select()->toArray();

        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv .= "ID,租户ID,管理员ID,类型,内容,URL,IP,创建时间\n";
        foreach ($list as $row) {
            $ts = $row['create_time'] ?? null;
            $time = ($ts !== null && $ts !== '') ? date('Y-m-d H:i:s', (int) $ts) : '';
            $csv .= implode(',', [
                $row['id'] ?? '',
                $row['tenant_id'] ?? '',
                $row['admin_id'] ?? '',
                '"' . str_replace('"', '""', (string) ($row['type'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string) ($row['content'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string) ($row['url'] ?? '')) . '"',
                '"' . str_replace('"', '""', (string) ($row['ip'] ?? '')) . '"',
                $time,
            ]) . "\n";
        }

        $filename = 'log_export_' . date('YmdHis') . '.csv';
        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
