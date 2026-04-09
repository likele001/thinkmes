<?php
declare(strict_types=1);
namespace app\admin\controller\prompt;

use app\admin\controller\Backend;
use app\admin\model\prompt\GenerationModel;
use think\Response;

class Generation extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            [$limit, $page] = $this->getPaginationParams();
            $query = GenerationModel::order('id desc');
            $userId   = (int)$this->request->get('user_id', 0);
            $keyword  = trim((string)$this->request->get('keyword', ''));
            $status   = $this->request->get('status', '');
            if ($userId > 0)    $query->where('user_id', $userId);
            if ($keyword !== '') $query->whereLike('template_title|input_text', '%' . $keyword . '%');
            if ($status !== '')  $query->where('status', (int)$status);
            $total = $query->count();
            $list  = $query->page($page, $limit)->select()->toArray();
            return $this->success('', ['total' => $total, 'rows' => $list]);
        }
        return $this->fetchWithLayout('prompt/generation/index');
    }

    public function del(): Response
    {
        $ids = (array)$this->request->post('ids', []);
        if (empty($ids)) return $this->error('请选择要删除的记录');
        GenerationModel::whereIn('id', $ids)->delete();
        return $this->success('删除成功');
    }
}
