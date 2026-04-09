<?php
declare(strict_types=1);
namespace app\admin\controller\prompt;

use app\admin\controller\Backend;
use app\admin\model\prompt\CategoryModel;
use think\facade\View;
use think\Response;

class Category extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            [$limit, $page] = $this->getPaginationParams();
            $query = CategoryModel::order('sort asc, id asc');
            $keyword = trim((string)$this->request->get('keyword', ''));
            if ($keyword !== '') $query->whereLike('name', '%' . $keyword . '%');
            $total = $query->count();
            $list  = $query->page($page, $limit)->select()->toArray();
            return $this->success('', ['total' => $total, 'rows' => $list]);
        }
        return $this->fetchWithLayout('prompt/category/index');
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['create_time'] = $data['update_time'] = time();
            CategoryModel::create($data);
            return $this->success('添加成功');
        }
        return $this->fetchWithLayout('prompt/category/add');
    }

    public function edit(): string|Response
    {
        $id = (int)$this->request->param('id', 0);
        $row = CategoryModel::find($id);
        if (!$row) return $this->error('记录不存在');
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['update_time'] = time();
            unset($data['id']);
            CategoryModel::where('id', $id)->update($data);
            return $this->success('保存成功');
        }
        View::assign('row', $row->toArray());
        return $this->fetchWithLayout('prompt/category/edit');
    }

    public function del(): Response
    {
        $ids = (array)$this->request->post('ids', []);
        if (empty($ids)) return $this->error('请选择要删除的记录');
        CategoryModel::whereIn('id', $ids)->delete();
        return $this->success('删除成功');
    }
}
