<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\StockOutModel;
use app\admin\model\mes\StockLogModel;
use app\admin\model\mes\MaterialModel;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\OrderMaterialModel;
use app\admin\model\mes\WarehouseModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 库存管理
 *
 * @icon fa fa-warehouse
 * @remark 管理物料库存、出入库
 */
class Stock extends Backend
{
    /**
     * 库存列表
     */
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->getTenantId();

            // 获取仓库列表
            $warehouseList = WarehouseModel::where('tenant_id', $tenantId)
                ->where('status', 1)
                ->column('name', 'id');
            View::assign('warehouseList', $warehouseList ?: []);

            View::assign('title', '库存管理');
            return $this->fetchWithLayout('mes/stock/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = MaterialModel::where('tenant_id', $tenantId)
            ->order('id', 'desc');

        // 搜索条件
        $name = trim((string) $this->request->get('name'));
        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }

        $warehouseId = $this->request->get('warehouse_id');
        if ($warehouseId) {
            $query->where('warehouse_id', (int) $warehouseId);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        // 计算库存预警
        foreach ($list as &$item) {
            $item['is_warning'] = $item['stock'] < $item['min_stock'];
        }

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 出库单列表
     */
    public function outbound(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '生产领料管理');
            return $this->fetchWithLayout('mes/stock/outbound');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = StockOutModel::with(['order', 'material'])
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc');

        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 创建出库单（根据订单物料需求）
     */
    public function addOutbound(): string|Response
    {
        $orderMaterialId = $this->request->get('order_material_id');
        if (!$orderMaterialId) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $orderMaterial = OrderMaterialModel::with(['material', 'order'])
            ->where('tenant_id', $tenantId)
            ->find($orderMaterialId);

        if (!$orderMaterial) {
            return $this->error('订单物料不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $params['tenant_id'] = $tenantId;
            $params['order_id'] = $orderMaterial->order_id;
            $params['material_id'] = $orderMaterial->material_id;
            $params['out_no'] = StockOutModel::generateOutNo();
            $params['operator_id'] = $this->auth->id ?? 0;
            $params['receiver_id'] = $params['receiver_id'] ?? 0;
            $params['out_quantity'] = $orderMaterial->required_quantity;

            // 处理出库时间
            if (!empty($params['out_time'])) {
                $params['out_time'] = strtotime($params['out_time']);
            }

            Db::startTrans();
            try {
                $outbound = StockOutModel::create($params);

                // 更新物料库存
                $material = MaterialModel::where('tenant_id', $tenantId)
                    ->find($orderMaterial->material_id);
                if ($material) {
                    $newStock = $material->stock - $params['out_quantity'];
                    if ($newStock < 0) {
                        throw new \Exception('库存不足，当前库存：' . $material->stock);
                    }
                    $material->stock = $newStock;
                    $material->save();

                    // 记录库存流水
                    StockLogModel::log(
                        $tenantId,
                        $orderMaterial->material_id,
                        -$params['out_quantity'],
                        'production_out',
                        $outbound->id,
                        $params['operator_id'],
                        '生产领料：' . $params['out_no']
                    );
                }

                // 更新订单物料状态
                $orderMaterial->stock_status = 0; // 已备料
                $orderMaterial->save();

                Db::commit();
                return $this->success('出库成功', ['id' => $outbound->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('出库失败：' . $e->getMessage());
            }
        }

        View::assign('orderMaterial', $orderMaterial);
        View::assign('title', '创建领料单');
        return $this->fetchWithLayout('mes/stock/add_outbound');
    }

    /**
     * 确认出库
     */
    public function confirmOutbound(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $id = $this->request->post('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $outbound = StockOutModel::where('tenant_id', $tenantId)->find($id);
        if (!$outbound) {
            return $this->error('出库单不存在');
        }

        if ($outbound->status == 1) {
            return $this->error('该出库单已确认');
        }

        Db::startTrans();
        try {
            $outbound->status = 1; // 已出库
            $outbound->out_time = time();
            $outbound->save();

            Db::commit();
            return $this->success('确认成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('确认失败：' . $e->getMessage());
        }
    }

    /**
     * 库存流水
     */
    public function log(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '库存流水');
            return $this->fetchWithLayout('mes/stock/log');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = StockLogModel::with(['material'])
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc');

        // 搜索条件
        $materialId = $this->request->get('material_id');
        if ($materialId) {
            $query->where('material_id', (int) $materialId);
        }

        $businessType = $this->request->get('business_type');
        if ($businessType) {
            $query->where('business_type', $businessType);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 盘点
     */
    public function check(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $materialId = $this->request->post('material_id');
        $actualQuantity = $this->request->post('actual_quantity');
        $remark = $this->request->post('remark', '');

        if (!$materialId || $actualQuantity === null || $actualQuantity === '') {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $material = MaterialModel::where('tenant_id', $tenantId)->find($materialId);
        if (!$material) {
            return $this->error('物料不存在');
        }

        Db::startTrans();
        try {
            $diffQuantity = $actualQuantity - $material->stock;
            $businessType = $diffQuantity >= 0 ? 'check_in' : 'check_out';

            // 更新库存
            $material->stock = $actualQuantity;
            $material->save();

            // 记录流水
            StockLogModel::log(
                $tenantId,
                $materialId,
                $diffQuantity,
                $businessType,
                0,
                $this->auth->id ?? 0,
                '库存盘点：' . $remark
            );

            Db::commit();
            return $this->success('盘点成功，差异：' . ($diffQuantity >= 0 ? '+' : '') . $diffQuantity);
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('盘点失败：' . $e->getMessage());
        }
    }
}
