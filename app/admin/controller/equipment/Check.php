<?php
declare(strict_types=1);

namespace app\admin\controller\equipment;

use app\admin\controller\Backend;
use app\admin\model\equipment\EquipmentCheckModel;
use app\admin\model\equipment\EquipmentModel;
use think\facade\View;
use think\Response;

/**
 * 设备点检记录
 */
class Check extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->getTenantId();
            $equipmentList = EquipmentModel::where('tenant_id', $tenantId)->order('code')->column('name', 'id');
            View::assign('equipmentList', $equipmentList ?: []);
            View::assign('title', '设备点检记录');
            return $this->fetchWithLayout('equipment/check/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = EquipmentCheckModel::with(['equipment'])->order('check_date', 'desc')->order('id', 'desc');
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
            $item['result_text'] = (EquipmentCheckModel::getResultList())[$item['result']] ?? '';
        }
        unset($item);

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['equipment_id']) || empty($params['check_date'])) {
                return $this->error('请填写设备与点检日期');
            }
            $params['tenant_id'] = $this->getTenantId();
            $params['create_time'] = time();
            try {
                $row = EquipmentCheckModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        $tenantId = $this->getTenantId();
        $equipmentList = EquipmentModel::where('tenant_id', $tenantId)->order('code')->select();
        View::assign('equipmentList', $equipmentList);
        View::assign('resultList', EquipmentCheckModel::getResultList());
        View::assign('title', '添加点检记录');
        return $this->fetchWithLayout('equipment/check/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = EquipmentCheckModel::where('tenant_id', $tenantId)->find($ids);
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
        View::assign('resultList', EquipmentCheckModel::getResultList());
        View::assign('row', $row);
        View::assign('title', '编辑点检记录');
        return $this->fetchWithLayout('equipment/check/edit');
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
            $row = EquipmentCheckModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }
}
