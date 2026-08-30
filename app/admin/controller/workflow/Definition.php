<?php
declare(strict_types=1);

namespace app\admin\controller\workflow;

use app\admin\controller\Backend;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;

class Definition extends Backend
{
    private function moduleOptions(): array
    {
        return [
            'mes_order' => 'MES-订单',
            'mes_purchase' => 'MES-采购',
            'mes_salary' => 'MES-工资',
            'mes_quality' => 'MES-质检',
            'mes_shipment' => 'MES-发货',
            'crm_contract' => 'CRM-合同',
            'crm_sale_order' => 'CRM-销售订单',
            'crm_quote' => 'CRM-报价',
            'hr_leave' => 'HR-请假',
            'hr_overtime' => 'HR-加班',
            'hr_onboard' => 'HR-入职',
            'hr_offboard' => 'HR-离职',
            'finance_receivable' => '财务-应收',
            'finance_payable' => '财务-应付',
            'finance_payment' => '财务-付款申请',
            'equipment_repair' => '设备-维修',
            'equipment_purchase' => '设备-采购',
        ];
    }

    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '审批流定义');
            return $this->fetchWithLayout('workflow/definition/index');
        }

        [$limit, $page] = $this->getPaginationParams(20, 100);
        $keyword = trim((string) $this->request->get('keyword', ''));
        $moduleCode = trim((string) $this->request->get('module_code', ''));

        $q = Db::name('wf_definition')->order('id', 'desc');
        $this->applyTenantFilter($q, 'tenant_id');
        if ($keyword !== '') {
            $q->where('name', 'like', '%' . $keyword . '%');
        }
        if ($moduleCode !== '') {
            $q->where('module_code', $moduleCode);
        }
        $total = (int) $q->count();
        $list = $q->page($page, $limit)->select()->toArray();

        $mods = $this->moduleOptions();
        foreach ($list as &$row) {
            $mc = (string) ($row['module_code'] ?? '');
            $row['module_name'] = $mods[$mc] ?? $mc;
        }

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $tenantId = $this->getTenantId();
            $name = trim((string) $this->request->post('name', ''));
            $moduleCode = trim((string) $this->request->post('module_code', ''));
            $status = (int) $this->request->post('status', 1);
            $remark = trim((string) $this->request->post('remark', ''));

            if ($tenantId <= 0) {
                return $this->error('未识别租户');
            }
            if ($name === '' || mb_strlen($name) > 100) {
                return $this->error('流程名称不能为空，且长度需 <= 100');
            }
            if ($moduleCode === '') {
                return $this->error('请选择所属业务模块');
            }
            $exist = Db::name('wf_definition')->where('tenant_id', $tenantId)->where('name', $name)->find();
            if ($exist) {
                return $this->error('同租户内流程名称已存在');
            }

            $now = time();
            $id = (int) Db::name('wf_definition')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => $name,
                'module_code' => $moduleCode,
                'status' => ($status ? 1 : 0),
                'remark' => $remark,
                'create_time' => $now,
                'update_time' => $now,
            ]);

            return $this->success('创建成功', ['id' => $id]);
        }

        View::assign('title', '新建审批流');
        View::assign('modules', $this->moduleOptions());
        View::assign('data', ['status' => 1]);
        return $this->fetchWithLayout('workflow/definition/add');
    }

    public function edit(): string|Response
    {
        if ($this->request->isPost()) {
            $tenantId = $this->getTenantId();
            $id = (int) $this->request->post('id', 0);
            $name = trim((string) $this->request->post('name', ''));
            $moduleCode = trim((string) $this->request->post('module_code', ''));
            $status = (int) $this->request->post('status', 1);
            $remark = trim((string) $this->request->post('remark', ''));

            if ($tenantId <= 0 || $id <= 0) {
                return $this->error('参数错误');
            }
            $row = Db::name('wf_definition')->where('tenant_id', $tenantId)->where('id', $id)->find();
            if (!$row) {
                return $this->error('记录不存在');
            }
            if ($name === '' || mb_strlen($name) > 100) {
                return $this->error('流程名称不能为空，且长度需 <= 100');
            }
            if ($moduleCode === '') {
                return $this->error('请选择所属业务模块');
            }
            $exist = Db::name('wf_definition')->where('tenant_id', $tenantId)->where('name', $name)->where('id', '<>', $id)->find();
            if ($exist) {
                return $this->error('同租户内流程名称已存在');
            }

            $now = time();
            Db::name('wf_definition')->where('id', $id)->update([
                'name' => $name,
                'module_code' => $moduleCode,
                'status' => ($status ? 1 : 0),
                'remark' => $remark,
                'update_time' => $now,
            ]);

            return $this->success('保存成功', ['id' => $id]);
        }

        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);
        if ($tenantId <= 0 || $id <= 0) {
            return $this->error('参数错误');
        }
        $row = Db::name('wf_definition')->where('tenant_id', $tenantId)->where('id', $id)->find();
        if (!$row) {
            return $this->error('记录不存在');
        }

        View::assign('title', '编辑审批流');
        View::assign('modules', $this->moduleOptions());
        View::assign('data', $row);
        return $this->fetchWithLayout('workflow/definition/edit');
    }

    public function del(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        if ($tenantId <= 0 || $id <= 0) {
            return $this->error('参数错误');
        }
        $row = Db::name('wf_definition')->where('tenant_id', $tenantId)->where('id', $id)->find();
        if (!$row) {
            return $this->error('记录不存在');
        }
        $running = Db::name('wf_instance')->where('tenant_id', $tenantId)->where('definition_id', $id)->where('status', 0)->count();
        if ($running > 0) {
            return $this->error('存在审批中的实例，无法删除');
        }
        Db::transaction(function () use ($tenantId, $id) {
            Db::name('wf_node')->where('tenant_id', $tenantId)->where('definition_id', $id)->delete();
            Db::name('wf_definition')->where('tenant_id', $tenantId)->where('id', $id)->delete();
        });
        return $this->success('删除成功');
    }

    public function toggle(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $status = (int) $this->request->post('status', 1);
        if ($tenantId <= 0 || $id <= 0) {
            return $this->error('参数错误');
        }
        $row = Db::name('wf_definition')->where('tenant_id', $tenantId)->where('id', $id)->find();
        if (!$row) {
            return $this->error('记录不存在');
        }
        Db::name('wf_definition')->where('id', $id)->update([
            'status' => ($status ? 1 : 0),
            'update_time' => time(),
        ]);
        return $this->success('已更新');
    }

    public function designer(): string|Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);
        if ($tenantId <= 0 || $id <= 0) {
            return $this->error('参数错误');
        }
        $def = Db::name('wf_definition')->where('tenant_id', $tenantId)->where('id', $id)->find();
        if (!$def) {
            return $this->error('流程不存在');
        }
        if ($this->request->isAjax()) {
            $nodes = Db::name('wf_node')
                ->where('tenant_id', $tenantId)
                ->where('definition_id', $id)
                ->order('sort', 'asc')
                ->select()
                ->toArray();
            return $this->success('', ['definition' => $def, 'nodes' => $nodes]);
        }
        View::assign('title', '节点配置');
        View::assign('definition', $def);
        return $this->fetchWithLayout('workflow/definition/designer');
    }

    public function saveNodes(): Response
    {
        $tenantId = $this->getTenantId();
        $definitionId = (int) $this->request->post('definition_id', 0);
        $nodes = $this->request->post('nodes');
        if (!is_array($nodes)) {
            $nodesJson = (string) $this->request->post('nodes_json', '');
            if ($nodesJson !== '') {
                $decoded = json_decode($nodesJson, true);
                if (is_array($decoded)) {
                    $nodes = $decoded;
                }
            }
        }
        if ($tenantId <= 0 || $definitionId <= 0 || !is_array($nodes)) {
            return $this->error('参数错误');
        }
        $def = Db::name('wf_definition')->where('tenant_id', $tenantId)->where('id', $definitionId)->find();
        if (!$def) {
            return $this->error('流程不存在');
        }

        $clean = [];
        $sort = 1;
        foreach ($nodes as $n) {
            if (!is_array($n)) continue;
            $name = trim((string) ($n['name'] ?? ''));
            if ($name === '') continue;
            $approverType = trim((string) ($n['approver_type'] ?? 'admin'));
            $approvalMode = trim((string) ($n['approval_mode'] ?? 'any_sign'));
            $logic = strtoupper(trim((string) ($n['condition_logic'] ?? 'AND')));
            if ($logic !== 'OR') $logic = 'AND';

            $approverIds = $n['approver_ids'] ?? [];
            if (!is_array($approverIds)) {
                $approverIds = [];
            }
            $approverIds = array_values(array_unique(array_filter(array_map('intval', $approverIds), function ($v) { return $v > 0; })));
            $approverIdsJson = json_encode($approverIds, JSON_UNESCAPED_UNICODE);
            if (!is_string($approverIdsJson) || $approverIdsJson === '') $approverIdsJson = '[]';

            $items = $n['condition_items'] ?? [];
            if (!is_array($items)) $items = [];
            $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);
            if (!is_string($itemsJson) || $itemsJson === '') $itemsJson = '[]';

            $clean[] = [
                'sort' => $sort,
                'name' => $name,
                'approver_type' => $approverType !== '' ? $approverType : 'admin',
                'approver_ids' => $approverIdsJson,
                'approval_mode' => $approvalMode !== '' ? $approvalMode : 'any_sign',
                'condition_logic' => $logic,
                'condition_items' => $itemsJson,
            ];
            $sort++;
        }
        if (!$clean) {
            return $this->error('至少保留一个审批节点');
        }

        $now = time();
        Db::transaction(function () use ($tenantId, $definitionId, $clean, $now) {
            Db::name('wf_node')->where('tenant_id', $tenantId)->where('definition_id', $definitionId)->delete();
            foreach ($clean as $row) {
                Db::name('wf_node')->insert([
                    'tenant_id' => $tenantId,
                    'definition_id' => $definitionId,
                    'sort' => (int) $row['sort'],
                    'name' => (string) $row['name'],
                    'approver_type' => (string) $row['approver_type'],
                    'approver_ids' => (string) $row['approver_ids'],
                    'approval_mode' => (string) $row['approval_mode'],
                    'condition_logic' => (string) $row['condition_logic'],
                    'condition_items' => (string) $row['condition_items'],
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
            }
        });

        return $this->success('保存成功');
    }
}
