<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\AuthRuleModel;
use app\common\lib\Auth;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

class AuthRule extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', __("title"));
            return $this->fetchWithLayout('auth_rule/index');
        }
        // AJAX请求返回树形数据（用于前端表格显示）
        $list = (new AuthRuleModel())->getTree(0, false);
        return $this->success('', $list);
    }

    public function tree(): Response
    {
        $list = (new AuthRuleModel())->getTree(0, false);
        return $this->success('', $list);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            return $this->addPost();
        }
        $pid = (int) $this->request->get('pid', 0);
        $parents = (new AuthRuleModel())->getTree(0, false);
        View::assign('parentOptions', $this->buildParentOptions($parents, null));
        View::assign('data', ['pid' => $pid, 'type' => 1, 'ismenu' => 1, 'status' => 1, 'sort' => 0]);
        View::assign('title', __("add_title"));
        return $this->fetchWithLayout('auth_rule/add');
    }

    /**
     * 将树形规则转为父级下拉选项（主菜单 + 各级菜单），编辑时排除当前节点及其子节点
     * @param array $tree getTree 返回的树
     * @param int|null $excludeId 编辑时传入当前规则 id，不列入选项且不递归其子节点
     * @return array [['id'=>0,'title'=>'主菜单'], ['id'=>1,'title'=>'  └ 标题'], ...]
     */
    private function buildParentOptions(array $tree, ?int $excludeId, int $level = 0): array
    {
        $options = [];
        if ($level === 0) {
            $options[] = ['id' => 0, 'title' => __('parent_main_menu'), 'level' => 0];
        }
        $prefix = str_repeat('　', $level) . ($level > 0 ? '└ ' : '');
        foreach ($tree as $item) {
            $id = (int) ($item['id'] ?? 0);
            if ($id === $excludeId) {
                continue;
            }
            $title = $prefix . ($item['title'] ?? $item['name'] ?? '');
            $options[] = ['id' => $id, 'title' => $title, 'level' => $level];
            $children = $item['children'] ?? [];
            if (!empty($children)) {
                $options = array_merge(
                    $options,
                    $this->buildParentOptions($children, $excludeId, $level + 1)
                );
            }
        }
        return $options;
    }

    public function addPost(): Response
    {
        $name = trim((string) $this->request->post('name'));
        $title = trim((string) $this->request->post('title'));
        $type = (int) $this->request->post('type', 1);
        $ismenu = (int) $this->request->post('ismenu', 1);
        $status = (int) $this->request->post('status', 1);
        $pid = (int) $this->request->post('pid', 0);
        $icon = trim((string) $this->request->post('icon', ''));
        $sort = (int) $this->request->post('sort', 0);

        if ($name === '') {
            return $this->error('规则标识不能为空');
        }
        $name = strtolower($name);
        if (AuthRuleModel::where('name', $name)->find()) {
            return $this->error('规则标识已存在');
        }

        $rule = new AuthRuleModel();
        $rule->name = $name;
        $rule->title = $title ?: $name;
        $rule->type = $type;
        $rule->ismenu = $ismenu;
        $rule->status = $status;
        $rule->pid = $pid;
        $rule->icon = $icon;
        $rule->sort = $sort;
        $rule->create_time = time();
        $rule->update_time = time();
        $rule->save();

        (new Auth())->clearAllCache();
        $this->log('add', '添加规则:' . $name);
        return $this->success('添加成功');
    }

    public function edit(): string|Response
    {
        if ($this->request->isPost()) {
            return $this->editPost();
        }
        $id = (int) $this->request->get('id');
        $data = AuthRuleModel::find($id);
        if (!$data) {
            return $this->error('记录不存在');
        }
        $parents = (new AuthRuleModel())->getTree(0, false);
        View::assign('parentOptions', $this->buildParentOptions($parents, $id));
        View::assign('data', $data->toArray());
        View::assign('title', __("edit_title"));
        return $this->fetchWithLayout('auth_rule/edit');
    }

    public function editPost(): Response
    {
        $id = (int) $this->request->post('id');
        $rule = AuthRuleModel::find($id);
        if (!$rule) {
            return $this->error('记录不存在');
        }
        $name = trim((string) $this->request->post('name'));
        $title = trim((string) $this->request->post('title'));
        $type = (int) $this->request->post('type', 1);
        $ismenu = (int) $this->request->post('ismenu', 1);
        $status = (int) $this->request->post('status', 1);
        $pid = (int) $this->request->post('pid', 0);
        $icon = trim((string) $this->request->post('icon', ''));
        $sort = (int) $this->request->post('sort', 0);

        if ($name === '') {
            return $this->error('规则标识不能为空');
        }
        $name = strtolower($name);
        $exists = AuthRuleModel::where('name', $name)->where('id', '<>', $id)->find();
        if ($exists) {
            return $this->error('规则标识已存在');
        }

        $rule->name = $name;
        $rule->title = $title ?: $name;
        $rule->type = $type;
        $rule->ismenu = $ismenu;
        $rule->status = $status;
        $rule->pid = $pid;
        $rule->icon = $icon;
        $rule->sort = $sort;
        $rule->update_time = time();
        $rule->save();

        (new Auth())->clearAllCache();
        $this->log('edit', '编辑规则:id=' . $id);
        return $this->success('保存成功', ['id' => $id]);
    }

    public function del(): Response
    {
        $id = (int) $this->request->post('id');
        $rule = AuthRuleModel::find($id);
        if (!$rule) {
            return $this->error('记录不存在');
        }
        $child = AuthRuleModel::where('pid', $id)->find();
        if ($child) {
            return $this->error('请先删除子级规则');
        }
        $rule->delete();
        (new Auth())->clearAllCache();
        $this->log('del', '删除规则:id=' . $id);
        return $this->success('删除成功');
    }

    protected function log(string $type, string $content): void
    {
        $admin = Session::get('admin_info');
        Db::name('log')->insert([
            'tenant_id' => $this->getTenantId(),
            'admin_id' => $admin['id'] ?? 0,
            'type' => $type,
            'content' => $content,
            'url' => $this->request->url(),
            'ip' => $this->request->ip(),
            'create_time' => time(),
        ]);
    }
}
