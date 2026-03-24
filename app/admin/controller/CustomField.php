<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\CustomFieldGroup;
use app\admin\model\CustomField as CustomFieldModel;
use app\admin\model\CustomFieldValue;
use think\facade\View;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class CustomField extends Backend
{
    protected ?CustomFieldModel $model = null;
    private const ALLOWED_FIELD_TYPES = [
        'text', 'number', 'select', 'radio', 'checkbox', 'date', 'datetime',
        'textarea', 'editor', 'image', 'file', 'switch',
    ];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new CustomFieldModel();
    }

    public function index(): string|Json
    {
        if ($this->request->isAjax()) {
            $page = $this->request->get('page/d', 1);
            $limit = $this->request->get('limit/d', 10);
            $sort = $this->request->get('sort', 'id');
            $order = $this->request->get('order', 'desc');
            $search = $this->request->get('search', '');

            $where = [];
            if ($search) {
                $where[] = ['title|name', 'like', '%' . $search . '%'];
            }

            $list = $this->model
                ->with(['group'])
                ->where($where)
                ->order($sort, $order)
                ->paginate([
                    'list_rows' => $limit,
                    'page'      => $page,
                ]);

            return json([
                'code'  => 0,
                'msg'   => '',
                'count' => $list->total(),
                'data'  => $list->items(),
            ]);
        }

        return $this->fetchWithLayout('custom_field/index');
    }

    public function add(): string|Json
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a') ?: [];
            try {
                validate([
                    'group_id' => 'require',
                    'name'     => 'require|alphaNum',
                    'title'    => 'require',
                    'type'     => 'require',
                ])->check($params);

                $params = $this->normalizeAndValidateFieldParams($params);
                $params['tenant_id'] = $this->auth->tenant_id ?? 0;
                $this->model->save($params);
                return $this->success('添加成功');
            } catch (ValidateException $e) {
                return $this->error($e->getMessage());
            }
        }

        $groupList = CustomFieldGroup::where('status', 1)->select();
        View::assign('groupList', $groupList);
        return $this->fetchWithLayout('custom_field/add');
    }

    public function edit($id = null): string|Json
    {
        $row = $this->model->find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a') ?: [];
            try {
                validate([
                    'group_id' => 'require',
                    'name'     => 'require|alphaNum',
                    'title'    => 'require',
                    'type'     => 'require',
                ])->check($params);

                $params = $this->normalizeAndValidateFieldParams($params);
                $row->save($params);
                return $this->success('更新成功');
            } catch (ValidateException $e) {
                return $this->error($e->getMessage());
            }
        }

        $groupList = CustomFieldGroup::where('status', 1)->select();
        View::assign([
            'row'      => $row,
            'groupList' => $groupList,
        ]);
        return $this->fetchWithLayout('custom_field/edit');
    }

    private function normalizeAndValidateFieldParams(array $params): array
    {
        $type = trim((string) ($params['type'] ?? ''));
        if (!in_array($type, self::ALLOWED_FIELD_TYPES, true)) {
            throw new ValidateException('字段类型不合法');
        }

        $rawRules = (string) ($params['validation_rules'] ?? '');
        $rules = $this->splitValidationRules($rawRules);

        if ($type === 'number') {
            $hasNumberRule = false;
            foreach ($rules as $r) {
                $base = strtolower((string) strtok($r, ':'));
                if ($base === 'number' || $base === 'integer') {
                    $hasNumberRule = true;
                    break;
                }
            }
            if (!$hasNumberRule) {
                $rules[] = 'number';
            }
            $defaultValue = (string) ($params['default_value'] ?? '');
            if ($defaultValue !== '' && !is_numeric($defaultValue)) {
                throw new ValidateException('默认值必须为数字');
            }
        }

        if ($type === 'switch') {
            $defaultValue = (string) ($params['default_value'] ?? '');
            if ($defaultValue !== '' && !in_array($defaultValue, ['0', '1'], true)) {
                throw new ValidateException('开关类型默认值只能为 0 或 1');
            }
        }

        if (in_array($type, ['select', 'radio', 'checkbox'], true)) {
            $options = trim((string) ($params['options'] ?? ''));
            if ($options === '') {
                throw new ValidateException('选项配置不能为空');
            }
            if ((str_starts_with($options, '[') || str_starts_with($options, '{')) && json_decode($options, true) === null) {
                throw new ValidateException('选项配置必须为有效 JSON');
            }
        }

        $allowedRuleBases = [
            'required', 'email', 'url', 'phone', 'number', 'integer', 'min', 'max', 'length', 'regex',
        ];
        $hasRegex = false;
        foreach ($rules as $r) {
            $base = strtolower((string) strtok($r, ':'));
            if ($base === 'regex') {
                $hasRegex = true;
            }
            if (!in_array($base, $allowedRuleBases, true)) {
                throw new ValidateException('验证规则不支持：' . $base);
            }
        }
        if ($hasRegex) {
            $pattern = trim((string) ($params['regex_pattern'] ?? ''));
            if ($pattern === '') {
                throw new ValidateException('使用 regex 规则时必须填写正则表达式');
            }
            if (@preg_match($pattern, '') === false) {
                throw new ValidateException('正则表达式格式不正确');
            }
        }

        if ($rules !== []) {
            $params['validation_rules'] = implode(',', $rules);
        } else {
            $params['validation_rules'] = '';
        }

        return $params;
    }

    private function splitValidationRules(string $raw): array
    {
        $raw = str_replace(['|', '，', ' '], [',', ',', ''], $raw);
        $parts = array_filter(explode(',', $raw), static fn ($s) => $s !== '');
        $uniq = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '') {
                continue;
            }
            if (!in_array($p, $uniq, true)) {
                $uniq[] = $p;
            }
        }
        return $uniq;
    }

    public function del($ids = null)
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        Db::startTrans();
        try {
            $list = $this->model->where('id', 'in', $ids)->select();
            foreach ($list as $item) {
                CustomFieldValue::where('field_id', $item->id)->delete();
                $item->delete();
            }
            Db::commit();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    public function groups()
    {
        if ($this->request->isAjax()) {
            $page = $this->request->get('page/d', 1);
            $limit = $this->request->get('limit/d', 10);

            $list = CustomFieldGroup::order('sort', 'asc')
                ->paginate([
                    'list_rows' => $limit,
                    'page'      => $page,
                ]);

            return json([
                'code'  => 0,
                'msg'   => '',
                'count' => $list->total(),
                'data'  => $list->items(),
            ]);
        }

        return $this->fetchWithLayout('custom_field/groups');
    }

    public function addGroup()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            try {
                validate([
                    'name'       => 'require',
                    'table_name' => 'require',
                ])->check($params);

                $params['tenant_id'] = $this->auth->tenant_id ?? 0;
                CustomFieldGroup::create($params);
                return $this->success('添加成功');
            } catch (ValidateException $e) {
                return $this->error($e->getMessage());
            }
        }

        return $this->fetchWithLayout('custom_field/add_group');
    }

    public function editGroup($id = null)
    {
        $row = CustomFieldGroup::find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post();
            try {
                validate([
                    'name'       => 'require',
                    'table_name' => 'require',
                ])->check($params);

                $row->save($params);
                return $this->success('更新成功');
            } catch (ValidateException $e) {
                return $this->error($e->getMessage());
            }
        }

        View::assign('row', $row);
        return $this->fetchWithLayout('custom_field/edit_group');
    }

    public function delGroup($ids = null)
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        Db::startTrans();
        try {
            $list = CustomFieldGroup::where('id', 'in', $ids)->select();
            foreach ($list as $item) {
                $fieldIds = CustomFieldModel::where('group_id', $item->id)->column('id');
                if ($fieldIds) {
                    CustomFieldValue::where('field_id', 'in', $fieldIds)->delete();
                    CustomFieldModel::where('group_id', $item->id)->delete();
                }
                $item->delete();
            }
            Db::commit();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    public function getFieldsByGroup()
    {
        $groupId = $this->request->get('group_id/d', 0);
        $fields = CustomFieldModel::where('group_id', $groupId)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select();

        return json(['code' => 0, 'data' => $fields]);
    }
}
