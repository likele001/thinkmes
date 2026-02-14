<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\ProcessModel;
use app\admin\model\mes\AllocationQrcodeModel;
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
                
                // 生成二维码
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
        
        // 获取员工列表（从用户表）
        $userList = UserModel::where('status', 'normal')
            ->column('nickname', 'id');
        
        View::assign('orderList', $orderList);
        View::assign('processList', $processList ?: []);
        View::assign('userList', $userList ?: []);
        View::assign('title', '添加分工分配');
        return $this->fetchWithLayout('mes/allocation/add');
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
            $allocation = AllocationModel::where('tenant_id', $tenantId)->find($id);
            if (!$allocation) {
                return $this->error('记录不存在');
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
        $data = AllocationModel::where('tenant_id', $tenantId)->find($id);
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
        
        // 获取员工列表
        $userList = UserModel::where('status', 'normal')
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
            $allocations = $this->request->post('allocations');
            
            if (!$orderId || !$allocations) {
                return $this->error('参数不完整');
            }
            
            // 处理JSON格式
            if (is_string($allocations)) {
                $allocations = json_decode($allocations, true);
            }
            
            if (!is_array($allocations)) {
                return $this->error('分配数据格式错误');
            }
            
            $tenantId = $this->getTenantId();
            $successCount = 0;
            
            Db::startTrans();
            try {
                foreach ($allocations as $item) {
                    if (empty($item['model_id']) || empty($item['process_id']) || empty($item['user_id']) || empty($item['quantity'])) {
                        continue;
                    }
                    
                    $allocation = AllocationModel::create([
                        'tenant_id' => $tenantId,
                        'order_id' => $orderId,
                        'model_id' => $item['model_id'],
                        'process_id' => $item['process_id'],
                        'user_id' => $item['user_id'],
                        'quantity' => $item['quantity'],
                        'allocation_code' => AllocationModel::generateAllocationCode(),
                        'status' => 0,
                        'create_time' => time(),
                        'update_time' => time(),
                    ]);
                    
                    // 生成二维码
                    $this->doGenerateQrcode($allocation->id, $tenantId);
                    $successCount++;
                }
                
                Db::commit();
                return $this->success("批量分配成功，共分配 {$successCount} 条任务");
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('批量分配失败');
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
        
        // 获取员工列表
        $userList = UserModel::where('status', 'normal')
            ->column('nickname', 'id');
        
        View::assign('orderList', $orderList);
        View::assign('processList', $processList ?: []);
        View::assign('userList', $userList ?: []);
        View::assign('title', '批量分工分配');
        return $this->fetchWithLayout('mes/allocation/batch');
    }
}
