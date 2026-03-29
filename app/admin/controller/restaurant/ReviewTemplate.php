<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\admin\model\restaurant\ReviewReplyTemplateModel;
use think\facade\View;
use think\Response;

class ReviewTemplate extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '评价回复模板');
            return $this->fetchWithLayout('restaurant/review_template/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $tenantId = $this->getTenantId();
        $query = ReviewReplyTemplateModel::where('tenant_id', $tenantId)->order('id', 'desc');
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        $tenantId = $this->getTenantId();
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['template'])) return $this->error('请填写模板内容');
            $data = [
                'tenant_id' => $tenantId,
                'platform' => trim((string) ($params['platform'] ?? '')),
                'scene' => trim((string) ($params['scene'] ?? 'good')) ?: 'good',
                'rating_min' => (int) ($params['rating_min'] ?? 0),
                'rating_max' => (int) ($params['rating_max'] ?? 5),
                'template' => (string) ($params['template'] ?? ''),
                'status' => isset($params['status']) ? (int) $params['status'] : 1,
                'create_time' => time(),
                'update_time' => time(),
            ];
            try {
                ReviewReplyTemplateModel::create($data);
                return $this->success('添加成功');
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        View::assign('title', '添加回复模板');
        return $this->fetchWithLayout('restaurant/review_template/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) return $this->error('参数错误');
        $tenantId = $this->getTenantId();
        $row = ReviewReplyTemplateModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) return $this->error('记录不存在');
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['template'])) return $this->error('请填写模板内容');
            $data = [
                'platform' => trim((string) ($params['platform'] ?? '')),
                'scene' => trim((string) ($params['scene'] ?? 'good')) ?: 'good',
                'rating_min' => (int) ($params['rating_min'] ?? 0),
                'rating_max' => (int) ($params['rating_max'] ?? 5),
                'template' => (string) ($params['template'] ?? ''),
                'status' => isset($params['status']) ? (int) $params['status'] : (int) $row->status,
                'update_time' => time(),
            ];
            try {
                $row->save($data);
                return $this->success('保存成功');
            } catch (\Throwable $e) {
                return $this->error('保存失败：' . $e->getMessage());
            }
        }
        View::assign('row', $row);
        View::assign('title', '编辑回复模板');
        return $this->fetchWithLayout('restaurant/review_template/edit');
    }

    public function del(): Response
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $ids = $this->request->post('ids');
        if (empty($ids)) return $this->error('参数错误');
        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : explode(',', (string) $ids);
        $count = 0;
        foreach ($idsArr as $id) {
            $row = ReviewReplyTemplateModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }
}

