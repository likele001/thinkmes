<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\common\model\WemediaVideoScriptModel;
use think\facade\View;
use think\Response;

/**
 * 自媒体短视频 - 后台管理（框架级独立应用，与租户无关）
 */
class WemediaVideo extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax() && $this->request->get('limit') !== null) {
            return $this->listData();
        }
        View::assign('title', '短视频管理');
        return $this->fetchWithLayout('wemedia/video_index');
    }

    private function listData(): Response
    {
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $page = max(1, (int) $this->request->get('page', 1));
        $query = WemediaVideoScriptModel::where('tenant_id', 0)->order('id', 'desc');
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['status_text'] = WemediaVideoScriptModel::statusText((int) ($row['status'] ?? 0));
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function del(): Response
    {
        $ids = $this->request->post('ids');
        $ids = is_array($ids) ? array_filter(array_map('intval', $ids)) : [(int) $ids];
        if (empty($ids)) return $this->error('请选择要删除的记录');
        WemediaVideoScriptModel::where('tenant_id', 0)->whereIn('id', $ids)->delete();
        return $this->success('删除成功');
    }
}
