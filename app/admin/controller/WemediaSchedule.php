<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\common\model\WemediaScheduleModel;
use think\facade\View;
use think\Response;

/**
 * 自媒体排期 - 后台管理（框架级独立应用，与租户无关）
 */
class WemediaSchedule extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax() && $this->request->get('limit') !== null) {
            return $this->listData();
        }
        View::assign('title', '排期管理');
        return $this->fetchWithLayout('wemedia/schedule_index');
    }

    private function listData(): Response
    {
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $page = max(1, (int) $this->request->get('page', 1));
        $query = WemediaScheduleModel::where('tenant_id', 0)->order('plan_time', 'desc');
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['status_text'] = WemediaScheduleModel::statusText((int) ($row['status'] ?? 0));
            $row['relate_type_text'] = WemediaScheduleModel::relateTypeText($row['relate_type'] ?? '');
            $row['plan_time_text'] = $row['plan_time'] ? date('Y-m-d H:i', (int) $row['plan_time']) : '-';
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function del(): Response
    {
        $ids = $this->request->post('ids');
        $ids = is_array($ids) ? array_filter(array_map('intval', $ids)) : [(int) $ids];
        if (empty($ids)) return $this->error('请选择要删除的记录');
        WemediaScheduleModel::where('tenant_id', 0)->whereIn('id', $ids)->delete();
        return $this->success('删除成功');
    }
}
