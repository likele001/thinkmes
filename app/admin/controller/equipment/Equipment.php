<?php
declare(strict_types=1);

namespace app\admin\controller\equipment;

use app\admin\controller\Backend;
use app\admin\model\equipment\EquipmentModel;
use app\admin\model\equipment\EquipmentRuntimeModel;
use think\facade\View;
use think\Response;

/**
 * 设备档案
 */
class Equipment extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '设备档案');
            return $this->fetchWithLayout('equipment/equipment/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $code = trim((string) $this->request->get('code'));
        $name = trim((string) $this->request->get('name'));
        $status = $this->request->get('status');

        $tenantId = $this->getTenantId();
        $query = EquipmentModel::order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }

        if ($code !== '') {
            $query->where('code', 'like', '%' . $code . '%');
        }
        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            $params['tenant_id'] = $this->getTenantId();
            $params['create_time'] = time();
            $params['update_time'] = time();
            try {
                $row = EquipmentModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        View::assign('title', '添加设备');
        return $this->fetchWithLayout('equipment/equipment/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = EquipmentModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            $params['update_time'] = time();
            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        View::assign('row', $row);
        View::assign('title', '编辑设备');
        return $this->fetchWithLayout('equipment/equipment/edit');
    }

    public function del(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : explode(',', $ids);
        $count = 0;
        foreach ($idsArr as $id) {
            $row = EquipmentModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }

    /**
     * 设备利用率统计
     */
    public function stat(): string|Response
    {
        if ($this->request->isAjax()) {
            $tenantId = $this->getTenantId();
            $startDate = $this->request->get('start_date', date('Y-m-d', strtotime('-30 days')));
            $endDate = $this->request->get('end_date', date('Y-m-d'));

            $query = EquipmentRuntimeModel::with(['equipment'])
                ->where('run_date', '>=', $startDate)
                ->where('run_date', '<=', $endDate);
            if ($tenantId > 0) {
                $query->where('tenant_id', $tenantId);
            }
            $list = $query->select()->toArray();

            $byEquipment = [];
            foreach ($list as $row) {
                $eid = $row['equipment_id'];
                if (!isset($byEquipment[$eid])) {
                    $byEquipment[$eid] = [
                        'equipment_id' => $eid,
                        'equipment_name' => $row['equipment']['name'] ?? '',
                        'plan_hours' => 0,
                        'run_hours' => 0,
                        'down_hours' => 0,
                        'days' => 0,
                    ];
                }
                $byEquipment[$eid]['plan_hours'] += (float) ($row['plan_hours'] ?? 8);
                $byEquipment[$eid]['run_hours'] += (float) ($row['run_hours'] ?? 0);
                $byEquipment[$eid]['down_hours'] += (float) ($row['down_hours'] ?? 0);
                $byEquipment[$eid]['days']++;
            }
            foreach ($byEquipment as &$v) {
                $v['utilization'] = $v['plan_hours'] > 0
                    ? round($v['run_hours'] / $v['plan_hours'] * 100, 1)
                    : 0;
            }
            unset($v);

            return $this->success('', [
                'list' => array_values($byEquipment),
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        }

        View::assign('title', '设备利用率统计');
        return $this->fetchWithLayout('equipment/equipment/stat');
    }
}
