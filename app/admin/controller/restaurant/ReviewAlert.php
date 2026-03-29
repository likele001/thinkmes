<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\admin\model\restaurant\ReviewAlertModel;
use think\facade\View;
use think\Response;

class ReviewAlert extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '差评告警');
            return $this->fetchWithLayout('restaurant/review_alert/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $tenantId = $this->getTenantId();
        $query = ReviewAlertModel::with(['store'])->where('tenant_id', $tenantId)->order('review_time', 'desc')->order('id', 'desc');
        $status = $this->request->get('status');
        if ($status !== null && $status !== '') $query->where('status', (int) $status);
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['store_name'] = $row['store']['name'] ?? '-';
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function markDone(): Response
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        if ($id <= 0) return $this->error('参数错误');
        $row = ReviewAlertModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) return $this->error('记录不存在');
        $row->save(['status' => 1]);
        return $this->success('已处理');
    }
}

