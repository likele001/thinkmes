<?php
declare(strict_types=1);

namespace app\admin\controller\workflow;

use app\admin\controller\Backend;
use think\facade\Db;
use think\facade\View;
use think\Response;

class Module extends Backend
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
        if (!$this->request->isAjax()) {
            View::assign('title', '业务模块接入');
            return $this->fetchWithLayout('workflow/module/index');
        }

        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) {
            return $this->success('', ['total' => 0, 'list' => []]);
        }

        $mods = $this->moduleOptions();
        $list = Db::name('wf_module')
            ->where('tenant_id', $tenantId)
            ->order('id', 'asc')
            ->select()
            ->toArray();
        $byCode = [];
        foreach ($list as $r) {
            $byCode[(string) ($r['module_code'] ?? '')] = $r;
        }
        $out = [];
        foreach ($mods as $code => $title) {
            $row = $byCode[$code] ?? null;
            $out[] = [
                'module_code' => $code,
                'module_name' => $title,
                'enabled' => (int) ($row['enabled'] ?? 0),
                'definition_id' => (int) ($row['definition_id'] ?? 0),
                'table_name' => (string) ($row['table_name'] ?? ''),
                'title_field' => (string) ($row['title_field'] ?? ''),
                'status_field' => (string) ($row['status_field'] ?? ''),
                'in_progress_value' => (string) ($row['in_progress_value'] ?? ''),
                'approved_value' => (string) ($row['approved_value'] ?? ''),
                'rejected_value' => (string) ($row['rejected_value'] ?? ''),
                'update_time' => (int) ($row['update_time'] ?? 0),
            ];
        }
        return $this->success('', ['total' => count($out), 'list' => $out]);
    }

    public function options(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) {
            return $this->success('', ['definitions' => []]);
        }
        $defs = Db::name('wf_definition')
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc')
            ->field('id,name,module_code,status')
            ->select()
            ->toArray();
        return $this->success('', ['definitions' => $defs]);
    }

    public function save(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $moduleCode = trim((string) $this->request->post('module_code', ''));
        $enabled = (int) $this->request->post('enabled', 0);
        $definitionId = (int) $this->request->post('definition_id', 0);
        $tableName = trim((string) $this->request->post('table_name', ''));
        $titleField = trim((string) $this->request->post('title_field', ''));
        $statusField = trim((string) $this->request->post('status_field', ''));
        $inProgressValue = trim((string) $this->request->post('in_progress_value', ''));
        $approvedValue = trim((string) $this->request->post('approved_value', ''));
        $rejectedValue = trim((string) $this->request->post('rejected_value', ''));

        if ($moduleCode === '') {
            return $this->error('参数错误');
        }
        if ($enabled === 1) {
            if ($definitionId <= 0) return $this->error('请选择默认流程');
            if ($tableName === '' || $statusField === '') return $this->error('请填写业务表名与状态字段');
        }

        $now = time();
        $exists = Db::name('wf_module')->where('tenant_id', $tenantId)->where('module_code', $moduleCode)->find();
        $data = [
            'tenant_id' => $tenantId,
            'module_code' => $moduleCode,
            'enabled' => ($enabled ? 1 : 0),
            'definition_id' => $definitionId,
            'table_name' => $tableName,
            'title_field' => $titleField,
            'status_field' => $statusField,
            'in_progress_value' => $inProgressValue,
            'approved_value' => $approvedValue,
            'rejected_value' => $rejectedValue,
            'update_time' => $now,
        ];
        if ($exists) {
            Db::name('wf_module')->where('id', (int) $exists['id'])->update($data);
        } else {
            $data['create_time'] = $now;
            Db::name('wf_module')->insert($data);
        }
        return $this->success('保存成功');
    }
}

