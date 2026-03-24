<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\Workflow as WorkflowModel;
use app\admin\model\WorkflowState;
use app\admin\model\WorkflowTransition;
use app\admin\model\WorkflowInstance;
use app\admin\model\WorkflowApproval;
use app\admin\service\WorkflowService;
use think\facade\View;
use think\exception\ValidateException;
use think\facade\Db;

class Workflow extends Backend
{
    protected ?WorkflowModel $model = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new WorkflowModel();
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
                $where[] = ['name|description', 'like', '%' . $search . '%'];
            }

            $list = $this->model
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

        return $this->fetchWithLayout('workflow/index');
    }

    public function add(): string
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            try {
                validate([
                    'name'        => 'require',
                    'table_name'  => 'require',
                    'title_field' => 'require',
                ])->check($params);

                $params['tenant_id'] = $this->auth->tenant_id ?? 0;
                $params['created_by'] = $this->auth->id ?? 0;

                Db::startTrans();
                try {
                    $workflow = $this->model->create($params);

                    $states = $params['states'] ?? [];
                    $transitions = $params['transitions'] ?? [];

                    foreach ($states as $index => $state) {
                        WorkflowState::create([
                            'workflow_id' => $workflow->id,
                            'name'        => $state['name'],
                            'is_initial'  => $state['is_initial'] ?? 0,
                            'sort'        => $index + 1,
                            'tenant_id'   => $workflow->tenant_id,
                        ]);
                    }

                    foreach ($transitions as $transition) {
                        $fromState = WorkflowState::where('workflow_id', $workflow->id)
                            ->where('name', $transition['from_state'])
                            ->value('id');
                        $toState = WorkflowState::where('workflow_id', $workflow->id)
                            ->where('name', $transition['to_state'])
                            ->value('id');

                        if ($fromState && $toState) {
                            WorkflowTransition::create([
                                'workflow_id'    => $workflow->id,
                                'from_state_id'  => $fromState,
                                'to_state_id'    => $toState,
                                'condition'      => $transition['condition'] ?? '',
                                'auto_transition'=> $transition['auto_transition'] ?? 0,
                                'tenant_id'      => $workflow->tenant_id,
                            ]);
                        }
                    }

                    Db::commit();
                    return $this->success('创建成功');
                } catch (\Exception $e) {
                    Db::rollback();
                    return $this->error('创建失败：' . $e->getMessage());
                }
            } catch (ValidateException $e) {
                return $this->error($e->getMessage());
            }
        }

        return $this->fetchWithLayout('workflow/add');
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
                    'name'        => 'require',
                    'table_name'  => 'require',
                    'title_field' => 'require',
                ])->check($params);

                $row->save($params);
                return $this->success('更新成功');
            } catch (ValidateException $e) {
                return $this->error($e->getMessage());
            }
        }

        View::assign('row', $row);
        return $this->fetchWithLayout('workflow/edit');
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
                WorkflowApproval::where('instance_id', 'in',
                    WorkflowInstance::where('workflow_id', $item->id)->column('id'))->delete();
                WorkflowInstance::where('workflow_id', $item->id)->delete();
                WorkflowTransition::where('workflow_id', $item->id)->delete();
                WorkflowState::where('workflow_id', $item->id)->delete();
                $item->delete();
            }
            Db::commit();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    public function instances(): string
    {
        if ($this->request->isAjax()) {
            $page = $this->request->get('page/d', 1);
            $limit = $this->request->get('limit/d', 10);
            $sort = $this->request->get('sort', 'id');
            $order = $this->request->get('order', 'desc');
            $search = $this->request->get('search', '');

            $where = [];
            if ($search) {
                $where[] = ['title', 'like', '%' . $search . '%'];
            }

            $list = WorkflowInstance::with(['workflow', 'currentState'])
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

        return $this->fetchWithLayout('workflow/instances');
    }

    public function startInstance()
    {
        $tableName = $this->request->post('table_name');
        $recordId = $this->request->post('record_id/d');
        $title = $this->request->post('title');

        try {
            $service = new WorkflowService(
                $this->auth->tenant_id ?? 0,
                $this->auth->id ?? 0,
                $this->auth->username ?? ''
            );
            $instance = $service->startWorkflow($tableName, $recordId, $title);
            return $this->success('启动成功', ['instance_id' => $instance->id]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function approve()
    {
        $instanceId = $this->request->post('instance_id/d');
        $transitionId = $this->request->post('transition_id/d');
        $comment = $this->request->post('comment', '');

        try {
            $service = new WorkflowService(
                $this->auth->tenant_id ?? 0,
                $this->auth->id ?? 0,
                $this->auth->username ?? ''
            );
            $service->approve($instanceId, $transitionId, $comment);
            return $this->success('审批通过');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function reject()
    {
        $instanceId = $this->request->post('instance_id/d');
        $comment = $this->request->post('comment', '');

        try {
            $service = new WorkflowService(
                $this->auth->tenant_id ?? 0,
                $this->auth->id ?? 0,
                $this->auth->username ?? ''
            );
            $service->reject($instanceId, $comment);
            return $this->success('已驳回');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function withdraw()
    {
        $instanceId = $this->request->post('instance_id/d');

        try {
            $service = new WorkflowService(
                $this->auth->tenant_id ?? 0,
                $this->auth->id ?? 0,
                $this->auth->username ?? ''
            );
            $service->withdraw($instanceId);
            return $this->success('已撤回');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function instanceDetail($id = null)
    {
        $instance = WorkflowInstance::with(['workflow', 'currentState', 'approvals'])->find($id);
        if (!$instance) {
            return $this->error('记录不存在');
        }

        View::assign('instance', $instance);
        return $this->fetchWithLayout('workflow/instance_detail');
    }

    public function getStates()
    {
        $workflowId = $this->request->get('workflow_id/d', 0);
        $states = WorkflowState::where('workflow_id', $workflowId)
            ->order('sort', 'asc')
            ->select();

        return json(['code' => 0, 'data' => $states]);
    }

    public function getTransitions()
    {
        $workflowId = $this->request->get('workflow_id/d', 0);
        $transitions = WorkflowTransition::with(['fromState', 'toState'])
            ->where('workflow_id', $workflowId)
            ->select();

        return json(['code' => 0, 'data' => $transitions]);
    }
}
