<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ProductionPlanModel;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\ProcessModel;
use app\admin\model\mes\ProductModel;
use app\admin\model\mes\OrderModelModel;
use app\common\model\UserModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 生产计划管理
 */
class ProductionPlan extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '生产计划管理');
            return $this->fetchWithLayout('mes/production_plan/index');
        }
        
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $tenantId = $this->getTenantId();
        $query = ProductionPlanModel::with(['order', 'model.product'])
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
        
        // 计算进度
        foreach ($list as &$row) {
            if ($row['total_quantity'] > 0) {
                $row['progress'] = round(($row['completed_quantity'] / $row['total_quantity']) * 100, 2);
            } else {
                $row['progress'] = 0;
            }
        }
        
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 查看生产计划关联的分工分配
     */
    public function allocations(): string|Response
     {
        $id = (int) $this->request->get('id');
        if (!$id) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $plan = ProductionPlanModel::with(['order', 'model.product'])
            ->where('tenant_id', $tenantId)
            ->find($id);
        if (!$plan) {
            return $this->error('生产计划不存在');
        }

        $allocations = AllocationModel::with(['process', 'user'])
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($plan) {
                $q->where('plan_id', $plan->id)
                    ->whereOr(function ($q2) use ($plan) {
                        $q2->whereNull('plan_id')
                            ->where('order_id', $plan->order_id)
                            ->where('model_id', $plan->model_id);
                    });
            })
            ->order('id', 'asc')
            ->select();

        View::assign('plan', $plan);
        View::assign('allocations', $allocations);
        View::assign('title', '查看分工');
        return $this->fetchWithLayout('mes/production_plan/allocations');
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
            $params['plan_code'] = ProductionPlanModel::generatePlanCode();
            $params['plan_name'] = $params['plan_name'] ?? '未命名计划';
            $params['order_id'] = (int) ($params['order_id'] ?? 0);
            $params['model_id'] = (int) ($params['model_id'] ?? 0);
            $params['create_time'] = time();
            $params['update_time'] = time();
            
            if (!empty($params['planned_start_time'])) {
                $startRaw = (string) $params['planned_start_time'];
                if (preg_match('/^\d+$/', $startRaw)) {
                    $params['planned_start_time'] = (int) $startRaw;
                } else {
                    $startRaw = str_replace('T', ' ', $startRaw);
                    $timestamp = strtotime($startRaw);
                    $params['planned_start_time'] = $timestamp !== false ? $timestamp : 0;
                }
            }
            if (!empty($params['planned_end_time'])) {
                $endRaw = (string) $params['planned_end_time'];
                if (preg_match('/^\d+$/', $endRaw)) {
                    $params['planned_end_time'] = (int) $endRaw;
                } else {
                    $endRaw = str_replace('T', ' ', $endRaw);
                    $timestamp = strtotime($endRaw);
                    $params['planned_end_time'] = $timestamp !== false ? $timestamp : 0;
                }
            }
            
            if ($params['order_id'] <= 0 || $params['model_id'] <= 0) {
                return $this->error('请选择订单和产品型号');
            }
            $totalQty = (int) ($params['total_quantity'] ?? 0);
            if ($totalQty <= 0) {
                return $this->error('计划数量必须大于0');
            }

            $orderModel = Db::name('mes_order_model')
                ->where('tenant_id', $tenantId)
                ->where('order_id', $params['order_id'])
                ->where('model_id', $params['model_id'])
                ->find();
            if (!$orderModel) {
                return $this->error('该订单中不存在所选产品型号');
            }

            $plannedSum = (int) Db::name('mes_production_plan')
                ->where('tenant_id', $tenantId)
                ->where('order_id', $params['order_id'])
                ->where('model_id', $params['model_id'])
                ->sum('total_quantity');

            if ($plannedSum + $totalQty > (int) $orderModel['quantity']) {
                $remaining = max(0, (int) $orderModel['quantity'] - $plannedSum);
                return $this->error('计划数量不能超过订单该型号数量，剩余可计划数量为：' . $remaining);
            }

            // 填充默认值，避免数据库 NOT NULL 约束导致失败
            $params['plan_name'] = $params['plan_name'] ?? '未命名计划';
            $params['order_id'] = (int) ($params['order_id'] ?? 0);
            $params['model_id'] = (int) ($params['model_id'] ?? 0);
            if (empty($params['plan_code'])) {
                $params['plan_code'] = 'PP' . date('YmdHis') . rand(1000, 9999);
            }
            
            try {
                $plan = ProductionPlanModel::create($params);
                return $this->success('添加成功', ['id' => $plan->id]);
            } catch (\Exception $e) {
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
        
        View::assign('orderList', $orderList);
        View::assign('title', '添加生产计划');
        return $this->fetchWithLayout('mes/production_plan/add');
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
            $plan = ProductionPlanModel::where('tenant_id', $tenantId)->find($id);
            if (!$plan) {
                return $this->error('记录不存在');
            }
            
            $params['order_id'] = isset($params['order_id']) ? (int) $params['order_id'] : (int) $plan->order_id;
            $params['model_id'] = isset($params['model_id']) ? (int) $params['model_id'] : (int) $plan->model_id;
            $totalQty = (int) ($params['total_quantity'] ?? $plan->total_quantity);
            if ($params['order_id'] <= 0 || $params['model_id'] <= 0) {
                return $this->error('请选择订单和产品型号');
            }
            if ($totalQty <= 0) {
                return $this->error('计划数量必须大于0');
            }

            $orderModel = Db::name('mes_order_model')
                ->where('tenant_id', $tenantId)
                ->where('order_id', $params['order_id'])
                ->where('model_id', $params['model_id'])
                ->find();
            if (!$orderModel) {
                return $this->error('该订单中不存在所选产品型号');
            }

            $otherPlanned = (int) Db::name('mes_production_plan')
                ->where('tenant_id', $tenantId)
                ->where('order_id', $params['order_id'])
                ->where('model_id', $params['model_id'])
                ->where('id', '<>', $id)
                ->sum('total_quantity');

            if ($otherPlanned + $totalQty > (int) $orderModel['quantity']) {
                $remaining = max(0, (int) $orderModel['quantity'] - $otherPlanned);
                return $this->error('计划数量不能超过订单该型号数量，剩余可计划数量为：' . $remaining);
            }

            if (!empty($params['planned_start_time'])) {
                $startRaw = (string) $params['planned_start_time'];
                if (preg_match('/^\d+$/', $startRaw)) {
                    $params['planned_start_time'] = (int) $startRaw;
                } else {
                    $startRaw = str_replace('T', ' ', $startRaw);
                    $timestamp = strtotime($startRaw);
                    $params['planned_start_time'] = $timestamp !== false ? $timestamp : 0;
                }
            }
            if (!empty($params['planned_end_time'])) {
                $endRaw = (string) $params['planned_end_time'];
                if (preg_match('/^\d+$/', $endRaw)) {
                    $params['planned_end_time'] = (int) $endRaw;
                } else {
                    $endRaw = str_replace('T', ' ', $endRaw);
                    $timestamp = strtotime($endRaw);
                    $params['planned_end_time'] = $timestamp !== false ? $timestamp : 0;
                }
            }
            
            $params['update_time'] = time();
            try {
                $plan->save($params);
                return $this->success('保存成功', ['id' => $plan->id]);
            } catch (\Exception $e) {
                return $this->error('保存失败');
            }
        }
        
        $tenantId = $this->getTenantId();
        $data = ProductionPlanModel::where('tenant_id', $tenantId)->find($id);
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
        
        View::assign('orderList', $orderList);
        View::assign('data', $data->toArray());
        View::assign('title', '编辑生产计划');
        return $this->fetchWithLayout('mes/production_plan/edit');
    }

    public function del(): Response
    {
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('请选择要删除的记录');
        }
        
        $tenantId = $this->getTenantId();
        $ids = is_array($ids) ? $ids : explode(',', (string) $ids);
        
        try {
            ProductionPlanModel::where('tenant_id', $tenantId)
                ->whereIn('id', $ids)
                ->delete();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败');
        }
    }

    /**
     * 获取订单的型号列表（用于生产计划）
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
     * 生产进度统计（按计划）
     */
    public function progressStats(): string|Response
    {
        $id = (int) $this->request->get('id');
        if (!$id) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $plan = ProductionPlanModel::with(['order', 'model.product'])
            ->where('tenant_id', $tenantId)
            ->find($id);
        if (!$plan) {
            return $this->error('生产计划不存在');
        }

        $stats = $this->calculatePlanStats($plan, $tenantId);

        // 回写计划完成数量和进度
        $plan->completed_quantity = (int) $stats['total_reported'];
        if ($plan->total_quantity > 0) {
            $plan->progress = round(($plan->completed_quantity / $plan->total_quantity) * 100, 2);
        } else {
            $plan->progress = 0;
        }
        $plan->save();

        View::assign('plan', $plan);
        View::assign('stats', $stats);
        View::assign('title', '生产进度统计');
        return $this->fetchWithLayout('mes/production_plan/progress_stats');
    }

    /**
     * 生产进度跟踪看板（多维统计）
     */
    public function progress(): string|Response
    {
        $tenantId = $this->getTenantId();

        $orderId = $this->request->get('order_id', '');
        $planId = $this->request->get('plan_id', '');
        $processId = $this->request->get('process_id', '');
        $userId = $this->request->get('user_id', '');

        $where = [];
        if ($tenantId > 0) {
            $where['tenant_id'] = $tenantId;
        }
        if ($orderId !== '' && $orderId !== null) {
            $where['order_id'] = (int) $orderId;
        }
        if ($planId !== '' && $planId !== null) {
            $where['id'] = (int) $planId;
        }

        $plans = ProductionPlanModel::with(['order'])
            ->where($where)
            ->select();

        $orderStats = [];
        $processStats = [];
        $employeeStats = [];
        $productStats = [];

        $overallStats = [
            'total_orders' => 0,
            'total_plans' => 0,
            'total_allocations' => 0,
            'total_reports' => 0,
            'total_quantity' => 0,
            'completed_quantity' => 0,
            'overall_completion_rate' => 0,
        ];

        $processedOrders = [];

        foreach ($plans as $plan) {
            $overallStats['total_plans']++;
            $overallStats['total_quantity'] += (int) $plan->total_quantity;

            $planCompletedQuantity = 0;

            $allocationsQuery = AllocationModel::with(['process', 'user', 'model.product', 'order'])
                ->where('tenant_id', $tenantId)
                ->where(function ($q) use ($plan) {
                    $q->where('plan_id', $plan->id)
                        ->whereOr(function ($q2) use ($plan) {
                            $q2->whereNull('plan_id')
                                ->where('order_id', $plan->order_id)
                                ->where('model_id', $plan->model_id);
                        });
                });

            if ($processId !== '' && $processId !== null) {
                $allocationsQuery->where('process_id', (int) $processId);
            }
            if ($userId !== '' && $userId !== null) {
                $allocationsQuery->where('user_id', (int) $userId);
            }

            $allocations = $allocationsQuery->select();

            foreach ($allocations as $allocation) {
                $overallStats['total_allocations']++;

                $reportsQuery = ReportModel::where('tenant_id', $tenantId)
                    ->where('allocation_id', $allocation->id)
                    ->where('status', 1);
                $completedQuantity = (int) $reportsQuery->sum('quantity');

                $planCompletedQuantity += $completedQuantity;
                $overallStats['total_reports'] += $completedQuantity;

                if ($allocation->process) {
                    $processName = $allocation->process->name;
                    if (!isset($processStats[$processName])) {
                        $processStats[$processName] = [
                            'process_name' => $processName,
                            'total_allocations' => 0,
                            'total_quantity' => 0,
                            'completed_quantity' => 0,
                            'completion_rate' => 0,
                            'allocations' => [],
                        ];
                    }

                    $processStats[$processName]['total_allocations']++;
                    $processStats[$processName]['total_quantity'] += (int) $allocation->quantity;
                    $processStats[$processName]['completed_quantity'] += $completedQuantity;

                    $processStats[$processName]['allocations'][] = [
                        'allocation_id' => $allocation->id,
                        'plan_code' => $plan->plan_code,
                        'employee_name' => $allocation->user ? $allocation->user->nickname : '',
                        'allocated_quantity' => (int) $allocation->quantity,
                        'completed_quantity' => $completedQuantity,
                        'completion_rate' => $allocation->quantity > 0 ? round(($completedQuantity / $allocation->quantity) * 100, 1) : 0,
                        'status' => $allocation->status,
                    ];
                }

                if ($allocation->user) {
                    $empId = $allocation->user->id;
                    $empName = $allocation->user->nickname;

                    if (!isset($employeeStats[$empId])) {
                        $employeeStats[$empId] = [
                            'user_id' => $empId,
                            'user_name' => $empName,
                            'total_allocations' => 0,
                            'total_quantity' => 0,
                            'completed_quantity' => 0,
                            'completion_rate' => 0,
                            'allocations' => [],
                        ];
                    }

                    $employeeStats[$empId]['total_allocations']++;
                    $employeeStats[$empId]['total_quantity'] += (int) $allocation->quantity;
                    $employeeStats[$empId]['completed_quantity'] += $completedQuantity;

                    $employeeStats[$empId]['allocations'][] = [
                        'allocation_id' => $allocation->id,
                        'plan_code' => $plan->plan_code,
                        'process_name' => $allocation->process ? $allocation->process->name : '',
                        'allocated_quantity' => (int) $allocation->quantity,
                        'completed_quantity' => $completedQuantity,
                        'completion_rate' => $allocation->quantity > 0 ? round(($completedQuantity / $allocation->quantity) * 100, 1) : 0,
                        'status' => $allocation->status,
                    ];
                }

                if ($allocation->model && $allocation->model->product) {
                    $productId = $allocation->model->product->id;
                    $productName = $allocation->model->product->name;
                    $modelName = $allocation->model->name;

                    if (!isset($productStats[$productId])) {
                        $productStats[$productId] = [
                            'product_id' => $productId,
                            'product_name' => $productName,
                            'total_plans' => 0,
                            'total_quantity' => 0,
                            'completed_quantity' => 0,
                            'completion_rate' => 0,
                            'models' => [],
                        ];
                    }

                    $productStats[$productId]['total_plans']++;
                    $productStats[$productId]['total_quantity'] += (int) $plan->total_quantity;
                    $productStats[$productId]['completed_quantity'] += $completedQuantity;

                    if (!isset($productStats[$productId]['models'][$modelName])) {
                        $productStats[$productId]['models'][$modelName] = [
                            'model_name' => $modelName,
                            'total_quantity' => 0,
                            'completed_quantity' => 0,
                            'processes' => [],
                        ];
                    }

                    $productStats[$productId]['models'][$modelName]['total_quantity'] += (int) $allocation->quantity;
                    $productStats[$productId]['models'][$modelName]['completed_quantity'] += $completedQuantity;

                    $pName = $allocation->process ? $allocation->process->name : '';
                    if (!isset($productStats[$productId]['models'][$modelName]['processes'][$pName])) {
                        $productStats[$productId]['models'][$modelName]['processes'][$pName] = [
                            'process_name' => $pName,
                            'allocated_quantity' => 0,
                            'completed_quantity' => 0,
                            'completion_rate' => 0,
                        ];
                    }

                    $productStats[$productId]['models'][$modelName]['processes'][$pName]['allocated_quantity'] += (int) $allocation->quantity;
                    $productStats[$productId]['models'][$modelName]['processes'][$pName]['completed_quantity'] += $completedQuantity;
                }
            }

            $overallStats['completed_quantity'] += $planCompletedQuantity;

            if ($plan->order) {
                $oid = $plan->order->id;
                if (!isset($processedOrders[$oid])) {
                    $processedOrders[$oid] = true;
                    $overallStats['total_orders']++;

                    $orderStats[$oid] = [
                        'order_id' => $oid,
                        'order_name' => $plan->order->order_name ?? $plan->order->order_no,
                        'order_no' => $plan->order->order_no,
                        'total_plans' => 0,
                        'total_quantity' => 0,
                        'completed_quantity' => 0,
                        'completion_rate' => 0,
                        'plans' => [],
                    ];
                }

                $orderStats[$oid]['total_plans']++;
                $orderStats[$oid]['total_quantity'] += (int) $plan->total_quantity;
                $orderStats[$oid]['completed_quantity'] += $planCompletedQuantity;

                $orderStats[$oid]['plans'][] = [
                    'plan_id' => $plan->id,
                    'plan_code' => $plan->plan_code,
                    'plan_name' => $plan->plan_name,
                    'product_name' => $plan->model ? ($plan->model->product->name ?? '') : '',
                    'product_model' => $plan->model ? $plan->model->name : '',
                    'total_quantity' => (int) $plan->total_quantity,
                    'actual_quantity' => $planCompletedQuantity,
                    'completion_rate' => $plan->total_quantity > 0 ? round(($planCompletedQuantity / $plan->total_quantity) * 100, 1) : 0,
                    'status' => $plan->status,
                ];
            }
        }

        foreach ($orderStats as &$order) {
            if ($order['total_quantity'] > 0) {
                $order['completion_rate'] = round(($order['completed_quantity'] / $order['total_quantity']) * 100, 1);
            }
        }
        unset($order);

        foreach ($processStats as &$ps) {
            if ($ps['total_quantity'] > 0) {
                $ps['completion_rate'] = round(($ps['completed_quantity'] / $ps['total_quantity']) * 100, 1);
            }
        }
        unset($ps);

        foreach ($employeeStats as &$emp) {
            if ($emp['total_quantity'] > 0) {
                $emp['completion_rate'] = round(($emp['completed_quantity'] / $emp['total_quantity']) * 100, 1);
            }
        }
        unset($emp);

        foreach ($productStats as &$product) {
            if ($product['total_quantity'] > 0) {
                $product['completion_rate'] = round(($product['completed_quantity'] / $product['total_quantity']) * 100, 1);
            }
            foreach ($product['models'] as &$model) {
                if ($model['total_quantity'] > 0) {
                    $model['completion_rate'] = round(($model['completed_quantity'] / $model['total_quantity']) * 100, 1);
                }
                foreach ($model['processes'] as &$p) {
                    if ($p['allocated_quantity'] > 0) {
                        $p['completion_rate'] = round(($p['completed_quantity'] / $p['allocated_quantity']) * 100, 1);
                    }
                }
                unset($p);
            }
            unset($model);
        }
        unset($product);

        if ($overallStats['total_quantity'] > 0) {
            $overallStats['overall_completion_rate'] = round(($overallStats['completed_quantity'] / $overallStats['total_quantity']) * 100, 1);
        }

        $orderListQuery = OrderModel::whereRaw('1=1');
        $planListQuery = ProductionPlanModel::whereRaw('1=1');
        $processListQuery = ProcessModel::whereRaw('1=1');
        $userListQuery = UserModel::whereRaw('1=1');

        if ($tenantId > 0) {
            $orderListQuery->where('tenant_id', $tenantId);
            $planListQuery->where('tenant_id', $tenantId);
            $processListQuery->where('tenant_id', $tenantId);
            $userListQuery->where('tenant_id', $tenantId);
        }

        $orderList = $orderListQuery->column('order_name', 'id');
        foreach ($orderList as $id => $name) {
            if (!$name) {
                $orderList[$id] = OrderModel::where('id', $id)->value('order_no');
            }
        }

        $planList = $planListQuery->column('plan_name', 'id');
        $processList = $processListQuery->column('name', 'id');
        $userList = $userListQuery->column('nickname', 'id');

        View::assign([
            'orderStats' => $orderStats,
            'processStats' => $processStats,
            'employeeStats' => $employeeStats,
            'productStats' => $productStats,
            'overallStats' => $overallStats,
            'orderList' => $orderList,
            'planList' => $planList,
            'processList' => $processList,
            'userList' => $userList,
            'filters' => [
                'order_id' => $orderId,
                'plan_id' => $planId,
                'process_id' => $processId,
                'user_id' => $userId,
            ],
        ]);

        View::assign('title', '生产进度看板');
        return $this->fetchWithLayout('mes/production_plan/progress');
    }

    public function getOrderDetails(): Response
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

        $plans = ProductionPlanModel::where('tenant_id', $tenantId)
            ->where('order_id', $orderId)
            ->select();

        $planDetails = [];
        foreach ($plans as $plan) {
            $allocations = AllocationModel::with(['process', 'user'])
                ->where('tenant_id', $tenantId)
                ->where('order_id', $plan->order_id)
                ->where('model_id', $plan->model_id)
                ->select();

            $allocationDetails = [];
            foreach ($allocations as $allocation) {
                $reports = ReportModel::where('tenant_id', $tenantId)
                    ->where('allocation_id', $allocation->id)
                    ->where('status', 1)
                    ->sum('quantity');

                $quantity = (int) $allocation->quantity;
                $completed = (int) $reports;

                $allocationDetails[] = [
                    'allocation_id' => $allocation->id,
                    'process_name' => $allocation->process ? $allocation->process->name : '',
                    'employee_name' => $allocation->user ? $allocation->user->nickname : '',
                    'allocated_quantity' => $quantity,
                    'completed_quantity' => $completed,
                    'completion_rate' => $quantity > 0 ? round(($completed / $quantity) * 100, 1) : 0,
                    'status' => $allocation->status,
                ];
            }

            $planDetails[] = [
                'plan_id' => $plan->id,
                'plan_code' => $plan->plan_code,
                'plan_name' => $plan->plan_name,
                'product_name' => '',
                'product_model' => '',
                'total_quantity' => (int) $plan->total_quantity,
                'allocations' => $allocationDetails,
            ];
        }

        return $this->success('', [
            'order' => $order,
            'plans' => $planDetails,
        ]);
    }

    public function getProductDetails(): Response
    {
        $productId = (int) $this->request->get('product_id');
        if (!$productId) {
            return $this->error('产品ID不能为空');
        }

        $tenantId = $this->getTenantId();
        $product = ProductModel::where('tenant_id', $tenantId)->find($productId);
        if (!$product) {
            return $this->error('产品不存在');
        }

        $models = ProductModelModel::where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->select();

        $modelDetails = [];
        foreach ($models as $model) {
            $allocations = AllocationModel::with(['process'])
                ->where('tenant_id', $tenantId)
                ->where('model_id', $model->id)
                ->select();

            $processDetails = [];
            foreach ($allocations as $allocation) {
                $processName = $allocation->process ? $allocation->process->name : '';
                if (!isset($processDetails[$processName])) {
                    $processDetails[$processName] = [
                        'process_name' => $processName,
                        'allocated_quantity' => 0,
                        'completed_quantity' => 0,
                        'completion_rate' => 0,
                    ];
                }

                $processDetails[$processName]['allocated_quantity'] += (int) $allocation->quantity;

                $reports = ReportModel::where('tenant_id', $tenantId)
                    ->where('allocation_id', $allocation->id)
                    ->where('status', 1)
                    ->sum('quantity');
                $processDetails[$processName]['completed_quantity'] += (int) $reports;
            }

            foreach ($processDetails as &$process) {
                if ($process['allocated_quantity'] > 0) {
                    $process['completion_rate'] = round(($process['completed_quantity'] / $process['allocated_quantity']) * 100, 1);
                }
            }
            unset($process);

            $modelDetails[] = [
                'model_id' => $model->id,
                'model_name' => $model->name,
                'total_quantity' => array_sum(array_column($processDetails, 'allocated_quantity')),
                'completed_quantity' => array_sum(array_column($processDetails, 'completed_quantity')),
                'completion_rate' => 0,
                'processes' => array_values($processDetails),
            ];
        }

        foreach ($modelDetails as &$m) {
            if ($m['total_quantity'] > 0) {
                $m['completion_rate'] = round(($m['completed_quantity'] / $m['total_quantity']) * 100, 1);
            }
        }
        unset($m);

        return $this->success('', [
            'product' => $product,
            'models' => $modelDetails,
        ]);
    }

    public function getProcessDetails(): Response
    {
        $processName = (string) $this->request->get('process_name');
        if ($processName === '') {
            return $this->error('工序名称不能为空');
        }

        $tenantId = $this->getTenantId();
        $process = ProcessModel::where('tenant_id', $tenantId)
            ->where('name', $processName)
            ->find();
        if (!$process) {
            return $this->error('工序不存在');
        }

        $allocations = AllocationModel::with(['user', 'order', 'model.product'])
            ->where('tenant_id', $tenantId)
            ->where('process_id', $process->id)
            ->select();

        $allocationDetails = [];
        foreach ($allocations as $allocation) {
            $reports = ReportModel::where('tenant_id', $tenantId)
                ->where('allocation_id', $allocation->id)
                ->where('status', 1)
                ->sum('quantity');

            $quantity = (int) $allocation->quantity;
            $completed = (int) $reports;

            $allocationDetails[] = [
                'allocation_id' => $allocation->id,
                'order_name' => $allocation->order ? ($allocation->order->order_name ?? $allocation->order->order_no) : '',
                'product_name' => $allocation->model && $allocation->model->product ? $allocation->model->product->name : '',
                'model_name' => $allocation->model ? $allocation->model->name : '',
                'employee_name' => $allocation->user ? $allocation->user->nickname : '',
                'allocated_quantity' => $quantity,
                'completed_quantity' => $completed,
                'completion_rate' => $quantity > 0 ? round(($completed / $quantity) * 100, 1) : 0,
                'status' => $allocation->status,
            ];
        }

        return $this->success('', [
            'process' => $process,
            'allocations' => $allocationDetails,
        ]);
    }

    public function getEmployeeDetails(): Response
    {
        $userId = (int) $this->request->get('user_id');
        if (!$userId) {
            return $this->error('员工ID不能为空');
        }

        $tenantId = $this->getTenantId();
        $user = UserModel::where('tenant_id', $tenantId)->find($userId);
        if (!$user) {
            return $this->error('员工不存在');
        }

        $allocations = AllocationModel::with(['process', 'order', 'model.product'])
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->select();

        $allocationDetails = [];
        foreach ($allocations as $allocation) {
            $reports = ReportModel::where('tenant_id', $tenantId)
                ->where('allocation_id', $allocation->id)
                ->where('status', 1)
                ->sum('quantity');

            $quantity = (int) $allocation->quantity;
            $completed = (int) $reports;

            $allocationDetails[] = [
                'allocation_id' => $allocation->id,
                'order_name' => $allocation->order ? ($allocation->order->order_name ?? $allocation->order->order_no) : '',
                'product_name' => $allocation->model && $allocation->model->product ? $allocation->model->product->name : '',
                'model_name' => $allocation->model ? $allocation->model->name : '',
                'process_name' => $allocation->process ? $allocation->process->name : '',
                'allocated_quantity' => $quantity,
                'completed_quantity' => $completed,
                'completion_rate' => $quantity > 0 ? round(($completed / $quantity) * 100, 1) : 0,
                'status' => $allocation->status,
            ];
        }

        return $this->success('', [
            'user' => $user,
            'allocations' => $allocationDetails,
        ]);
    }

    /**
     * 计算指定计划的进度统计
     */
    protected function calculatePlanStats(ProductionPlanModel $plan, int $tenantId): array
    {
        $allocations = AllocationModel::with(['process', 'user'])
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($plan) {
                $q->where('plan_id', $plan->id)
                    ->whereOr(function ($q2) use ($plan) {
                        $q2->whereNull('plan_id')
                            ->where('order_id', $plan->order_id)
                            ->where('model_id', $plan->model_id);
                    });
            })
            ->select();

        $stats = [
            'total_allocated' => 0,
            'total_reported' => 0,
            'total_hours' => 0,
            'total_wage' => 0,
            'process_stats' => [],
            'worker_stats' => [],
        ];

        foreach ($allocations as $allocation) {
            $stats['total_allocated'] += (int) $allocation->quantity;

            $reports = ReportModel::where('tenant_id', $tenantId)
                ->where('allocation_id', $allocation->id)
                ->where('status', 1)
                ->select();

            $allocationReported = 0;
            $allocationHours = 0.0;
            $allocationWage = 0.0;

            foreach ($reports as $report) {
                $allocationReported += (int) $report->quantity;
                $allocationHours += (float) $report->work_hours;
                $allocationWage += (float) $report->wage;
            }

            $stats['total_reported'] += $allocationReported;
            $stats['total_hours'] += $allocationHours;
            $stats['total_wage'] += $allocationWage;

            $processName = $allocation->process ? $allocation->process->name : '未知工序';
            if (!isset($stats['process_stats'][$processName])) {
                $stats['process_stats'][$processName] = [
                    'allocated' => 0,
                    'reported' => 0,
                    'hours' => 0.0,
                    'completion_rate' => 0.0,
                ];
            }
            $stats['process_stats'][$processName]['allocated'] += (int) $allocation->quantity;
            $stats['process_stats'][$processName]['reported'] += $allocationReported;
            $stats['process_stats'][$processName]['hours'] += $allocationHours;

            $workerName = $allocation->user ? $allocation->user->nickname : '未知员工';
            if (!isset($stats['worker_stats'][$workerName])) {
                $stats['worker_stats'][$workerName] = [
                    'allocated' => 0,
                    'reported' => 0,
                    'hours' => 0.0,
                    'wage' => 0.0,
                    'completion_rate' => 0.0,
                    'efficiency' => 0.0,
                ];
            }
            $stats['worker_stats'][$workerName]['allocated'] += (int) $allocation->quantity;
            $stats['worker_stats'][$workerName]['reported'] += $allocationReported;
            $stats['worker_stats'][$workerName]['hours'] += $allocationHours;
            $stats['worker_stats'][$workerName]['wage'] += $allocationWage;
        }

        foreach ($stats['process_stats'] as &$process) {
            if ($process['allocated'] > 0) {
                $process['completion_rate'] = round(($process['reported'] / $process['allocated']) * 100, 1);
            }
        }
        unset($process);

        foreach ($stats['worker_stats'] as &$worker) {
            if ($worker['allocated'] > 0) {
                $worker['completion_rate'] = round(($worker['reported'] / $worker['allocated']) * 100, 1);
            }
            if ($worker['hours'] > 0) {
                $worker['efficiency'] = round($worker['reported'] / $worker['hours'], 2);
            }
        }
        unset($worker);

        return $stats;
    }
}
