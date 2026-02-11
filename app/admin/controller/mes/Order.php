<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\OrderModelModel;
use app\admin\model\mes\OrderMaterialModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\CustomerModel;
use app\admin\model\mes\BomModel;
use app\admin\model\mes\BomItemModel;
use app\admin\model\mes\MaterialModel;
use app\admin\model\mes\PurchaseRequestModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 订单管理
 * 
 * @icon fa fa-shopping-cart
 * @remark 管理工厂生产订单信息
 */
class Order extends Backend
{
    /**
     * 订单列表
     */
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '订单管理');
            return $this->fetchWithLayout('mes/order/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $orderNo = trim((string) $this->request->get('order_no'));
        $status = $this->request->get('status');

        $tenantId = $this->getTenantId();
        $query = OrderModel::with(['orderModels.model.product', 'customer'])
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc');

        if ($orderNo !== '') {
            $query->where('order_no', 'like', '%' . $orderNo . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 添加订单
     */
    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $modelData = $this->request->post('models');

            if (empty($params) || empty($modelData)) {
                return $this->error('参数不能为空');
            }

            // 处理JSON格式的型号数据
            if (is_string($modelData)) {
                $modelData = json_decode($modelData, true);
            }

            $tenantId = $this->getTenantId();
            $params['tenant_id'] = $tenantId;
            $params['order_no'] = OrderModel::generateOrderNo();

            // 如果选择了客户ID，自动填充客户信息
            if (!empty($params['customer_id'])) {
                $customer = CustomerModel::where('tenant_id', $tenantId)
                    ->where('id', $params['customer_id'])
                    ->find();
                if ($customer) {
                    $params['customer_name'] = $customer->customer_name;
                    $params['customer_phone'] = $customer->contact_phone ?? '';
                }
            }

            // 处理交货时间
            if (!empty($params['delivery_time'])) {
                $params['delivery_time'] = strtotime($params['delivery_time']);
            }

            Db::startTrans();
            try {
                $order = OrderModel::create($params);

                // 保存订单型号
                $totalQuantity = 0;
                foreach ($modelData as $modelItem) {
                    if (isset($modelItem['model_id']) && isset($modelItem['quantity']) && $modelItem['quantity'] > 0) {
                        OrderModelModel::create([
                            'tenant_id' => $tenantId,
                            'order_id' => $order->id,
                            'model_id' => $modelItem['model_id'],
                            'quantity' => $modelItem['quantity']
                        ]);
                        $totalQuantity += $modelItem['quantity'];
                    }
                }

                // 更新订单总数量
                $order->save(['total_quantity' => $totalQuantity]);

                // 自动计算物料需求
                $this->calculateMaterialsWithCost($order->id, $tenantId);

                // 自动检查库存并生成采购申请
                $this->autoGeneratePurchaseRequests($order->id, $tenantId);

                Db::commit();
                return $this->success('添加成功', ['id' => $order->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        // 获取产品型号列表
        $tenantId = $this->getTenantId();
        $modelList = [];
        $models = ProductModelModel::with('product')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->select();
        foreach ($models as $model) {
            $displayName = $model->product->name . ' - ' . $model->name;
            if ($model->model_code) {
                $displayName .= ' (' . $model->model_code . ')';
            }
            $modelList[$model->id] = $displayName;
        }
        View::assign('modelList', $modelList);

        // 获取客户列表
        $customerList = CustomerModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('customer_name', 'id');
        View::assign('customerList', $customerList ?: []);

        View::assign('title', '添加订单');
        return $this->fetchWithLayout('mes/order/add');
    }

    /**
     * 编辑订单
     */
    public function edit(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = OrderModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('订单不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $modelData = $this->request->post('models');

            if (empty($params) || empty($modelData)) {
                return $this->error('参数不能为空');
            }

            // 处理JSON格式的型号数据
            if (is_string($modelData)) {
                $modelData = json_decode($modelData, true);
            }

            // 如果选择了客户ID，自动填充客户信息
            if (!empty($params['customer_id'])) {
                $customer = CustomerModel::where('tenant_id', $tenantId)
                    ->where('id', $params['customer_id'])
                    ->find();
                if ($customer) {
                    $params['customer_name'] = $customer->customer_name;
                    $params['customer_phone'] = $customer->contact_phone ?? '';
                }
            }

            // 处理交货时间
            if (!empty($params['delivery_time'])) {
                $params['delivery_time'] = strtotime($params['delivery_time']);
            }

            Db::startTrans();
            try {
                $row->save($params);

                // 删除原有订单型号
                OrderModelModel::where('tenant_id', $tenantId)
                    ->where('order_id', $ids)
                    ->delete();

                // 保存新的订单型号
                $totalQuantity = 0;
                foreach ($modelData as $modelItem) {
                    if (isset($modelItem['model_id']) && isset($modelItem['quantity']) && $modelItem['quantity'] > 0) {
                        OrderModelModel::create([
                            'tenant_id' => $tenantId,
                            'order_id' => $ids,
                            'model_id' => $modelItem['model_id'],
                            'quantity' => $modelItem['quantity']
                        ]);
                        $totalQuantity += $modelItem['quantity'];
                    }
                }

                // 更新订单总数量
                $row->save(['total_quantity' => $totalQuantity]);

                // 重新计算物料需求
                OrderMaterialModel::where('tenant_id', $tenantId)
                    ->where('order_id', $ids)
                    ->delete();
                $this->calculateMaterialsWithCost($ids, $tenantId);

                // 自动检查库存并生成采购申请
                $this->autoGeneratePurchaseRequests($ids, $tenantId);

                Db::commit();
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        // 获取产品型号列表
        $modelList = [];
        $models = ProductModelModel::with('product')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->select();
        foreach ($models as $model) {
            $displayName = $model->product->name . ' - ' . $model->name;
            if ($model->model_code) {
                $displayName .= ' (' . $model->model_code . ')';
            }
            $modelList[$model->id] = $displayName;
        }
        View::assign('modelList', $modelList);

        // 获取客户列表
        $customerList = CustomerModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('customer_name', 'id');
        View::assign('customerList', $customerList ?: []);

        // 获取订单型号数据
        $orderModels = OrderModelModel::where('tenant_id', $tenantId)
            ->where('order_id', $ids)
            ->select();
        View::assign('orderModels', $orderModels);

        View::assign('row', $row);
        View::assign('title', '编辑订单');
        return $this->fetchWithLayout('mes/order/edit');
    }

    /**
     * 删除订单
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
                $order = OrderModel::where('tenant_id', $tenantId)->find($id);
                if (!$order) {
                    continue;
                }

                // 删除订单型号
                OrderModelModel::where('tenant_id', $tenantId)
                    ->where('order_id', $id)
                    ->delete();

                // 删除订单物料需求
                OrderMaterialModel::where('tenant_id', $tenantId)
                    ->where('order_id', $id)
                    ->delete();

                $order->delete();
            }

            Db::commit();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 计算订单物料需求及成本
     */
    private function calculateMaterialsWithCost(int $orderId, int $tenantId): array
    {
        $order = OrderModel::with(['orderModels.model'])
            ->where('tenant_id', $tenantId)
            ->find($orderId);
        if (!$order) {
            return [];
        }

        $materialNeeds = [];

        foreach ($order->orderModels as $orderModel) {
            $quantity = $orderModel->quantity;
            $modelId = $orderModel->model_id;

            // 优先使用BOM计算物料需求（如果存在已发布的BOM）
            $bom = BomModel::where('tenant_id', $tenantId)
                ->where('model_id', $modelId)
                ->where('status', 2) // 已发布状态
                ->order('version', 'desc')
                ->find();

            if ($bom) {
                // 使用BOM计算物料需求
                $bomItems = BomItemModel::where('tenant_id', $tenantId)
                    ->where('bom_id', $bom->id)
                    ->where('parent_id', 0) // 只取第一层物料
                    ->with(['material'])
                    ->select();

                foreach ($bomItems as $item) {
                    if (!$item->material) {
                        continue;
                    }

                    // 计算需求量 = 订单数量 × BOM用量 × (1 + 损耗率)
                    $baseQty = $quantity * ($bom->base_quantity ?: 1);
                    $needQty = $baseQty * $item->quantity * (1 + ($item->loss_rate ?: 0) / 100);

                    if (!isset($materialNeeds[$item->material_id])) {
                        $bestPrice = $item->material->current_price;
                        $bestSupplierId = $item->material->default_supplier_id;

                        if ($item->supplier_id) {
                            $bestSupplierId = $item->supplier_id;
                            $bestPrice = $item->unit_price ?: $item->material->current_price;
                        }

                        $materialNeeds[$item->material_id] = [
                            'material' => $item->material,
                            'quantity' => 0,
                            'price' => $bestPrice,
                            'supplier_id' => $bestSupplierId,
                            'amount' => 0,
                            'loss_rate' => $item->loss_rate ?: 0
                        ];
                    }

                    $materialNeeds[$item->material_id]['quantity'] += $needQty;
                    $materialNeeds[$item->material_id]['amount'] = 
                        $materialNeeds[$item->material_id]['quantity'] * 
                        $materialNeeds[$item->material_id]['price'];
                }
            }
        }

        // 保存到订单物料需求表
        foreach ($materialNeeds as $materialId => $data) {
            OrderMaterialModel::create([
                'tenant_id' => $tenantId,
                'order_id' => $orderId,
                'material_id' => $materialId,
                'required_quantity' => $data['quantity'],
                'estimated_price' => $data['price'],
                'estimated_amount' => $data['amount'],
                'supplier_id' => $data['supplier_id'],
                'loss_rate' => $data['loss_rate'],
                'purchase_status' => 0,
                'stock_status' => 0
            ]);
        }

        return $materialNeeds;
    }

    /**
     * 自动检查库存并生成采购申请
     */
    private function autoGeneratePurchaseRequests(int $orderId, int $tenantId): void
    {
        $orderMaterials = OrderMaterialModel::where('tenant_id', $tenantId)
            ->where('order_id', $orderId)
            ->with(['material'])
            ->select();

        foreach ($orderMaterials as $om) {
            if (!$om->material) {
                continue;
            }

            $material = $om->material;
            $requiredQty = $om->required_quantity;
            $currentStock = $material->stock;

            // 检查是否缺料
            if ($currentStock < $requiredQty) {
                $shortageQty = $requiredQty - $currentStock;

                // 更新订单物料需求状态
                $om->stock_status = 1; // 缺料
                $om->save();

                // 检查24小时内是否已有采购申请
                $recentRequest = PurchaseRequestModel::where('tenant_id', $tenantId)
                    ->where('material_id', $material->id)
                    ->where('status', '<', 2) // 未完成状态
                    ->where('create_time', '>', time() - 86400) // 24小时内
                    ->find();

                if (!$recentRequest && $om->supplier_id) {
                    // 创建采购申请
                    PurchaseRequestModel::create([
                        'tenant_id' => $tenantId,
                        'request_no' => PurchaseRequestModel::generateRequestNo(),
                        'material_id' => $material->id,
                        'supplier_id' => $om->supplier_id,
                        'required_quantity' => $shortageQty,
                        'estimated_price' => $om->estimated_price,
                        'estimated_amount' => $shortageQty * $om->estimated_price,
                        'order_id' => $orderId,
                        'order_material_id' => $om->id,
                        'status' => 0, // 待审核
                    ]);
                }
            } else {
                // 库存充足
                $om->stock_status = 0; // 已备料
                $om->save();
            }
        }
    }

    /**
     * 查看订单物料清单
     */
    public function materialList(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $order = OrderModel::where('tenant_id', $tenantId)->find($ids);
        if (!$order) {
            return $this->error('订单不存在');
        }

        // 重新计算物料需求
        OrderMaterialModel::where('tenant_id', $tenantId)
            ->where('order_id', $ids)
            ->delete();
        $this->calculateMaterialsWithCost($ids, $tenantId);

        // 获取物料需求
        $orderMaterials = OrderMaterialModel::with(['material', 'supplier'])
            ->where('tenant_id', $tenantId)
            ->where('order_id', $ids)
            ->select();

        // 统计总成本和缺料情况
        $totalAmount = 0;
        $shortageCount = 0;
        foreach ($orderMaterials as $om) {
            $totalAmount += $om->estimated_amount;
            if ($om->material && $om->required_quantity > $om->material->stock) {
                $shortageCount++;
            }
        }

        View::assign('order', $order);
        View::assign('orderMaterials', $orderMaterials);
        View::assign('totalAmount', $totalAmount);
        View::assign('shortageCount', $shortageCount);
        View::assign('title', '订单物料清单');
        return $this->fetchWithLayout('mes/order/material_list');
    }
}
