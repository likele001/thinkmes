<?php
declare(strict_types=1);

namespace app\admin\controller\equipment;

use app\admin\controller\Backend;
use app\admin\model\equipment\EquipmentRuntimeModel;
use app\admin\model\equipment\EquipmentModel;
use think\facade\View;
use think\Response;

/**
 * 设备运行记录
 */
class Runtime extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->getTenantId();
            $equipmentList = EquipmentModel::where('tenant_id', $tenantId)->order('code')->column('name', 'id');
            View::assign('equipmentList', $equipmentList ?: []);
            View::assign('title', '设备运行记录');
            return $this->fetchWithLayout('equipment/runtime/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = EquipmentRuntimeModel::with(['equipment'])->order('run_date', 'desc')->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tp = (int) $this->request->get('tenant_id', 0);
            if ($tp > 0) {
                $query->where('tenant_id', $tp);
            }
        }

        $equipmentId = $this->request->get('equipment_id');
        if ($equipmentId !== '' && $equipmentId !== null) {
            $query->where('equipment_id', (int) $equipmentId);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['equipment_name'] = $item['equipment']['name'] ?? '';
            $plan = (float) ($item['plan_hours'] ?? 8);
            $run = (float) ($item['run_hours'] ?? 0);
            $item['utilization'] = $plan > 0 ? round($run / $plan * 100, 1) : 0;
        }
        unset($item);

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['equipment_id']) || empty($params['run_date'])) {
                return $this->error('请填写设备与运行日期');
            }
            $params['tenant_id'] = $this->getTenantId();
            $params['create_time'] = time();
            $params['plan_hours'] = $params['plan_hours'] ?? 8;
            $params['run_hours'] = $params['run_hours'] ?? 0;
            $params['down_hours'] = $params['down_hours'] ?? 0;
            try {
                $row = EquipmentRuntimeModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        $tenantId = $this->getTenantId();
        $equipmentList = EquipmentModel::where('tenant_id', $tenantId)->order('code')->select();
        View::assign('equipmentList', $equipmentList);
        View::assign('title', '添加运行记录');
        return $this->fetchWithLayout('equipment/runtime/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = EquipmentRuntimeModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        $equipmentList = EquipmentModel::where('tenant_id', $tenantId)->order('code')->select();
        View::assign('equipmentList', $equipmentList);
        View::assign('row', $row);
        View::assign('title', '编辑运行记录');
        return $this->fetchWithLayout('equipment/runtime/edit');
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
            $row = EquipmentRuntimeModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }
}
