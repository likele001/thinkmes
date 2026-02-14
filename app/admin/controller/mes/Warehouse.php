<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\WarehouseModel;
use think\facade\View;
use think\Response;

/**
 * 仓库管理
 *
 * @icon fa fa-home
 * @remark 管理仓库信息
 */
class Warehouse extends Backend
{
    /**
     * 仓库列表
     */
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '仓库管理');
            return $this->fetchWithLayout('mes/warehouse/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = WarehouseModel::order('is_default', 'desc')
            ->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }

        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 添加仓库
     */
    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $tenantId = $this->getTenantId();
            $params['tenant_id'] = $tenantId;

            try {
                $warehouse = WarehouseModel::create($params);

                // 如果设为默认仓库，取消其他默认仓库
                if (!empty($params['is_default']) && $params['is_default'] == 1) {
                    WarehouseModel::where('tenant_id', $tenantId)
                        ->where('id', '<>', $warehouse->id)
                        ->save(['is_default' => 0]);
                }

                return $this->success('添加成功', ['id' => $warehouse->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败');
            }
        }

        View::assign('title', '添加仓库');
        return $this->fetchWithLayout('mes/warehouse/add');
    }

    /**
     * 编辑仓库
     */
    public function edit(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = WarehouseModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('仓库不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            try {
                // 如果设为默认仓库，取消其他默认仓库
                if (!empty($params['is_default']) && $params['is_default'] == 1) {
                    WarehouseModel::where('tenant_id', $tenantId)
                        ->where('id', '<>', $ids)
                        ->save(['is_default' => 0]);
                }

                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败');
            }
        }

        View::assign('row', $row);
        View::assign('title', '编辑仓库');
        return $this->fetchWithLayout('mes/warehouse/edit');
    }

    /**
     * 删除仓库
     */
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

        try {
            foreach ($idsArr as $id) {
                $warehouse = WarehouseModel::where('tenant_id', $tenantId)->find($id);
                if (!$warehouse) {
                    continue;
                }

                // 不允许删除默认仓库
                if ($warehouse->is_default == 1) {
                    return $this->error('默认仓库不能删除');
                }

                $warehouse->delete();
            }

            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败');
        }
    }
}
