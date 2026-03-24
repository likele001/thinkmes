<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\CustomFieldGroup;
use app\admin\model\CustomField as CustomFieldModel;
use app\admin\model\CustomFieldValue;
use think\facade\View;
use think\exception\ValidateException;
use think\facade\Db;

class CustomField extends Backend
{
    protected ?CustomFieldModel $model = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new CustomFieldModel();
    }

    public function index(): string
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

    public function add(): string
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            try {
                validate([
                    'group_id' => 'require',
                    'name'     => 'require|alphaNum',
                    'title'    => 'require',
                    'type'     => 'require',
                ])->check($params);

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

    public function edit($id = null): string
    {
        $row = $this->model->find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post();
            try {
                validate([
                    'group_id' => 'require',
                    'name'     => 'require|alphaNum',
                    'title'    => 'require',
                    'type'     => 'require',
                ])->check($params);

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
                $fieldIds = CustomField::where('group_id', $item->id)->column('id');
                if ($fieldIds) {
                    CustomFieldValue::where('field_id', 'in', $fieldIds)->delete();
                    CustomField::where('group_id', $item->id)->delete();
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
        $fields = CustomField::where('group_id', $groupId)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select();

        return json(['code' => 0, 'data' => $fields]);
    }
}
