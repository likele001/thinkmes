<?php
declare(strict_types=1);

namespace app\admin\controller\workflow;

use app\admin\controller\Backend;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 工作流实例列表（租户维度；平台超管可查全部或 ?tenant_id= 筛选）
 */
class Instance extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '工作流实例');
            return $this->fetchWithLayout('workflow/instance/index');
        }

        [$limit, $page] = $this->getPaginationParams(20, 100);
        $keyword = trim((string) $this->request->get('keyword', ''));
        $moduleCode = trim((string) $this->request->get('module_code', ''));
        $statusRaw = $this->request->get('status', '');

        $q = Db::name('wf_instance');
        $this->applyTenantFilter($q, 'tenant_id');

        if ($keyword !== '') {
            $q->whereLike('business_title|instance_no', '%' . $keyword . '%');
        }
        if ($moduleCode !== '') {
            $q->where('module_code', $moduleCode);
        }
        if ($statusRaw !== '' && $statusRaw !== null && is_numeric($statusRaw)) {
            $q->where('status', (int) $statusRaw);
        }

        $q->order('id', 'desc');
        $total = (int) $q->count();
        $list = $q->page($page, $limit)->select()->toArray();

        $defIds = array_unique(array_values(array_filter(array_map(static function ($row) {
            return (int) ($row['definition_id'] ?? 0);
        }, $list))));
        $defMap = [];
        if ($defIds !== []) {
            $defMap = Db::name('wf_definition')->whereIn('id', $defIds)->column('name', 'id');
        }
        foreach ($list as &$row) {
            $did = (int) ($row['definition_id'] ?? 0);
            $row['definition_name'] = $defMap[$did] ?? '-';
        }
        unset($row);

        return $this->success('', ['total' => $total, 'list' => $list]);
    }
}
