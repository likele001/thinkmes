<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\ProductionPlanModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\ProcessModel;
use app\admin\model\mes\AllocationQrcodeModel;
use app\admin\model\mes\TraceCodeModel;
use app\common\model\UserModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 分工分配管理
 */
class Allocation extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '分工分配管理');
            return $this->fetchWithLayout('mes/allocation/index');
        }
        
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $tenantId = $this->getTenantId();
        $query = AllocationModel::with(['order', 'model.product', 'process'])
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
        
        $orderId = $this->request->get('order_id');
        if ($orderId) {
            $query->where('order_id', (int) $orderId);
        }
        
        $modelId = $this->request->get('model_id');
        if ($modelId) {
            $query->where('model_id', (int) $modelId);
        }
        
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        
        // 计算完成率
        foreach ($list as &$row) {
            if ($row['quantity'] > 0) {
                $row['completion_rate'] = round(($row['completed_quantity'] / $row['quantity']) * 100, 2);
            } else {
                $row['completion_rate'] = 0;
            }
        }
        
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            
            $tenantId = $this->getTenantId();
            $params['tenant_id'] = $tenantId;
            $params['allocation_code'] = AllocationModel::generateAllocationCode();
            $params['create_time'] = time();
            $params['update_time'] = time();
            
            // 填充默认值，避免数据库 NOT NULL 约束导致失败
            $params['qr_content'] = $params['qr_content'] ?? '';
            $params['qr_image'] = $params['qr_image'] ?? '';
            
            Db::startTrans();
            try {
                $allocation = AllocationModel::create($params);
                $this->createTraceItems($allocation, $tenantId);
                $this->doGenerateQrcode($allocation->id, $tenantId);
                
                Db::commit();
                return $this->success('添加成功', ['id' => $allocation->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('添加失败');
            }
        }
        
        // 获取订单列表
        $tenantId = $this->getTenantId();
        $orders = OrderModel::where('tenant_id', $tenantId)
            ->where('status', '<>', 3)
            ->select();
        $orderList = [];
        foreach ($orders as $order) {
            $orderList[$order->id] = $order->order_name ?: $order->order_no;
        }
        
        // 获取工序列表
        $processList = ProcessModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        
        // 获取员工列表（当前租户下的前端会员）
        $userList = UserModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('nickname', 'id');
        
        View::assign('orderList', $orderList);
        View::assign('processList', $processList ?: []);
        View::assign('userList', $userList ?: []);
        View::assign('title', '添加分工分配');
        return $this->fetchWithLayout('mes/allocation/add');
    }

    public function edit(): string|Response
    {
        $idParam = $this->request->param('ids');
        if ($idParam === null || $idParam === '') {
            $idParam = $this->request->param('id');
        }
        if ($idParam === null || $idParam === '') {
            return $this->error('参数错误');
        }
        $id = (int) $idParam;
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            
            $tenantId = $this->getTenantId();
            $allocation = AllocationModel::where('tenant_id', $tenantId)->find($id);
            if (!$allocation) {
                return $this->error('记录不存在');
            }

            $quantity = isset($params['quantity']) ? (int) $params['quantity'] : (int) $allocation->quantity;
            if ($quantity <= 0) {
                return $this->error('分配数量必须大于0');
            }

            $orderId = isset($params['order_id']) ? (int) $params['order_id'] : (int) $allocation->order_id;
            $modelId = (int) $allocation->model_id;
            $planId = (int) $allocation->plan_id;

            if ($planId > 0 && $modelId > 0) {
                $plan = ProductionPlanModel::where('tenant_id', $tenantId)->find($planId);
                if (!$plan) {
                    return $this->error('对应的生产计划不存在');
                }
                if ((int) $plan->order_id !== $orderId) {
                    return $this->error('分配的订单与生产计划不匹配');
                }

                $otherAllocated = (int) AllocationModel::where('tenant_id', $tenantId)
                    ->where('order_id', $orderId)
                    ->where('plan_id', $planId)
                    ->where('model_id', $modelId)
                    ->where('id', '<>', $allocation->id)
                    ->sum('quantity');

                $remaining = (int) $plan->total_quantity - $otherAllocated;
                if ($remaining <= 0) {
                    return $this->error('该计划下该型号已全部分配，无法继续分配');
                }
                if ($quantity > $remaining) {
                    return $this->error('分配数量不能超过生产计划数量，当前计划剩余可分配数量为：' . $remaining);
                }
            }
            
            $params['update_time'] = time();
            try {
                $allocation->save($params);
                return $this->success('保存成功', ['id' => $allocation->id]);
            } catch (\Exception $e) {
                return $this->error('保存失败');
            }
        }
        
        $tenantId = $this->getTenantId();
        $data = AllocationModel::with(['model.product'])
            ->where('tenant_id', $tenantId)
            ->find($id);
        if (!$data) {
            return $this->error('记录不存在');
        }
        
        // 获取订单列表
        $orders = OrderModel::where('tenant_id', $tenantId)
            ->where('status', '<>', 3)
            ->select();
        $orderList = [];
        foreach ($orders as $order) {
            $orderList[$order->id] = $order->order_name ?: $order->order_no;
        }
        
        // 获取工序列表
        $processList = ProcessModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        
        // 获取员工列表（当前租户下的前端会员）
        $userList = UserModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('nickname', 'id');
        
        View::assign('orderList', $orderList);
        View::assign('processList', $processList ?: []);
        View::assign('userList', $userList ?: []);
        View::assign('data', $data->toArray());
        View::assign('title', '编辑分工分配');
        return $this->fetchWithLayout('mes/allocation/edit');
    }

    public function del(): Response
    {
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('请选择要删除的记录');
        }
        
        $tenantId = $this->getTenantId();
        $ids = is_array($ids) ? $ids : explode(',', (string) $ids);
        
        Db::startTrans();
        try {
            // 检查是否有报工记录
            $reportCount = Db::name('mes_report')
                ->where('tenant_id', $tenantId)
                ->whereIn('allocation_id', $ids)
                ->count();
            if ($reportCount > 0) {
                throw new \Exception("存在 {$reportCount} 条关联的报工记录，无法删除");
            }
            
            // 删除二维码
            AllocationQrcodeModel::where('tenant_id', $tenantId)
                ->whereIn('allocation_id', $ids)
                ->delete();
            
            // 删除分工分配
            AllocationModel::where('tenant_id', $tenantId)
                ->whereIn('id', $ids)
                ->delete();
            
            Db::commit();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败');
        }
    }

    /**
     * 生成二维码
     */
    public function generateQrcode(): Response
    {
        $id = (int) $this->request->post('id');
        $tenantId = $this->getTenantId();
        
        try {
            $this->doGenerateQrcode($id, $tenantId);
            return $this->success('二维码生成成功');
        } catch (\Exception $e) {
            return $this->error('二维码生成失败');
        }
    }

    /**
     * 生成二维码（内部方法）
     */
    protected function doGenerateQrcode(int $allocationId, int $tenantId): void
    {
        $allocation = AllocationModel::with(['order', 'model.product', 'process'])
            ->where('tenant_id', $tenantId)
            ->find($allocationId);
        
        if (!$allocation) {
            throw new \Exception('分工分配不存在');
        }
        
        $this->createTraceItems($allocation, $tenantId);
        
        // 生成二维码内容（URL格式）
        $domain = $this->request->domain();
        $qrContent = $domain . '/index/worker/scan?allocation_id=' . $allocationId;
        
        // 生成二维码图片（使用简单的文本二维码，实际项目中可以使用QRCode库）
        // 这里先存储URL，实际二维码图片可以通过前端或专门的二维码服务生成
        $qrImage = ''; // 可以后续集成二维码生成库
        
        // 更新分工分配的二维码信息
        $allocation->qr_content = $qrContent;
        $allocation->qr_image = $qrImage;
        $allocation->save();
        
        // 保存到二维码表
        $exists = AllocationQrcodeModel::where('tenant_id', $tenantId)
            ->where('allocation_id', $allocationId)
            ->find();
        
        if ($exists) {
            $exists->qrcode_content = $qrContent;
            $exists->qrcode_image = $qrImage;
            $exists->qrcode_url = $qrContent;
            $exists->update_time = time();
            $exists->save();
        } else {
            AllocationQrcodeModel::create([
                'tenant_id' => $tenantId,
                'allocation_id' => $allocationId,
                'qrcode_content' => $qrContent,
                'qrcode_image' => $qrImage,
                'qrcode_url' => $qrContent,
                'status' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ]);
        }
    }

    /**
     * 获取订单的型号列表
     */
    public function getOrderModels(): Response
    {
        $orderId = (int) $this->request->get('order_id');
        if (!$orderId) {
            return $this->error('订单ID不能为空');
        }
        
        $tenantId = $this->getTenantId();
        $order = OrderModel::where('tenant_id', $tenantId)->find($orderId);
        if (!$order) {
            return $this->error('订单不存在');
        }
        
        // 获取订单的型号列表
        $orderModels = Db::name('mes_order_model')
            ->alias('om')
            ->join('mes_product_model pm', 'om.model_id = pm.id')
            ->join('mes_product p', 'pm.product_id = p.id')
            ->where('om.tenant_id', $tenantId)
            ->where('om.order_id', $orderId)
            ->field('pm.id, pm.name, pm.model_code, p.name as product_name, om.quantity')
            ->select();
        
        $result = [];
        foreach ($orderModels as $om) {
            $displayName = $om['product_name'] . ' - ' . $om['name'];
            if ($om['model_code']) {
                $displayName .= ' (' . $om['model_code'] . ')';
            }
            $result[] = [
                'id' => $om['id'],
                'name' => $displayName,
                'quantity' => $om['quantity']
            ];
        }
        
        return $this->success('', $result);
    }

    /**
     * 批量分配
     */
    public function batch(): string|Response
    {
        if ($this->request->isPost()) {
            $orderId = (int) $this->request->post('order_id');
            $planId = (int) $this->request->post('plan_id', 0);

            // 优先按数组方式获取（支持 allocations[0][field] 这种提交）
            $allocations = $this->request->post('allocations/a');
            if (!$allocations) {
                // 兼容前端以 JSON 字符串形式提交的场景
                $allocationsRaw = $this->request->post('allocations');
                if (is_string($allocationsRaw) && $allocationsRaw !== '') {
                    $decoded = json_decode($allocationsRaw, true);
                    if (is_array($decoded)) {
                        $allocations = $decoded;
                    }
                }
            }

            if (!$orderId || !$allocations || !is_array($allocations)) {
                return $this->error('分配数据格式错误');
            }
            
            $tenantId = $this->getTenantId();

            if ($planId > 0) {
                $plan = ProductionPlanModel::where('tenant_id', $tenantId)->find($planId);
                if (!$plan) {
                    return $this->error('对应的生产计划不存在');
                }
                if ((int) $plan->order_id !== $orderId) {
                    return $this->error('分配的订单与生产计划不匹配');
                }
            }

            Db::startTrans();
            try {
                // 按“型号 + 工序”统计本次新增的有效分配数量
                $quantityByModelProcess = [];
                $validCount = 0;

                foreach ($allocations as $item) {
                    if (empty($item['model_id']) || empty($item['process_id']) || empty($item['user_id']) || empty($item['quantity'])) {
                        continue;
                    }

                    $modelId = (int) $item['model_id'];
                    $processId = (int) $item['process_id'];
                    $qty = (int) $item['quantity'];
                    if ($qty <= 0) {
                        continue;
                    }

                    $key = $modelId . ':' . $processId;
                    if (!isset($quantityByModelProcess[$key])) {
                        $quantityByModelProcess[$key] = 0;
                    }
                    $quantityByModelProcess[$key] += $qty;
                    $validCount++;
                }

                if ($validCount === 0) {
                    Db::rollback();
                    return $this->error('没有有效的分配记录，请检查是否选择了型号、工序、员工及数量');
                }

                // 校验：每个“型号+工序”的分配数量不能超过该订单该型号的数量
                foreach ($quantityByModelProcess as $key => $addQty) {
                    list($modelId, $processId) = explode(':', $key);
                    $modelId = (int) $modelId;
                    $processId = (int) $processId;

                    $orderModelQty = Db::name('mes_order_model')
                        ->where('tenant_id', $tenantId)
                        ->where('order_id', $orderId)
                        ->where('model_id', $modelId)
                        ->value('quantity');
                    if ($orderModelQty === null) {
                        $orderModelQty = Db::name('mes_order_model')
                            ->where('order_id', $orderId)
                            ->where('model_id', $modelId)
                            ->value('quantity');
                    }
                    if ($orderModelQty === null) {
                        Db::rollback();
                        return $this->error('该订单中不存在所选产品型号');
                    }

                    // 已分配数量按“订单+型号+工序”统计
                    $allocatedOrder = (int) AllocationModel::where('tenant_id', $tenantId)
                        ->where('order_id', $orderId)
                        ->where('model_id', $modelId)
                        ->where('process_id', $processId)
                        ->sum('quantity');

                    $orderRemaining = (int) $orderModelQty - $allocatedOrder;
                    if ($orderRemaining <= 0) {
                        Db::rollback();
                        return $this->error('该订单下该型号在该工序已全部分配，无法继续分配');
                    }
                    if ($addQty > $orderRemaining) {
                        Db::rollback();
                        return $this->error('分配数量不能超过订单该型号在该工序的数量，当前剩余可分配数量为：' . $orderRemaining);
                    }
                }

                $successCount = 0;
                foreach ($allocations as $item) {
                    if (empty($item['model_id']) || empty($item['process_id']) || empty($item['user_id']) || empty($item['quantity'])) {
                        continue;
                    }

                    $data = [
                        'tenant_id' => $tenantId,
                        'order_id' => $orderId,
                        'model_id' => (int) $item['model_id'],
                        'process_id' => (int) $item['process_id'],
                        'user_id' => (int) $item['user_id'],
                        'quantity' => (int) $item['quantity'],
                        'allocation_code' => AllocationModel::generateAllocationCode(),
                        'status' => 0,
                        'create_time' => time(),
                        'update_time' => time(),
                    ];
                    $allocation = AllocationModel::create($data);
                    $this->createTraceItems($allocation, $tenantId);
                    $this->doGenerateQrcode($allocation->id, $tenantId);
                    $successCount++;
                }

                Db::commit();
                return $this->success("批量分配成功，共分配 {$successCount} 条任务");
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('批量分配失败: ' . $e->getMessage());
            }
        }
        
        // 获取订单列表
        $tenantId = $this->getTenantId();
        $orders = OrderModel::where('tenant_id', $tenantId)
            ->where('status', '<>', 3)
            ->select();
        $orderList = [];
        foreach ($orders as $order) {
            $orderList[$order->id] = $order->order_name ?: $order->order_no;
        }
        
        // 获取工序列表
        $processList = ProcessModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        
        // 获取员工列表（当前租户下的前端会员）
        $userList = UserModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('nickname', 'id');
        
        $orderIdParam = (int) $this->request->get('order_id', 0);
        $planIdParam = (int) $this->request->get('plan_id', 0);
        
        View::assign('orderList', $orderList);
        View::assign('processList', $processList ?: []);
        View::assign('userList', $userList ?: []);
        View::assign('order_id', $orderIdParam);
        View::assign('plan_id', $planIdParam);
        View::assign('title', '批量分工分配');
        return $this->fetchWithLayout('mes/allocation/batch');
    }

    protected function createTraceItems(AllocationModel $allocation, int $tenantId): void
    {
        $quantity = (int) $allocation->quantity;
        if ($quantity <= 0) {
            return;
        }

        $order = OrderModel::where('tenant_id', $tenantId)->find($allocation->order_id);
        $model = ProductModelModel::with(['product'])->where('tenant_id', $tenantId)->find($allocation->model_id);
        $process = ProcessModel::where('tenant_id', $tenantId)->find($allocation->process_id);
        if (!$order || !$model || !$process) {
            return;
        }

        $orderLabel = $order->order_no ?: ($order->order_name ?: '');
        $productName = '';
        if (isset($model->product) && $model->product) {
            $productName = $model->product->name ?? '';
        }
        $modelName = $model->name ?? '';
        $modelCode = $model->model_code ?? '';
        $fullModel = $productName ? ($productName . ' - ' . $modelName) : $modelName;
        if ($modelCode) {
            $fullModel .= ' (' . $modelCode . ')';
        }
        $prefix = $orderLabel . '-' . $fullModel . '-' . $process->name . '-';

        $existingForAllocation = TraceCodeModel::where('tenant_id', $tenantId)
            ->where('allocation_id', $allocation->id)
            ->column('item_no');
        $alreadyCount = count($existingForAllocation);
        if ($alreadyCount >= $quantity) {
            return;
        }

        $need = $quantity - $alreadyCount;

        $existingNos = TraceCodeModel::where('tenant_id', $tenantId)
            ->where('order_id', $allocation->order_id)
            ->where('model_id', $allocation->model_id)
            ->where('process_id', $allocation->process_id)
            ->column('item_no');

        $items = [];
        $current = 1;
        $existingSet = $existingNos ? array_flip($existingNos) : [];
        while (count($items) < $need) {
            $itemNo = $prefix . str_pad((string) $current, 4, '0', STR_PAD_LEFT);
            if (!isset($existingSet[$itemNo])) {
                $items[] = $itemNo;
                $existingSet[$itemNo] = true;
            }
            $current++;
        }

        $now = time();
        $rows = [];
        foreach ($items as $itemNo) {
            $rows[] = [
                'tenant_id' => $tenantId,
                'trace_code' => TraceCodeModel::generateTraceCode(),
                'report_id' => 0,
                'allocation_id' => $allocation->id,
                'order_id' => $allocation->order_id,
                'model_id' => $allocation->model_id,
                'process_id' => $allocation->process_id,
                'user_id' => $allocation->user_id,
                'item_no' => $itemNo,
                'qrcode_image' => '',
                'qrcode_url' => '',
                'scan_count' => 0,
                'status' => 1,
                'create_time' => $now,
                'update_time' => $now,
            ];
        }

        TraceCodeModel::insertAll($rows);
    }
}
