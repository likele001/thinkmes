<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ProductionPlanModel;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\ProductModelModel;
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
            $params['order_id'] = $params['order_id'] ?? 0;
            $params['model_id'] = $params['model_id'] ?? 0;
            $params['create_time'] = time();
            $params['update_time'] = time();
            
            // 处理时间
            if (!empty($params['planned_start_time'])) {
                $params['planned_start_time'] = strtotime($params['planned_start_time']);
            }
            if (!empty($params['planned_end_time'])) {
                $params['planned_end_time'] = strtotime($params['planned_end_time']);
            }
            
            // 填充默认值，避免数据库 NOT NULL 约束导致失败
            $params['plan_name'] = $params['plan_name'] ?? '未命名计划';
            $params['order_id'] = $params['order_id'] ?? 0;
            $params['model_id'] = $params['model_id'] ?? 0;
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
        $id = (int) $this->request->get('id');
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
            
            // 处理时间
            if (!empty($params['planned_start_time'])) {
                $params['planned_start_time'] = strtotime($params['planned_start_time']);
            }
            if (!empty($params['planned_end_time'])) {
                $params['planned_end_time'] = strtotime($params['planned_end_time']);
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
        
        // 格式化时间
        $dataArray = $data->toArray();
        if ($dataArray['planned_start_time']) {
            $dataArray['planned_start_time'] = date('Y-m-d H:i', $dataArray['planned_start_time']);
        }
        if ($dataArray['planned_end_time']) {
            $dataArray['planned_end_time'] = date('Y-m-d H:i', $dataArray['planned_end_time']);
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
        View::assign('data', $dataArray);
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
}
