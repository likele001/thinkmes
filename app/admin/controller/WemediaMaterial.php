<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\common\model\WemediaMaterialModel;
use think\facade\View;
use think\Response;

/**
 * 自媒体素材 - 后台管理（框架级独立应用，与租户无关）
 */
class WemediaMaterial extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax() && $this->request->get('limit') !== null) {
            return $this->listData();
        }
        View::assign('title', '素材管理');
        return $this->fetchWithLayout('wemedia/material_index');
    }

    private function listData(): Response
    {
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $page = max(1, (int) $this->request->get('page', 1));
        $query = WemediaMaterialModel::where('tenant_id', 0)->order('id', 'desc');
        $type = trim((string) $this->request->get('type', ''));
        if ($type !== '') $query->where('type', $type);
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['type_text'] = WemediaMaterialModel::typeText($row['type'] ?? '');
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function del(): Response
    {
        $ids = $this->request->post('ids');
        $ids = is_array($ids) ? array_filter(array_map('intval', $ids)) : [(int) $ids];
        if (empty($ids)) return $this->error('请选择要删除的记录');
        WemediaMaterialModel::where('tenant_id', 0)->whereIn('id', $ids)->delete();
        return $this->success('删除成功');
    }
}
