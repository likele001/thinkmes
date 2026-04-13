<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\OrderModelModel;
use app\admin\model\mes\OrderMaterialModel;
use app\admin\model\mes\ProductModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\CustomerModel;
use app\admin\model\mes\BomModel;
use app\admin\model\mes\BomItemModel;
use app\admin\model\mes\MaterialModel;
use app\admin\model\mes\PurchaseRequestModel;
use app\admin\service\WorkflowService;
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
        $workflowStatusFilter = trim((string) $this->request->get('workflow_status_filter', ''));

        $tenantId = $this->getTenantId();
        $workflowInstanceTable = Db::name('workflow_instance')->getTable();
        $workflowStateTable = Db::name('workflow_state')->getTable();

        $query = OrderModel::alias('o')->with(['orderModels.model.product', 'customer'])
            ->order('o.id', 'desc');
        if ($tenantId > 0) {
            $query->where('o.tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('o.tenant_id', $tenantParam);
            }
        }

        if ($orderNo !== '') {
            $query->where('o.order_no', 'like', '%' . $orderNo . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('o.status', (int) $status);
        }

        if ($workflowStatusFilter !== '') {
            $query->leftJoin($workflowInstanceTable . ' wi', 'wi.record_id = o.id AND wi.table_name = "mes_order"', 'left')
                ->leftJoin($workflowStateTable . ' ws', 'ws.id = wi.current_state_id', 'left')
                ->group('o.id');
            if ($workflowStatusFilter === 'not_started') {
                $query->whereNull('wi.id');
            } elseif ($workflowStatusFilter === 'in_progress') {
                $query->whereNotNull('wi.id')->where('wi.is_completed', 0);
            } elseif ($workflowStatusFilter === 'completed') {
                $query->where('wi.is_completed', 1);
            }
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        $workflowService = new WorkflowService(
            $tenantId,
            $this->auth->id ?? 0,
            $this->auth->username ?? ''
        );
        foreach ($list as &$item) {
            $workflowStatus = $workflowService->getCurrentStatus('mes_order', (int) $item['id']);
            $item['workflow_status'] = $workflowStatus['current_state'] ?? '';
            $item['workflow_instance_id'] = $workflowStatus['instance_id'] ?? 0;
            $item['workflow_is_completed'] = $workflowStatus['is_completed'] ?? false;
        }
        unset($item);

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 添加订单
     */
    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $modelData = $this->request->post('models/a');

            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            // 如果 models 是字符串（JSON），尝试解码
            $models = $this->request->post('models');
            if (is_string($models)) {
                $decoded = json_decode($models, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $modelData = $decoded;
                }
            }

            if (empty($modelData)) {
                \think\facade\Db::name('log')->insert([
                    'tenant_id' => $this->getTenantId(),
                    'admin_id' => $this->auth->id ?? 0,
                    'type' => 'error',
                    'content' => '订单添加失败：型号数据为空',
                    'url' => $this->request->url(),
                    'ip' => $this->request->ip(),
                    'create_time' => time(),
                ]);
                return $this->error('型号数据不能为空');
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

            // 确保必填字段有默认值
            $params['customer_name'] = $params['customer_name'] ?? '';
            $params['customer_phone'] = $params['customer_phone'] ?? '';
            $params['order_name'] = $params['order_name'] ?? '未命名订单';

            Db::startTrans();
            try {
                /** @var OrderModel $order */
                $order = OrderModel::create($params);
                
                $totalQuantity = 0;
                foreach ($modelData as $modelItem) {
                    $mid = (int) ($modelItem['model_id'] ?? 0);
                    $qty = (int) ($modelItem['quantity'] ?? 0);
                    if ($mid > 0) {
                        if ($qty <= 0) {
                            $qty = 1;
                        }
                        OrderModelModel::create([
                            'tenant_id' => $tenantId,
                            'order_id' => $order->id,
                            'model_id' => $mid,
                            'quantity' => $qty
                        ]);
                        $totalQuantity += $qty;
                    }
                }

                if ($totalQuantity == 0) {
                    throw new \Exception('订单至少需要包含一个型号及有效数量');
                }

                // 更新订单总数量
                $order->save(['total_quantity' => $totalQuantity]);

                // 自动计算物料需求
                $this->calculateMaterialsWithCost($order->id, $tenantId);

                // 自动检查库存并生成采购申请
                $this->autoGeneratePurchaseRequests($order->id, $tenantId);

                Db::commit();

                // 启动工作流（如果已在后台定义了对应的工作流）
                $this->startOrderWorkflow($order->id, $order->order_no ?: $order->order_name);

                return $this->success('添加成功', ['id' => $order->id]);
            } catch (\Exception $e) {
                Db::rollback();
                \think\facade\Db::name('log')->insert([
                    'tenant_id' => $this->getTenantId(),
                    'admin_id' => $this->auth->id ?? 0,
                    'type' => 'error',
                    'content' => '订单添加失败：' . $e->getMessage(),
                    'url' => $this->request->url(),
                    'ip' => $this->request->ip(),
                    'create_time' => time(),
                ]);
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
        if ($models->isEmpty()) {
            $models = ProductModelModel::with('product')
                ->where('status', 1)
                ->select();
        }
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
            $ids = $this->request->param('id');
        }
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        /** @var OrderModel $row */
        $row = OrderModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('订单不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $modelData = $this->request->post('models/a');

            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            if (empty($modelData)) {
                return $this->error('型号数据不能为空');
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
                    $mid = (int) ($modelItem['model_id'] ?? 0);
                    $qty = (int) ($modelItem['quantity'] ?? 0);
                    if ($mid > 0) {
                        if ($qty <= 0) {
                            $qty = 1;
                        }
                        OrderModelModel::create([
                            'tenant_id' => $tenantId,
                            'order_id' => $ids,
                            'model_id' => $mid,
                            'quantity' => $qty,
                        ]);
                        $totalQuantity += $qty;
                    }
                }

                // 更新订单总数量
                $row->save(['total_quantity' => $totalQuantity]);

                // 重新计算物料需求
                OrderMaterialModel::where('tenant_id', $tenantId)
                    ->where('order_id', $ids)
                    ->delete();
                $this->calculateMaterialsWithCost((int) $ids, $tenantId);

                // 自动检查库存并生成采购申请
                $this->autoGeneratePurchaseRequests((int) $ids, $tenantId);

                Db::commit();
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                Db::rollback();
                return $this->error('编辑失败: ' . $e->getMessage());
            }
        }

        // 获取产品型号列表
        $modelList = [];
        $models = ProductModelModel::with('product')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->select();
        if ($models->isEmpty()) {
            $models = ProductModelModel::with('product')
                ->where('status', 1)
                ->select();
        }
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
            return $this->error('删除失败');
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

        // 保存到订单物料需求表（仅保存需求量>0的，避免出现全0）
        foreach ($materialNeeds as $materialId => $data) {
            $reqQty = (float) ($data['quantity'] ?? 0);
            if ($reqQty <= 0) {
                continue;
            }
            OrderMaterialModel::create([
                'tenant_id' => $tenantId,
                'order_id' => $orderId,
                'material_id' => $materialId,
                'required_quantity' => $reqQty,
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
     * 自动检查库存并生成采购申请，返回本次新生成的申请数量
     */
    private function autoGeneratePurchaseRequests(int $orderId, int $tenantId): int
    {
        $created = 0;
        $orderMaterials = OrderMaterialModel::where('tenant_id', $tenantId)
            ->where('order_id', $orderId)
            ->with(['material'])
            ->select();

        foreach ($orderMaterials as $om) {
            /** @var OrderMaterialModel $om */
            if (!$om->material) {
                continue;
            }

            $material = $om->material;
            $requiredQty = $om->required_quantity;
            $currentStock = $material->stock;

            // 检查是否缺料
            if ($currentStock < $requiredQty) {
                $shortageQty = $requiredQty - $currentStock;
                $om->stock_status = 1;
                $om->save();

                $recentRequest = PurchaseRequestModel::where('tenant_id', $tenantId)
                    ->where('order_material_id', $om->id)
                    ->where('status', '<', 2)
                    ->find();

                $supplierId = $om->supplier_id ?: ($material->default_supplier_id ?? 0);
                if (!$recentRequest && $supplierId) {
                    PurchaseRequestModel::create([
                        'tenant_id' => $tenantId,
                        'request_no' => PurchaseRequestModel::generateRequestNo(),
                        'material_id' => $material->id,
                        'order_id' => $orderId,
                        'order_material_id' => $om->id,
                        'required_quantity' => $shortageQty,
                        'estimated_price' => $material->current_price,
                        'estimated_amount' => $shortageQty * $material->current_price,
                        'supplier_id' => $supplierId,
                        'status' => 0,
                        'remark' => '订单需求自动生成',
                        'create_time' => time(),
                        'update_time' => time(),
                    ]);
                    $om->purchase_status = 1;
                    $om->save();
                    $created++;
                }
            } else {
                // 库存充足
                $om->stock_status = 0; // 已备料
                $om->save();
            }
        }
        return $created;
    }

    /**
     * 启动 MES 订单工作流
     */
    protected function startOrderWorkflow(int $orderId, string $title): void
    {
        try {
            $service = new WorkflowService(
                $this->auth->tenant_id ?? 0,
                $this->auth->id ?? 0,
                $this->auth->username ?? ''
            );
            $service->startWorkflow('mes_order', $orderId, $title);
        } catch (\Exception $e) {
            \think\facade\Db::name('log')->insert([
                'tenant_id' => $this->getTenantId(),
                'admin_id' => $this->auth->id ?? 0,
                'type' => 'workflow',
                'content' => '启动 MES 订单工作流失败：' . $e->getMessage(),
                'url' => $this->request->url(),
                'ip' => $this->request->ip(),
                'create_time' => time(),
            ]);
        }
    }

    /**
     * 申请采购：根据当前订单物料缺料批量生成采购申请（供订单物料清单页「申请采购」按钮调用）
     */
    public function applyPurchase(): Response
    {
        $orderId = (int) $this->request->param('id', 0) ?: (int) $this->request->param('order_id', 0);
        if ($orderId <= 0) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $order = OrderModel::where('tenant_id', $tenantId)->find($orderId);
        if (!$order) {
            return $this->error('订单不存在');
        }
        $created = $this->autoGeneratePurchaseRequests($orderId, $tenantId);
        if ($this->request->isAjax() || $this->request->get('ajax')) {
            return $this->success($created > 0 ? '已生成 ' . $created . ' 条采购申请，请到「采购申请」查看' : '当前缺料已全部有未完成申请，或缺料物料未设置供应商', null, ['created' => $created]);
        }
        $msg = $created > 0 ? '已生成 ' . $created . ' 条采购申请' : '当前无新申请（缺料已申请或物料需先设置供应商）';
        return redirect((string) url('mes/order/materialList', ['id' => $orderId]) . '?toast=' . urlencode($msg));
    }

    /**
     * 单独申请：为某一条订单物料（缺料）生成一条采购申请
     */
    public function applyPurchaseOne(): Response
    {
        $orderMaterialId = (int) $this->request->param('order_material_id', 0);
        if ($orderMaterialId <= 0) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $om = OrderMaterialModel::with(['material'])->where('tenant_id', $tenantId)->find($orderMaterialId);
        if (!$om || !$om->material) {
            return $this->error('订单物料不存在');
        }
        $material = $om->material;
        $requiredQty = $om->required_quantity;
        $currentStock = (float) ($material->stock ?? 0);
        if ($currentStock >= $requiredQty) {
            return $this->error('该物料库存已充足，无需申请');
        }
        $shortageQty = $requiredQty - $currentStock;
        $supplierId = $om->supplier_id ?: ($material->default_supplier_id ?? 0);
        if (!$supplierId) {
            return $this->error('该物料未设置供应商，请先在物料或订单物料中维护供应商');
        }
        $exists = PurchaseRequestModel::where('tenant_id', $tenantId)->where('order_material_id', $orderMaterialId)->where('status', '<', 2)->find();
        if ($exists) {
            return $this->error('该物料已有未完成的采购申请');
        }
        $om->stock_status = 1;
        $om->save();
        PurchaseRequestModel::create([
            'tenant_id' => $tenantId,
            'request_no' => PurchaseRequestModel::generateRequestNo(),
            'material_id' => $material->id,
            'order_id' => $om->order_id,
            'order_material_id' => $om->id,
            'required_quantity' => $shortageQty,
            'estimated_price' => $material->current_price,
            'estimated_amount' => $shortageQty * $material->current_price,
            'supplier_id' => $supplierId,
            'status' => 0,
            'remark' => '订单物料清单单独申请',
            'create_time' => time(),
            'update_time' => time(),
        ]);
        $om->purchase_status = 1;
        $om->save();
        if ($this->request->isAjax() || $this->request->get('ajax')) {
            return $this->success('已生成采购申请', null, ['created' => 1]);
        }
        return redirect((string) url('mes/order/materialList', ['id' => $om->order_id]) . '?toast=' . urlencode('已为该物料生成采购申请'));
    }

    /**
     * 查看订单物料清单
     */
    public function materialList(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            $ids = $this->request->param('id');
        }
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        /** @var OrderModel $order */
        $order = OrderModel::where('tenant_id', $tenantId)->find($ids);
        if (!$order) {
            return $this->error('订单不存在');
        }

        $orderId = (int)$ids;
        $forceRecalc = $this->request->param('recalc') === '1' || $this->request->param('recalc') === 1;

        // 获取物料需求；若从未计算过（如客户下单创建的订单）或全部为 0，则按 BOM 自动计算一次；带 recalc=1 时强制重算
        $orderMaterials = OrderMaterialModel::with(['material', 'supplier'])
            ->where('tenant_id', $tenantId)
            ->where('order_id', $orderId)
            ->select();
        $hasAnyQty = false;
        foreach ($orderMaterials as $om) {
            if ((float)$om->required_quantity > 0) {
                $hasAnyQty = true;
                break;
            }
        }
        if ($forceRecalc || !$hasAnyQty) {
            OrderMaterialModel::where('tenant_id', $tenantId)
                ->where('order_id', $orderId)
                ->delete();
            $this->calculateMaterialsWithCost($orderId, $tenantId);
            $orderMaterials = OrderMaterialModel::with(['material', 'supplier'])
                ->where('tenant_id', $tenantId)
                ->where('order_id', $orderId)
                ->select();
        }

        // 统计总成本和缺料情况，并为每条物料计算缺料数供模板使用
        $totalAmount = 0;
        $shortageCount = 0;
        foreach ($orderMaterials as $om) {
            $totalAmount += $om->estimated_amount;
            $stock = $om->material ? (int)($om->material->getData('stock') ?? 0) : 0;
            $shortage = max(0, $om->required_quantity - $stock);
            $om->setAttr('shortage', $shortage);
            if ($shortage > 0) {
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

    /**
     * Excel 批量导入订单（按 产品名称、型号、型号备注(编号)、数量、客户资料 等）
     */
    public function import(): string|Response
    {
        $tenantId = $this->getTenantId();
        // 仅显示当前租户的客户（传 id+name 列表，模板里用真实 id 作 option value，避免 volist key 为序号）
        $customerList = CustomerModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->order('id', 'desc')
            ->column('customer_name', 'id');
        $customerOptions = [];
        foreach ($customerList ?: [] as $id => $name) {
            $customerOptions[] = ['id' => (int) $id, 'customer_name' => $name];
        }
        View::assign('customerOptions', $customerOptions);

        if ($this->request->isPost()) {
            $file = $this->request->file('file');
            if (!$file || !$file->getOriginalName()) {
                return $this->error('请选择要上传的 Excel 文件');
            }
            $ext = strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION));
            if (!in_array($ext, ['xlsx', 'xls'], true)) {
                return $this->error('仅支持 .xlsx 或 .xls 格式');
            }

            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                // 模板列：A=0 序号, B=1 产品名称, C=2 颜色, D=3 型号(旧模板可能写规格), E=4 数量
                $dataRows = [];
                $startRow = 0;
                if (count($rows) > 0) {
                    $firstCell = isset($rows[0][1]) ? trim((string) $rows[0][1]) : '';
                    if ($firstCell === '产品名称' || $firstCell === '产品名稱') {
                        $startRow = 1;
                    }
                }
                for ($i = $startRow, $len = count($rows); $i < $len; $i++) {
                    $row = $rows[$i];
                    $trimmed = array_map(function ($c) {
                        $s = is_scalar($c) ? (string) $c : '';
                        return trim($s);
                    }, $row);
                    if (array_filter($trimmed)) {
                        $dataRows[] = $trimmed;
                    }
                }

                $colIndex = 0;   // 序号
                $colProduct = 1; // 产品名称
                $colColor = 2;   // 颜色
                $colModel = 3;   // 规格(即型号)，一个产品下多个型号用此列区分
                $colQty = 4;     // 数量

                $orderName = trim((string) $this->request->post('order_name', ''));
                $customerId = (int) $this->request->post('customer_id', 0);
                $deliveryTime = trim((string) $this->request->post('delivery_time', ''));
                $remark = trim((string) $this->request->post('remark', ''));

                if ($customerId <= 0) {
                    return $this->error('请选择客户');
                }
                $importUseCustomerTenant = false; // 是否因平台管理员而使用了客户所属租户
                $customer = CustomerModel::where('tenant_id', $tenantId)->find($customerId);
                if (!$customer) {
                    $other = CustomerModel::find($customerId);
                    if ($other) {
                        // 当前为平台管理员(租户0)时，允许按客户所属租户导入
                        if ($tenantId === 0) {
                            $tenantId = (int) $other->tenant_id;
                            $customer = $other;
                            $importUseCustomerTenant = true;
                        } else {
                            return $this->error('所选客户不在当前租户下，请选择本租户的客户或切换租户后重试（customer_id：' . $customerId . '，当前租户ID：' . $tenantId . '，客户租户ID：' . (int) $other->tenant_id . '）');
                        }
                    } else {
                        return $this->error('所选客户不存在，请确认客户未删除且在当前租户下');
                    }
                }
                if ($customer->status != 1) {
                    return $this->error('所选客户已禁用');
                }
                $customerName = $customer->customer_name;
                $customerPhone = $customer->contact_phone ?? '';

                $productModels = [];
                $skipped = [];
                $remarkLines = []; // 完整产品名称（带-）用于写入订单备注

                foreach ($dataRows as $rowIndex => $row) {
                    $productNameCell = isset($row[$colProduct]) ? trim((string) $row[$colProduct]) : '';
                    $color = isset($row[$colColor]) ? trim((string) $row[$colColor]) : '';
                    $modelIdentifier = isset($row[$colModel]) ? trim((string) $row[$colModel]) : ''; // 规格列 = 型号
                    $quantity = isset($row[$colQty]) ? (int) preg_replace('/[^0-9]/', '', (string) $row[$colQty]) : 0;

                    if ($productNameCell === '' || $quantity <= 0) {
                        continue;
                    }

                    // 产品名称：仅用“-”前部分作为产品名；若型号列为空且产品名带“-”，则用“-”后部分作为型号
                    $productName = $productNameCell;
                    if (strpos($productNameCell, '-') !== false) {
                        $productName = trim((string) substr($productNameCell, 0, strpos($productNameCell, '-')));
                        if ($modelIdentifier === '') {
                            $modelIdentifier = trim((string) substr($productNameCell, strpos($productNameCell, '-') + 1));
                        }
                    }
                    $remarkLines[] = $productNameCell . ' ' . $quantity . '件';

                    // 按“产品 + 型号(规格列) + 颜色”匹配，一个产品对应多个型号
                    $modelId = $this->findModelByProductAndModelAndColor($tenantId, $productName, $modelIdentifier, $color);
                    if ($modelId === null) {
                        $modelId = $this->ensureProductAndModel($tenantId, $productName, $modelIdentifier, $color);
                    }
                    if ($modelId === null) {
                        $skipped[] = '第' . ($rowIndex + 2) . '行：未匹配且无法创建 产品「' . $productName . '」型号「' . $modelIdentifier . '」';
                        continue;
                    }

                    $productModels[] = ['model_id' => $modelId, 'quantity' => $quantity];
                }

                if (empty($productModels)) {
                    $msg = '没有可导入的明细';
                    if (!empty($skipped)) {
                        $msg .= '。' . implode('；', array_slice($skipped, 0, 5));
                        if (count($skipped) > 5) {
                            $msg .= '… 共 ' . count($skipped) . ' 行未匹配';
                        }
                    }
                    return $this->error($msg);
                }

                $totalQty = array_sum(array_column($productModels, 'quantity'));
                $finalOrderName = $orderName !== '' ? $orderName : ('Excel导入-' . date('Y-m-d H:i'));
                $deliveryTs = $deliveryTime !== '' ? strtotime($deliveryTime) : null;
                $remarkBase = $remark !== '' ? $remark : ('Excel导入 ' . date('Y-m-d H:i:s'));
                $finalRemark = $remarkBase . "\n导入明细(原文)：\n" . implode("\n", $remarkLines);

                Db::startTrans();
                try {
                    $order = OrderModel::create([
                        'tenant_id' => $tenantId,
                        'order_no' => OrderModel::generateOrderNo(),
                        'order_name' => $finalOrderName,
                        'customer_id' => $customerId,
                        'customer_name' => $customerName,
                        'customer_phone' => $customerPhone,
                        'total_quantity' => $totalQty,
                        'status' => 0,
                        'delivery_time' => $deliveryTs,
                        'remark' => $finalRemark,
                    ]);

                    $orderId = (int) $order->id;
                    if ($orderId <= 0) {
                        throw new \RuntimeException('订单创建后未获得有效ID');
                    }

                    $now = time();
                    $orderModelRows = [];
                    foreach ($productModels as $item) {
                        $orderModelRows[] = [
                            'tenant_id' => $tenantId,
                            'order_id' => $orderId,
                            'model_id' => (int) $item['model_id'],
                            'quantity' => (int) $item['quantity'],
                            'create_time' => $now,
                        ];
                    }
                    if (!empty($orderModelRows)) {
                        OrderModelModel::insertAll($orderModelRows);
                    }

                    $this->calculateMaterialsWithCost($orderId, $tenantId);
                    $this->autoGeneratePurchaseRequests($orderId, $tenantId);

                    Db::commit();
                } catch (\Throwable $e) {
                    Db::rollback();
                    return $this->error('导入失败：' . $e->getMessage());
                }

                $msg = '导入成功，共 ' . count($productModels) . ' 条明细，总数量 ' . $totalQty;
                if (!empty($skipped)) {
                    $msg .= '；未匹配 ' . count($skipped) . ' 行';
                }
                if (!empty($importUseCustomerTenant)) {
                    $msg .= '。订单所属租户ID：' . $tenantId . '，请在订单列表选择该租户查看';
                }
                return $this->success($msg, ['id' => $order->id]);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                return $this->error('Excel 读取失败：' . $e->getMessage());
            } catch (\Throwable $e) {
                return $this->error('处理失败：' . $e->getMessage());
            }
        }

        View::assign('title', '订单 Excel 导入');
        return $this->fetchWithLayout('mes/order/import');
    }

    /**
     * 导入用：按“产品名称 + 型号(规格列) + 颜色”匹配，一个产品对应多个型号
     */
    private function findModelByProductAndModelAndColor(int $tenantId, string $productName, string $modelIdentifier, string $color): ?int
    {
        $product = ProductModel::where('tenant_id', $tenantId)
            ->where('name', $productName)
            ->where('status', 1)
            ->find();
        if (!$product) {
            return null;
        }

        $query = ProductModelModel::where('tenant_id', $tenantId)
            ->where('product_id', $product->id)
            ->where('status', 1);

        if ($modelIdentifier !== '') {
            $m = (clone $query)->where('model_code', $modelIdentifier)->find();
            if ($m) {
                if ($color === '' || (string) $m->color === $color) {
                    return (int) $m->id;
                }
            }
            $m = (clone $query)->where('name', $modelIdentifier)->find();
            if ($m) {
                if ($color === '' || (string) $m->color === $color) {
                    return (int) $m->id;
                }
            }
        }

        if ($color !== '') {
            $query->where('color', $color);
        }
        $first = $query->find();
        return $first ? (int) $first->id : null;
    }

    /**
     * 根据产品名称、型号名称、型号备注(编号) 解析出 model_id，未找到返回 null（用于其他场景）
     */
    private function findModelByProductAndModel(int $tenantId, string $productName, string $modelName, string $modelCode): ?int
    {
        $product = ProductModel::where('tenant_id', $tenantId)
            ->where('name', $productName)
            ->where('status', 1)
            ->find();
        if (!$product) {
            return null;
        }

        $query = ProductModelModel::where('tenant_id', $tenantId)
            ->where('product_id', $product->id)
            ->where('status', 1);

        if ($modelCode !== '') {
            $m = (clone $query)->where('model_code', $modelCode)->find();
            if ($m) {
                return (int) $m->id;
            }
        }
        if ($modelName !== '') {
            $m = (clone $query)->where('name', $modelName)->find();
            if ($m) {
                return (int) $m->id;
            }
        }
        if ($modelCode === '' && $modelName === '') {
            $first = $query->find();
            if ($first) {
                return (int) $first->id;
            }
        }
        return null;
    }

    /**
     * 导入时若产品/型号不存在则创建。规格列即型号，一个产品下可建多个型号。
     * @return int|null 型号ID，失败返回 null
     */
    private function ensureProductAndModel(int $tenantId, string $productName, string $modelIdentifier, string $color): ?int
    {
        $productName = trim($productName);
        if ($productName === '') {
            return null;
        }

        $product = ProductModel::where('tenant_id', $tenantId)->where('name', $productName)->find();
        if (!$product) {
            $product = ProductModel::create([
                'tenant_id' => $tenantId,
                'name'      => $productName,
                'code'      => $productName,
                'status'    => 1,
            ]);
            if (!$product || (int) $product->id <= 0) {
                return null;
            }
        }

        $productId = (int) $product->id;
        $modelName = $modelIdentifier !== '' ? $modelIdentifier : $productName;
        $modelCode = $modelIdentifier;

        $query = ProductModelModel::where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('status', 1);
        if ($modelCode !== '') {
            $m = (clone $query)->where('model_code', $modelCode)->find();
            if ($m) {
                return (int) $m->id;
            }
        }
        $m = (clone $query)->where('name', $modelName)->find();
        if ($m) {
            return (int) $m->id;
        }
        if ($color !== '') {
            $m = (clone $query)->where('color', $color)->find();
            if ($m) {
                return (int) $m->id;
            }
        }

        $modelRow = ProductModelModel::create([
            'tenant_id'     => $tenantId,
            'product_id'    => $productId,
            'name'          => $modelName,
            'model_code'    => $modelCode,
            'color'         => $color,
            'specification' => '', // 导入中“规格”列即型号，不再写入表里的规格字段
            'status'        => 1,
        ]);
        return $modelRow && (int) $modelRow->id > 0 ? (int) $modelRow->id : null;
    }

    /**
     * 下载订单导入 Excel 模板
     */
    public function downloadTemplate(): Response
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('订单导入');

            $headers = ['序号', '产品名称', '颜色', '型号', '数量'];
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '1', $h);
                $col++;
            }
            $sheet->getStyle('A1:E1')->getFont()->setBold(true);

            $examples = [
                [1, '30013', '白色', '7', 45],
                [2, '30013', '黑色', '10', 30],
            ];
            $row = 2;
            foreach ($examples as $r) {
                $sheet->setCellValue('A' . $row, $r[0]);
                $sheet->setCellValue('B' . $row, $r[1]);
                $sheet->setCellValue('C' . $row, $r[2]);
                $sheet->setCellValue('D' . $row, $r[3]);
                $sheet->setCellValue('E' . $row, $r[4]);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(8);
            $sheet->getColumnDimension('B')->setWidth(16);
            $sheet->getColumnDimension('C')->setWidth(10);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(10);

            $filename = '订单导入模板_' . date('YmdHis') . '.xlsx';
            ob_start();
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            $content = ob_get_clean();
            return response($content, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename*="UTF-8\'\'' . rawurlencode($filename) . '"',
                'Cache-Control'       => 'max-age=0',
            ]);
        } catch (\Throwable $e) {
            return $this->error('模板生成失败：' . $e->getMessage());
        }
    }

    /**
     * 订单生产进度总表：所有订单一眼看到 订单号→产品→总数→已完成→不良→完工率→状态
     */
    public function orderProgress(): string
    {
        $tenantId = $this->getTenantId();
        $orderT = Db::name('mes_order')->getTable();
        $planT = Db::name('mes_production_plan')->getTable();
        $allocT = Db::name('mes_allocation')->getTable();
        $reportT = Db::name('mes_report')->getTable();

        $query = Db::table($orderT . ' o')
            ->leftJoin($planT . ' t', 'o.id = t.order_id')
            ->leftJoin($allocT . ' a', '((a.plan_id = t.id) OR (a.plan_id IS NULL AND a.order_id = o.id))')
            ->leftJoin($reportT . ' r', 'r.allocation_id = a.id AND r.status = 1')
            ->field([
                'o.id',
                'o.order_no',
                'o.order_name as product_name',
                'o.total_quantity as order_num',
                'o.create_time',
                'o.delivery_time',
                'SUM(r.quantity) as finish_num',
                'SUM(CASE WHEN r.quality_status = 2 THEN r.quantity ELSE 0 END) as bad_num',
                'ROUND(IFNULL(SUM(r.quantity) / NULLIF(o.total_quantity, 0) * 100, 0), 1) as progress',
            ])
            ->group('o.id')
            ->order('o.id', 'desc');

        if ($tenantId > 0) {
            $query->where('o.tenant_id', $tenantId);
        } else {
            $tp = (int) $this->request->get('tenant_id', 0);
            if ($tp > 0) {
                $query->where('o.tenant_id', $tp);
            }
        }

        $list = $query->select()->toArray();

        $workflowService = new WorkflowService(
            $tenantId,
            $this->auth->id ?? 0,
            $this->auth->username ?? ''
        );

        foreach ($list as &$item) {
            $item['finish_num'] = (int) ($item['finish_num'] ?? 0);
            $item['bad_num'] = (int) ($item['bad_num'] ?? 0);
            $item['progress'] = round((float) ($item['progress'] ?? 0), 1);
            if ($item['finish_num'] <= 0) {
                $item['status_txt'] = '未开始';
            } elseif ($item['progress'] >= 100) {
                $item['status_txt'] = '已完成';
            } else {
                $item['status_txt'] = '生产中';
            }

            $workflowStatus = $workflowService->getCurrentStatus('mes_order', (int) $item['id']);
            $item['workflow_status'] = $workflowStatus['current_state'] ?? '';
            $item['workflow_instance_id'] = $workflowStatus['instance_id'] ?? 0;
            $item['workflow_is_completed'] = $workflowStatus['is_completed'] ?? false;
        }
        unset($item);

        View::assign('list', $list);
        View::assign('title', '订单生产进度');
        return $this->fetchWithLayout('mes/order/order_progress');
    }

    /**
     * 单个订单 → 按产品、按工序的进度明细，最后总进度
     */
    public function orderProcessDetail(): string|Response
    {
        $orderId = (int) $this->request->param('order_id');
        if ($orderId <= 0) {
            return $this->error('请选择订单');
        }

        $tenantId = $this->getTenantId();
        $order = OrderModel::where('id', $orderId)
            ->when($tenantId > 0, fn ($q) => $q->where('tenant_id', $tenantId))
            ->find();
        if (!$order) {
            return $this->error('订单不存在');
        }

        $planT = Db::name('mes_production_plan')->getTable();
        $allocT = Db::name('mes_allocation')->getTable();
        $processT = Db::name('mes_process')->getTable();
        $reportT = Db::name('mes_report')->getTable();
        $modelT = Db::name('mes_product_model')->getTable();
        $productT = Db::name('mes_product')->getTable();

        $planIds = Db::table($planT)->where('order_id', $orderId)->column('id');

        $query = Db::table($allocT . ' a')
            ->join($processT . ' p', 'p.id = a.process_id')
            ->join($modelT . ' pm', 'pm.id = a.model_id')
            ->leftJoin($productT . ' prod', 'prod.id = pm.product_id')
            ->leftJoin($reportT . ' r', 'r.allocation_id = a.id AND r.status = 1')
            ->field([
                'a.model_id',
                'MAX(prod.name) as product_name',
                'MAX(pm.name) as model_name_only',
                'p.id as process_id',
                'p.name as process_name',
                'p.sort as process_sort',
                'SUM(a.quantity) as plan_num',
                'COALESCE(SUM(r.quantity), 0) as real_num',
                'COALESCE(SUM(CASE WHEN r.quality_status = 2 THEN r.quantity ELSE 0 END), 0) as bad_num',
                'ROUND(IFNULL(SUM(r.quantity) / NULLIF(SUM(a.quantity), 0) * 100, 0), 1) as progress',
            ])
            ->where(function ($q) use ($orderId, $planIds) {
                if (!empty($planIds)) {
                    $q->whereIn('a.plan_id', $planIds)->whereOr(function ($q2) use ($orderId) {
                        $q2->whereNull('a.plan_id')->where('a.order_id', $orderId);
                    });
                } else {
                    $q->whereNull('a.plan_id')->where('a.order_id', $orderId);
                }
            })
            ->group('a.model_id, pm.id, p.id, p.name, p.sort')
            ->order('a.model_id', 'asc')
            ->order('p.sort', 'asc');

        if ($tenantId > 0) {
            $query->where('a.tenant_id', $tenantId);
        }

        $rows = $query->select()->toArray();

        // 按产品聚合为：产品 → 工序列表，并算产品级、订单级合计
        $products = [];
        $orderPlan = 0;
        $orderReal = 0;
        $orderBad = 0;

        foreach ($rows as $row) {
            $planNum = (int) ($row['plan_num'] ?? 0);
            $realNum = (int) ($row['real_num'] ?? 0);
            $badNum = (int) ($row['bad_num'] ?? 0);
            $progress = round((float) ($row['progress'] ?? 0), 1);

            $mid = (int) $row['model_id'];
            $pn = trim((string) ($row['product_name'] ?? ''));
            $mn = trim((string) ($row['model_name_only'] ?? ''));
            $modelName = $pn !== '' && $mn !== '' ? $pn . ' - ' . $mn : ($mn !== '' ? $mn : '型号#' . $mid);

            if (!isset($products[$mid])) {
                $products[$mid] = [
                    'model_id'   => $mid,
                    'model_name' => $modelName,
                    'plan_num'   => 0,
                    'real_num'   => 0,
                    'bad_num'    => 0,
                    'processes'  => [],
                ];
            }

            $products[$mid]['processes'][] = [
                'process_name' => $row['process_name'] ?? '',
                'process_sort' => (int) ($row['process_sort'] ?? 0),
                'plan_num'     => $planNum,
                'real_num'     => $realNum,
                'bad_num'      => $badNum,
                'progress'     => $progress,
            ];
            $products[$mid]['plan_num'] += $planNum;
            $products[$mid]['real_num'] += $realNum;
            $products[$mid]['bad_num'] += $badNum;
            $orderPlan += $planNum;
            $orderReal += $realNum;
            $orderBad += $badNum;
        }

        foreach ($products as &$prod) {
            $prod['progress'] = $prod['plan_num'] > 0
                ? round($prod['real_num'] / $prod['plan_num'] * 100, 1)
                : 0;
        }
        unset($prod);

        $orderTotal = [
            'plan_num' => $orderPlan,
            'real_num' => $orderReal,
            'bad_num'  => $orderBad,
            'progress' => $orderPlan > 0 ? round($orderReal / $orderPlan * 100, 1) : 0,
        ];

        $workflowService = new WorkflowService(
            $tenantId,
            $this->auth->id ?? 0,
            $this->auth->username ?? ''
        );
        $workflowStatus = $workflowService->getCurrentStatus('mes_order', $orderId);
        $workflowInstance = $workflowService->getInstance('mes_order', $orderId);
        $workflowHistory = $workflowService->getInstanceHistory('mes_order', $orderId);
        $workflowTransitions = $workflowService->getAvailableTransitions('mes_order', $orderId);
        $workflowGraph = $workflowService->getWorkflowGraph('mes_order', $orderId);
        $pendingApprovals = [];
        if ($workflowInstance) {
            $pendingApprovals = \app\admin\model\WorkflowApproval::where('instance_id', $workflowInstance->id)
                ->where('approver_id', $this->auth->id ?? 0)
                ->where('status', 'pending')
                ->select()
                ->toArray();
        }

        View::assign('order', $order);
        View::assign('products', array_values($products));
        View::assign('orderTotal', $orderTotal);
        View::assign('workflowStatus', $workflowStatus);
        View::assign('workflowInstance', $workflowInstance);
        View::assign('workflowHistory', $workflowHistory);
        View::assign('workflowTransitions', $workflowTransitions);
        View::assign('workflowGraph', $workflowGraph);
        View::assign('pendingApprovals', $pendingApprovals);
        View::assign('title', '订单进度（按产品·工序） - ' . $order->order_no);
        return $this->fetchWithLayout('mes/order/process_detail');
    }
}
