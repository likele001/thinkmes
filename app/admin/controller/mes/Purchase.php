<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\PurchaseRequestModel;
use app\admin\model\mes\PurchaseInModel;
use app\admin\model\mes\StockLogModel;
use app\admin\model\mes\SupplierModel;
use app\admin\model\mes\MaterialModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 采购管理
 *
 * @icon fa fa-shopping-cart
 * @remark 管理采购申请和入库
 */
class Purchase extends Backend
{
    /**
     * 采购申请列表
     */
    public function requestList(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '采购申请管理');
            return $this->fetchWithLayout('mes/purchase/request');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = PurchaseRequestModel::with(['material', 'supplier', 'order'])
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
     * 审核采购申请
     */
    public function auditRequest(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $ids = $this->request->post('ids');
        $status = $this->request->post('status'); // 1通过 2驳回
        $remark = $this->request->post('remark', '');

        if (empty($ids) || !in_array($status, ['1', '2'])) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : explode(',', $ids);

        try {
            foreach ($idsArr as $id) {
                $request = PurchaseRequestModel::where('tenant_id', $tenantId)->find($id);
                if (!$request) {
                    continue;
                }

                if ($status == 1) {
                    // 审核通过，状态改为已审核
                    $request->status = 1;
                } else {
                    // 驳回，状态改为已取消
                    $request->status = 3;
                }
                $request->save();
            }

            return $this->success('操作成功');
        } catch (\Exception $e) {
            return $this->error('操作失败');
        }
    }

    /**
     * 采购入库列表
     */
    public function inbound(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            // 获取供应商列表
            $tenantId = $this->getTenantId();
            $supplierList = SupplierModel::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->column('name', 'id');
            View::assign('supplierList', $supplierList ?: []);

            View::assign('title', '采购入库管理');
            return $this->fetchWithLayout('mes/purchase/inbound');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = PurchaseInModel::with(['material', 'supplier', 'warehouse'])
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
     * 添加入库单
     */
    public function addInbound(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $tenantId = $this->getTenantId();
            $params['tenant_id'] = $tenantId;
            $params['in_no'] = PurchaseInModel::generateInNo();
            $params['operator_id'] = $this->auth->id ?? 0;

            // 处理入库时间
            if (!empty($params['in_time'])) {
                $params['in_time'] = strtotime($params['in_time']);
            }

            Db::startTrans();
            try {
                $inbound = PurchaseInModel::create($params);

                // 更新物料库存
                $material = MaterialModel::where('tenant_id', $tenantId)->find($params['material_id']);
                if ($material) {
                    $material->stock += $params['in_quantity'];
                    $material->save();

                    // 记录库存流水
                    StockLogModel::log(
                        $tenantId,
                        $params['material_id'],
                        $params['in_quantity'],
                        'purchase_in',
                        $inbound->id,
                        $params['operator_id'],
                        '采购入库：' . $params['in_no']
                    );
                }

                // 更新采购申请状态
                if (!empty($params['purchase_request_id'])) {
                    $request = PurchaseRequestModel::where('tenant_id', $tenantId)
                        ->find($params['purchase_request_id']);
                    if ($request) {
                        $request->status = 2; // 已采购
                        $request->save();
                    }
                }

                Db::commit();
                return $this->success('入库成功', ['id' => $inbound->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('入库失败');
            }
        }

        $tenantId = $this->getTenantId();
        // 获取待审核的采购申请
        $requestList = PurchaseRequestModel::with(['material', 'supplier'])
            ->where('tenant_id', $tenantId)
            ->where('status', 1) // 已审核
            ->select();
        View::assign('requestList', $requestList);

        // 获取物料列表
        $materialList = MaterialModel::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->column('name', 'id');
        View::assign('materialList', $materialList ?: []);

        View::assign('title', '添加入库单');
        return $this->fetchWithLayout('mes/purchase/add_inbound');
    }

    /**
     * 编辑入库单
     */
    public function editInbound(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = PurchaseInModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('入库单不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败');
            }
        }

        View::assign('row', $row);
        View::assign('title', '编辑入库单');
        return $this->fetchWithLayout('mes/purchase/edit_inbound');
    }

    /**
     * 确认入库
     */
    public function confirmInbound(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $id = $this->request->post('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $inbound = PurchaseInModel::where('tenant_id', $tenantId)->find($id);
        if (!$inbound) {
            return $this->error('入库单不存在');
        }

        if ($inbound->status == 1) {
            return $this->error('该入库单已确认');
        }

        Db::startTrans();
        try {
            $inbound->status = 1; // 已入库
            $inbound->in_time = time();
            $inbound->save();

            Db::commit();
            return $this->success('确认成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('确认失败');
        }
    }

    /**
     * 根据供应商ID获取物料列表
     */
    public function getMaterials(): Response
    {
        $supplierId = $this->request->get('supplier_id');
        if (empty($supplierId)) {
            return $this->success('', []);
        }

        $tenantId = $this->getTenantId();
        // 这里可以根据实际业务逻辑获取该供应商的物料列表
        // 暂时返回所有活跃的物料
        $materials = MaterialModel::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->field('id, name')
            ->select()
            ->toArray();

        return $this->success('', $materials);
    }

    /**
     * 保存入库单
     */
    public function saveInbound(): Response
    {
        return $this->addInbound();
    }
}
