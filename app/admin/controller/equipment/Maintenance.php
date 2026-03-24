<?php
declare(strict_types=1);

namespace app\admin\controller\equipment;

use app\admin\controller\Backend;
use app\admin\model\equipment\EquipmentMaintenancePlanModel;
use app\admin\model\equipment\EquipmentModel;
use think\facade\View;
use think\Response;

/**
 * 设备保养计划
 */
class Maintenance extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->getTenantId();
            $equipmentList = EquipmentModel::where('tenant_id', $tenantId)->order('code')->column('name', 'id');
            View::assign('equipmentList', $equipmentList ?: []);
            View::assign('title', '设备保养计划');
            return $this->fetchWithLayout('equipment/maintenance/index');
        }

        [$limit, $page] = $this->getPaginationParams();
        $query = EquipmentMaintenancePlanModel::with(['equipment'])->order('id', 'desc');
        $this->applyTenantFilter($query);

        $equipmentId = $this->request->get('equipment_id');
        if ($equipmentId !== '' && $equipmentId !== null) {
            $query->where('equipment_id', (int) $equipmentId);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['equipment_name'] = $item['equipment']['name'] ?? '';
            $item['plan_type_text'] = EquipmentMaintenancePlanModel::getPlanTypeList()[$item['plan_type']] ?? $item['plan_type'];
        }
        unset($item);

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['equipment_id'])) {
                return $this->error('请选择设备');
            }
            $params['tenant_id'] = $this->getTenantId();
            $params['create_time'] = time();
            $params['update_time'] = time();
            try {
                $row = EquipmentMaintenancePlanModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        $tenantId = $this->getTenantId();
        $equipmentList = EquipmentModel::where('tenant_id', $tenantId)->order('code')->select();
        View::assign('equipmentList', $equipmentList);
        View::assign('planTypeList', EquipmentMaintenancePlanModel::getPlanTypeList());
        View::assign('title', '添加保养计划');
        return $this->fetchWithLayout('equipment/maintenance/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = EquipmentMaintenancePlanModel::where('tenant_id', $tenantId)->find($ids);
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

        $equipmentList = EquipmentModel::where('tenant_id', $tenantId)->order('code')->select();
        View::assign('equipmentList', $equipmentList);
        View::assign('planTypeList', EquipmentMaintenancePlanModel::getPlanTypeList());
        View::assign('row', $row);
        View::assign('title', '编辑保养计划');
        return $this->fetchWithLayout('equipment/maintenance/edit');
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
            $row = EquipmentMaintenancePlanModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }
}
