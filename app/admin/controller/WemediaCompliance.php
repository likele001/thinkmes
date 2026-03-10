<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\common\model\WemediaComplianceLogModel;
use think\facade\View;
use think\Response;

/**
 * 自媒体合规检测记录 - 后台管理（框架级独立应用，与租户无关）
 */
class WemediaCompliance extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax() && $this->request->get('limit') !== null) {
            return $this->listData();
        }
        View::assign('title', '合规记录');
        return $this->fetchWithLayout('wemedia/compliance_index');
    }

    private function listData(): Response
    {
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $page = max(1, (int) $this->request->get('page', 1));
        $query = WemediaComplianceLogModel::where('tenant_id', 0)->order('id', 'desc');
        $result = $this->request->get('result', '');
        if ($result !== '' && $result !== null) {
            $query->where('result', (int) $result);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['result_text'] = WemediaComplianceLogModel::resultText((int) ($row['result'] ?? 0));
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function del(): Response
    {
        $ids = $this->request->post('ids');
        if (is_array($ids)) $ids = array_filter(array_map('intval', $ids));
        else $ids = [(int) $ids];
        if (empty($ids)) return $this->error('请选择要删除的记录');
        WemediaComplianceLogModel::where('tenant_id', 0)->whereIn('id', $ids)->delete();
        return $this->success('删除成功');
    }
}
