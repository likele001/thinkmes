<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ShipmentModel;
use app\admin\model\mes\ShipmentItemModel;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\CustomerModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\TraceCodeModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 发货管理
 *
 * @icon fa fa-truck
 * @remark 管理发货单和物流跟踪
 */
class Shipment extends Backend
{
    /**
     * 发货单列表
     */
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '发货管理');
            return $this->fetchWithLayout('mes/shipment/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = ShipmentModel::with(['order', 'customer'])
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
     * 添加发货单
     */
    public function add(): string|Response
    {
        $orderId = $this->request->get('order_id');
        if (!$orderId) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $order = OrderModel::where('tenant_id', $tenantId)->find($orderId);
        if (!$order) {
            return $this->error('订单不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $items = $this->request->post('items');

            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $tenantId = $this->getTenantId();
            $params['tenant_id'] = $tenantId;
            $params['order_id'] = $orderId;
            $params['customer_id'] = $order->customer_id;
            $params['shipment_no'] = ShipmentModel::generateShipmentNo();
            $params['operator_id'] = $this->auth->id ?? 0;

            // 处理发货时间
            if (!empty($params['shipment_time'])) {
                $params['shipment_time'] = strtotime($params['shipment_time']);
            }

            // 处理JSON格式的明细数据
            if (is_string($items)) {
                $items = json_decode($items, true);
            }

            Db::startTrans();
            try {
                $shipment = ShipmentModel::create($params);

                $totalQuantity = 0;
                foreach ($items as $item) {
                    if (isset($item['model_id']) && isset($item['quantity']) && $item['quantity'] > 0) {
                        ShipmentItemModel::create([
                            'tenant_id' => $tenantId,
                            'shipment_id' => $shipment->id,
                            'model_id' => $item['model_id'],
                            'quantity' => $item['quantity'],
                            'batch_no' => $item['batch_no'] ?? '',
                            'trace_code' => $item['trace_code'] ?? '',
                            'create_time' => time(),
                        ]);
                        $totalQuantity += $item['quantity'];
                    }
                }

                // 更新发货数量
                $shipment->shipment_quantity = $totalQuantity;
                $shipment->save();

                // 更新订单发货状态
                $order->shipment_id = $shipment->id;
                $order->shipment_status = 2; // 已发货
                $order->status = 2; // 已完成
                $order->save();

                Db::commit();
                return $this->success('添加成功', ['id' => $shipment->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        // 获取订单的型号列表
        $modelList = [];
        $orderModels = Db::name('mes_order_model')
            ->alias('om')
            ->join('mes_product_model pm', 'om.model_id = pm.id')
            ->join('mes_product p', 'pm.product_id = p.id')
            ->where('om.tenant_id', $tenantId)
            ->where('om.order_id', $orderId)
            ->field('pm.id, pm.name, pm.model_code, p.name as product_name, om.quantity')
            ->select();
        foreach ($orderModels as $om) {
            $displayName = $om['product_name'] . ' - ' . $om['name'];
            if ($om['model_code']) {
                $displayName .= ' (' . $om['model_code'] . ')';
            }
            $modelList[] = [
                'id' => $om['id'],
                'name' => $displayName,
                'max_quantity' => $om['quantity']
            ];
        }

        View::assign('order', $order);
        View::assign('modelList', json_encode($modelList, JSON_UNESCAPED_UNICODE));
        View::assign('title', '添加发货单');
        return $this->fetchWithLayout('mes/shipment/add');
    }

    /**
     * 编辑发货单
     */
    public function edit(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = ShipmentModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('发货单不存在');
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
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        View::assign('row', $row);
        View::assign('title', '编辑发货单');
        return $this->fetchWithLayout('mes/shipment/edit');
    }

    /**
     * 删除发货单
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

        Db::startTrans();
        try {
            foreach ($idsArr as $id) {
                $shipment = ShipmentModel::where('tenant_id', $tenantId)->find($id);
                if (!$shipment) {
                    continue;
                }

                // 删除发货明细
                ShipmentItemModel::where('tenant_id', $tenantId)
                    ->where('shipment_id', $id)
                    ->delete();

                $shipment->delete();
            }

            Db::commit();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 确认签收
     */
    public function confirmSign(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $id = $this->request->post('id');
        $signUser = $this->request->post('sign_user', '');

        if (empty($id)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $shipment = ShipmentModel::where('tenant_id', $tenantId)->find($id);
        if (!$shipment) {
            return $this->error('发货单不存在');
        }

        if ($shipment->status == 2) {
            return $this->error('该发货单已签收');
        }

        Db::startTrans();
        try {
            $shipment->status = 2; // 已签收
            $shipment->sign_time = time();
            $shipment->sign_user = $signUser;
            $shipment->save();

            Db::commit();
            return $this->success('签收成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('签收失败：' . $e->getMessage());
        }
    }

    /**
     * 物流跟踪
     */
    public function track(): string|Response
    {
        $id = $this->request->get('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $shipment = ShipmentModel::with(['order', 'customer', 'items'])
            ->where('tenant_id', $tenantId)
            ->find($id);

        if (!$shipment) {
            return $this->error('发货单不存在');
        }

        View::assign('shipment', $shipment);
        View::assign('title', '物流跟踪');
        return $this->fetchWithLayout('mes/shipment/track');
    }
}
