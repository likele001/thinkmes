<?php
declare(strict_types=1);
namespace app\admin\controller\prompt;

use app\admin\controller\Backend;
use app\admin\model\prompt\TemplateModel;
use app\admin\model\prompt\CategoryModel;
use think\facade\View;
use think\Response;

class Template extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            [$limit, $page] = $this->getPaginationParams();
            $query = TemplateModel::with('category')->order('sort asc, id asc');
            $keyword    = trim((string)$this->request->get('keyword', ''));
            $categoryId = (int)$this->request->get('category_id', 0);
            if ($keyword !== '') $query->whereLike('title|description', '%' . $keyword . '%');
            if ($categoryId > 0) $query->where('category_id', $categoryId);
            $total = $query->count();
            $list  = $query->page($page, $limit)->select()->toArray();
            return $this->success('', ['total' => $total, 'rows' => $list]);
        }
        $categories = CategoryModel::where('status', 1)->order('sort asc')->select()->toArray();
        View::assign('categories', $categories);
        return $this->fetchWithLayout('prompt/template/index');
    }

    public function add(): string|Response
    {
        $categories = CategoryModel::where('status', 1)->order('sort asc')->select()->toArray();
        View::assign('categories', $categories);
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (isset($data['variables']) && is_string($data['variables'])) {
                // 保持字符串
            }
            $data['create_time'] = $data['update_time'] = time();
            TemplateModel::create($data);
            return $this->success('添加成功');
        }
        return $this->fetchWithLayout('prompt/template/add');
    }

    public function edit(): string|Response
    {
        $id  = (int)$this->request->param('id', 0);
        $row = TemplateModel::find($id);
        if (!$row) return $this->error('记录不存在');
        $categories = CategoryModel::where('status', 1)->order('sort asc')->select()->toArray();
        View::assign('categories', $categories);
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['update_time'] = time();
            unset($data['id']);
            TemplateModel::where('id', $id)->update($data);
            return $this->success('保存成功');
        }
        $arr = $row->toArray();
        // variables 保持 JSON 字符串以便前端 textarea 显示
        if (is_array($arr['variables'])) {
            $arr['variables'] = json_encode($arr['variables'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        if (isset($arr['ext_variables']) && is_array($arr['ext_variables'])) {
            $arr['ext_variables'] = json_encode($arr['ext_variables'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        View::assign('row', $arr);
        return $this->fetchWithLayout('prompt/template/edit');
    }

    public function del(): Response
    {
        $ids = (array)$this->request->post('ids', []);
        if (empty($ids)) return $this->error('请选择要删除的记录');
        TemplateModel::whereIn('id', $ids)->delete();
        return $this->success('删除成功');
    }
}
