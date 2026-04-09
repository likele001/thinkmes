<?php
declare(strict_types=1);
namespace app\api\controller\prompt;

use app\common\controller\BaseController;
use app\admin\model\prompt\CategoryModel;
use app\admin\model\prompt\TemplateModel;
use think\Response;

class Template extends BaseController
{
    /** 分类列表 */
    public function categories(): Response
    {
        $list = CategoryModel::where('status', 1)
            ->field('id, name, icon')
            ->order('sort asc, id asc')
            ->select()->toArray();
        return $this->success('', ['list' => $list]);
    }

    /** 模板列表（分页） */
    public function index(): Response
    {
        $page       = max(1, (int)$this->request->get('page', 1));
        $limit      = min(50, max(1, (int)$this->request->get('limit', 20)));
        $categoryId = (int)$this->request->get('category_id', 0);
        $keyword    = trim((string)$this->request->get('keyword', ''));

        $query = TemplateModel::where('status', 1)
            ->field('id, category_id, title, description, icon, use_count, variables, ext_variables, output_words');
        if ($categoryId > 0) $query->where('category_id', $categoryId);
        if ($keyword !== '') $query->whereLike('title|description', '%' . $keyword . '%');

        $total = $query->count();
        $list  = $query->order('sort asc, id asc')->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /** 模板详情（含 prompt_text） */
    public function detail(): Response
    {
        $id  = (int)$this->request->get('id', 0);
        $row = TemplateModel::where('id', $id)->where('status', 1)->find();
        if (!$row) return $this->error('模板不存在');
        return $this->success('', $row->toArray());
    }
}
