<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\api\middleware\AdminAuth;
use app\common\lib\Auth;
use app\admin\model\AdminModel;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\OrderModelModel;
use app\admin\model\mes\OrderMaterialModel;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\ReportMediaModel;
use app\admin\model\mes\ProductModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\ProcessModel;
use app\admin\model\mes\ProcessPriceModel;
use app\admin\model\mes\CustomerModel;
use app\admin\model\mes\SupplierModel;
use app\admin\model\mes\MaterialModel;
use app\admin\model\mes\WarehouseModel;
use app\admin\model\mes\BomModel;
use app\admin\model\mes\BomItemModel;
use app\admin\model\mes\ProductionPlanModel;
use app\admin\model\mes\StockLogModel;
use app\admin\model\mes\PurchaseRequestModel;
use app\admin\model\mes\PurchaseInModel;
use app\admin\model\mes\AllocationQrcodeModel;
use app\admin\model\mes\ShipmentModel;
use app\admin\model\mes\ShipmentItemModel;
use app\admin\model\mes\QualityStandardModel;
use app\admin\model\mes\QualityCheckModel;
use app\admin\model\mes\WageModel;
use app\admin\model\mes\TraceCodeModel;
use app\admin\model\mes\AfterSalesModel;
use app\common\model\UserModel;
use think\facade\Db;
use think\Response;

/**
 * 后端管理小程序 API（参考 FastAdmin 报工系统 Scanwork）
 * 管理员登录、订单/分配/报工/审核、基础数据、上传等
 */
class Scanwork extends BaseController
{
    protected function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    protected function getAdminId(): int
    {
        return (int) ($this->request->adminId ?? 0);
    }

    /**
     * 管理员登录（小程序端）
     * POST username, password
     */
    public function adminLogin(): Response
    {
        $username = trim((string) $this->request->post('username', ''));
        $password = (string) $this->request->post('password', '');
        if ($username === '' || $password === '') {
            return $this->error('请输入用户名和密码');
        }

        $admin = AdminModel::where('username', $username)->find();
        if (!$admin) {
            return $this->error('用户名或密码错误');
        }
        if ($admin->status !== 1 && $admin->status !== '1') {
            return $this->error('该账号已被禁用');
        }
        if (!password_verify($password, $admin->password)) {
            return $this->error('用户名或密码错误');
        }

        $tenantId = (int) ($admin->tenant_id ?? 0);
        $token = AdminAuth::makeToken((int) $admin->id, $tenantId);
        $adminInfo = [
            'id'       => (int) $admin->id,
            'username' => $admin->username,
            'nickname' => $admin->nickname ?: $admin->username,
            'avatar'   => $admin->avatar ?? '',
            'tenant_id'=> $tenantId,
        ];

        return $this->success('登录成功', [
            'token'      => $token,
            'admin_info' => $adminInfo,
        ]);
    }

    /**
     * 校验 token
     */
    public function checkToken(): Response
    {
        return $this->success('token有效', [
            'admin_id'  => $this->getAdminId(),
            'tenant_id' => $this->getTenantId(),
        ]);
    }

    /**
     * 获取当前管理员在小程序端可用的权限节点（与 PC 角色/菜单一致，用于前端显隐菜单）
     * 返回 rule names 列表，前端可根据节点显隐对应 Tab/页面
     */
    public function getScanworkMenu(): Response
    {
        $adminId = $this->getAdminId();
        $auth = new Auth();
        $rules = $auth->getRuleIds($adminId);
        return $this->success('获取成功', [
            'nodes' => is_array($rules) ? $rules : [],
        ]);
    }

    // ---------- 订单 ----------
    public function getOrders(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 10)));
        $status = $this->request->get('status', '');

        $query = OrderModel::with(['orderModels.model.product', 'customer'])
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('获取成功', ['total' => $total, 'list' => $list]);
    }

    public function getOrderDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $orderId = (int) $this->request->get('order_id', 0);
        if ($orderId <= 0) {
            return $this->error('参数错误');
        }
        $order = OrderModel::with(['orderModels.model.product', 'customer'])
            ->where('tenant_id', $tenantId)
            ->find($orderId);
        if (!$order) {
            return $this->error('订单不存在');
        }
        return $this->success('获取成功', $order->toArray());
    }

    public function getOrderModels(): Response
    {
        $tenantId = $this->getTenantId();
        $orderId = (int) $this->request->get('order_id', 0);
        if ($orderId <= 0) {
            return $this->error('参数错误');
        }
        $rows = OrderModelModel::with(['model.product'])
            ->where('tenant_id', $tenantId)
            ->where('order_id', $orderId)
            ->select();
        return $this->success('获取成功', ['list' => $rows->toArray()]);
    }

    /** 订单物料清单 */
    public function getOrderMaterialList(): Response
    {
        $tenantId = $this->getTenantId();
        $orderId = (int) $this->request->get('order_id', 0);
        if ($orderId <= 0) {
            return $this->error('参数错误');
        }
        $rows = OrderMaterialModel::with(['material', 'order'])
            ->where('tenant_id', $tenantId)
            ->where('order_id', $orderId)
            ->select();
        return $this->success('获取成功', ['list' => $rows->toArray()]);
    }

    /** 创建订单 POST row(customer_id,customer_name,order_name,delivery_time,remark), models([{model_id,quantity}]) */
    public function createOrder(): Response
    {
        $tenantId = $this->getTenantId();
        $params = $this->request->post('row/a') ?: [];
        $modelData = $this->request->post('models/a') ?: [];
        if (is_string($this->request->post('models'))) {
            $decoded = json_decode($this->request->post('models'), true);
            if (is_array($decoded)) {
                $modelData = $decoded;
            }
        }
        if (empty($params) || empty($modelData)) {
            return $this->error('参数不完整');
        }
        $params['tenant_id'] = $tenantId;
        $params['order_no'] = OrderModel::generateOrderNo();
        if (!empty($params['customer_id'])) {
            $customer = CustomerModel::where('tenant_id', $tenantId)->where('id', $params['customer_id'])->find();
            if ($customer) {
                $params['customer_name'] = $customer->customer_name ?? '';
                $params['customer_phone'] = $customer->contact_phone ?? '';
            }
        }
        $params['customer_name'] = $params['customer_name'] ?? '';
        $params['customer_phone'] = $params['customer_phone'] ?? '';
        $params['order_name'] = $params['order_name'] ?? '未命名订单';
        if (!empty($params['delivery_time'])) {
            $params['delivery_time'] = is_numeric($params['delivery_time']) ? (int) $params['delivery_time'] : strtotime($params['delivery_time']);
        }
        Db::startTrans();
        try {
            $order = OrderModel::create($params);
            $totalQuantity = 0;
            foreach ($modelData as $item) {
                $mid = (int) ($item['model_id'] ?? 0);
                $qty = (int) ($item['quantity'] ?? 0);
                if ($mid > 0) {
                    if ($qty <= 0) {
                        $qty = 1;
                    }
                    OrderModelModel::create(['tenant_id' => $tenantId, 'order_id' => $order->id, 'model_id' => $mid, 'quantity' => $qty]);
                    $totalQuantity += $qty;
                }
            }
            if ($totalQuantity === 0) {
                throw new \Exception('至少需要一个型号及数量');
            }
            $order->save(['total_quantity' => $totalQuantity]);
            Db::commit();
            return $this->success('添加成功', ['id' => $order->id]);
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('添加失败：' . $e->getMessage());
        }
    }

    /** 更新订单 POST id, row, models */
    public function updateOrder(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        $modelData = $this->request->post('models/a') ?: [];
        if (is_string($this->request->post('models'))) {
            $decoded = json_decode($this->request->post('models'), true);
            if (is_array($decoded)) {
                $modelData = $decoded;
            }
        }
        if ($id <= 0 || empty($params) || empty($modelData)) {
            return $this->error('参数不完整');
        }
        $order = OrderModel::where('tenant_id', $tenantId)->find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }
        if (!empty($params['customer_id'])) {
            $customer = CustomerModel::where('tenant_id', $tenantId)->where('id', $params['customer_id'])->find();
            if ($customer) {
                $params['customer_name'] = $customer->customer_name ?? '';
                $params['customer_phone'] = $customer->contact_phone ?? '';
            }
        }
        if (!empty($params['delivery_time'])) {
            $params['delivery_time'] = is_numeric($params['delivery_time']) ? (int) $params['delivery_time'] : strtotime($params['delivery_time']);
        }
        Db::startTrans();
        try {
            $order->save($params);
            OrderModelModel::where('tenant_id', $tenantId)->where('order_id', $id)->delete();
            $totalQuantity = 0;
            foreach ($modelData as $item) {
                $mid = (int) ($item['model_id'] ?? 0);
                $qty = (int) ($item['quantity'] ?? 0);
                if ($mid > 0) {
                    if ($qty <= 0) {
                        $qty = 1;
                    }
                    OrderModelModel::create(['tenant_id' => $tenantId, 'order_id' => $id, 'model_id' => $mid, 'quantity' => $qty]);
                    $totalQuantity += $qty;
                }
            }
            $order->save(['total_quantity' => $totalQuantity]);
            Db::commit();
            return $this->success('编辑成功');
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('编辑失败：' . $e->getMessage());
        }
    }

    /** 删除订单 POST ids */
    public function deleteOrder(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $order = OrderModel::where('tenant_id', $tenantId)->find($id);
            if ($order) {
                OrderModelModel::where('order_id', $id)->delete();
                $order->delete();
            }
        }
        return $this->success('删除成功');
    }

    // ---------- 客户 ----------
    public function getCustomerList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $name = trim((string) $this->request->get('customer_name', ''));
        $status = $this->request->get('status', '');
        $query = CustomerModel::where('tenant_id', $tenantId)->order('id', 'desc');
        if ($name !== '') {
            $query->where('customer_name', 'like', '%' . $name . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getCustomerDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = CustomerModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('客户不存在');
        }
        return $this->success('', $row->toArray());
    }

    public function createCustomer(): Response
    {
        $tenantId = $this->getTenantId();
        $params = $this->request->post('row/a') ?: [];
        if (empty($params)) {
            return $this->error('参数不能为空');
        }
        $params['tenant_id'] = $tenantId;
        $customer = CustomerModel::create($params);
        return $this->success('添加成功', ['id' => $customer->id]);
    }

    public function updateCustomer(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $row = CustomerModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('客户不存在');
        }
        $row->save($params);
        return $this->success('编辑成功');
    }

    public function deleteCustomer(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $row = CustomerModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
            }
        }
        return $this->success('删除成功');
    }

    // ---------- 供应商 ----------
    public function getSupplierList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $name = trim((string) $this->request->get('supplier_name', ''));
        $status = $this->request->get('status', '');
        $query = SupplierModel::where('tenant_id', $tenantId)->order('id', 'desc');
        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getSupplierDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = SupplierModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('供应商不存在');
        }
        return $this->success('', $row->toArray());
    }

    public function createSupplier(): Response
    {
        $tenantId = $this->getTenantId();
        $params = $this->request->post('row/a') ?: [];
        if (empty($params)) {
            return $this->error('参数不能为空');
        }
        $params['tenant_id'] = $tenantId;
        $row = SupplierModel::create($params);
        return $this->success('添加成功', ['id' => $row->id]);
    }

    public function updateSupplier(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $row = SupplierModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('供应商不存在');
        }
        $row->save($params);
        return $this->success('编辑成功');
    }

    public function deleteSupplier(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $row = SupplierModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
            }
        }
        return $this->success('删除成功');
    }

    // ---------- 工序 ----------
    public function createProcess(): Response
    {
        $tenantId = $this->getTenantId();
        $params = $this->request->post('row/a') ?: [];
        if (empty($params)) {
            return $this->error('参数不能为空');
        }
        $params['tenant_id'] = $tenantId;
        $process = ProcessModel::create($params);
        return $this->success('添加成功', ['id' => $process->id]);
    }

    public function updateProcess(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $row = ProcessModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('工序不存在');
        }
        $row->save($params);
        return $this->success('编辑成功');
    }

    public function deleteProcess(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $row = ProcessModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
            }
        }
        return $this->success('删除成功');
    }

    // ---------- 产品（增删改，列表已有 getProducts）----------
    public function createProduct(): Response
    {
        $tenantId = $this->getTenantId();
        $params = $this->request->post('row/a') ?: [];
        $models = $this->request->post('models/a') ?: [];
        $prices = $this->request->post('prices/a') ?: [];
        if (empty($params)) {
            return $this->error('参数不能为空');
        }
        $params['tenant_id'] = $tenantId;
        Db::startTrans();
        try {
            $product = ProductModel::create($params);
            foreach ($models as $m) {
                $name = trim((string) ($m['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                ProductModelModel::create([
                    'tenant_id' => $tenantId,
                    'product_id' => $product->id,
                    'name' => $name,
                    'model_code' => $m['model_code'] ?? '',
                    'color' => $m['color'] ?? '',
                    'specification' => $m['specification'] ?? '',
                    'remark' => $m['remark'] ?? '',
                    'description' => $m['description'] ?? '',
                    'status' => isset($m['status']) ? (int) $m['status'] : 1,
                ]);
            }
            $modelIds = ProductModelModel::where('product_id', $product->id)->column('id');
            foreach ($prices as $p) {
                $modelId = (int) ($p['model_id'] ?? 0);
                $processId = (int) ($p['process_id'] ?? 0);
                $price = (float) ($p['price'] ?? 0);
                if ($modelId > 0 && $processId > 0 && in_array($modelId, $modelIds, true)) {
                    ProcessPriceModel::create([
                        'tenant_id' => $tenantId,
                        'model_id' => $modelId,
                        'process_id' => $processId,
                        'price' => $price,
                    ]);
                }
            }
            Db::commit();
            return $this->success('添加成功', ['id' => $product->id]);
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('添加失败：' . $e->getMessage());
        }
    }

    public function updateProduct(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        $models = $this->request->post('models/a') ?: [];
        $prices = $this->request->post('prices/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $product = ProductModel::where('tenant_id', $tenantId)->find($id);
        if (!$product) {
            return $this->error('产品不存在');
        }
        Db::startTrans();
        try {
            $product->save($params);
            if (is_array($models)) {
                $oldModelIds = ProductModelModel::where('product_id', $id)->column('id');
                if (!empty($oldModelIds)) {
                    ProcessPriceModel::whereIn('model_id', $oldModelIds)->delete();
                }
                ProductModelModel::where('product_id', $id)->delete();
                foreach ($models as $m) {
                    $name = trim((string) ($m['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    ProductModelModel::create([
                        'tenant_id' => $tenantId,
                        'product_id' => $id,
                        'name' => $name,
                        'model_code' => $m['model_code'] ?? '',
                        'color' => $m['color'] ?? '',
                        'specification' => $m['specification'] ?? '',
                        'remark' => $m['remark'] ?? '',
                        'description' => $m['description'] ?? '',
                        'status' => isset($m['status']) ? (int) $m['status'] : 1,
                    ]);
                }
            }
            if (is_array($prices)) {
                $modelIds = ProductModelModel::where('product_id', $id)->column('id');
                if (!empty($modelIds)) {
                    ProcessPriceModel::where('tenant_id', $tenantId)->whereIn('model_id', $modelIds)->delete();
                }
                foreach ($prices as $p) {
                    $modelId = (int) ($p['model_id'] ?? 0);
                    $processId = (int) ($p['process_id'] ?? 0);
                    $price = (float) ($p['price'] ?? 0);
                    if ($modelId > 0 && $processId > 0 && in_array($modelId, $modelIds, true)) {
                        ProcessPriceModel::create([
                            'tenant_id' => $tenantId,
                            'model_id' => $modelId,
                            'process_id' => $processId,
                            'price' => $price,
                        ]);
                    }
                }
            }
            Db::commit();
            return $this->success('编辑成功');
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('编辑失败：' . $e->getMessage());
        }
    }

    public function deleteProduct(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $product = ProductModel::where('tenant_id', $tenantId)->find($id);
            if ($product) {
                $modelIds = ProductModelModel::where('product_id', $id)->column('id');
                ProcessPriceModel::whereIn('model_id', $modelIds)->delete();
                ProductModelModel::where('product_id', $id)->delete();
                $product->delete();
            }
        }
        return $this->success('删除成功');
    }

    // ---------- 产品型号 ----------
    public function createProductModel(): Response
    {
        $tenantId = $this->getTenantId();
        $params = $this->request->post('row/a') ?: [];
        if (empty($params) || empty($params['product_id'])) {
            return $this->error('参数错误');
        }
        $params['tenant_id'] = $tenantId;
        $row = ProductModelModel::create($params);
        return $this->success('添加成功', ['id' => $row->id]);
    }

    public function updateProductModel(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $row = ProductModelModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('型号不存在');
        }
        $row->save($params);
        return $this->success('编辑成功');
    }

    public function deleteProductModel(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $row = ProductModelModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                ProcessPriceModel::where('model_id', $id)->delete();
                $row->delete();
            }
        }
        return $this->success('删除成功');
    }

    /**
     * 批量添加型号及工序工价（型号重复则跳过）
     * POST product_id, models: [ { name, model_code?, color?, specification?, remark?, description?, prices: [...] } ]
     */
    public function batchAddProductModels(): Response
    {
        $tenantId = $this->getTenantId();
        $productId = (int) $this->request->post('product_id', 0);
        $models = $this->request->post('models/a') ?: [];
        if ($productId <= 0 || empty($models)) {
            return $this->error('请选择产品并至少添加一条型号');
        }
        $product = ProductModel::where('tenant_id', $tenantId)->find($productId);
        if (!$product) {
            return $this->error('产品不存在');
        }
        $existingNames = ProductModelModel::where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->column('name');
        $existingNames = array_map('trim', array_map('strval', $existingNames));
        $existingNames = array_filter($existingNames);
        $existingNames = array_unique($existingNames);

        $added = 0;
        $skipped = 0;
        Db::startTrans();
        try {
            foreach ($models as $m) {
                $name = trim((string) ($m['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                if (in_array($name, $existingNames, true)) {
                    $skipped++;
                    continue;
                }
                $modelRow = ProductModelModel::create([
                    'tenant_id' => $tenantId,
                    'product_id' => $productId,
                    'name' => $name,
                    'model_code' => $m['model_code'] ?? '',
                    'color' => $m['color'] ?? '',
                    'specification' => $m['specification'] ?? '',
                    'remark' => $m['remark'] ?? '',
                    'description' => $m['description'] ?? '',
                    'status' => 1
                ]);
                $existingNames[] = $name;
                $added++;

                $prices = $m['prices'] ?? [];
                if (is_array($prices)) {
                    foreach ($prices as $p) {
                        $processId = (int) ($p['process_id'] ?? 0);
                        $price = (float) ($p['price'] ?? 0);
                        $timePrice = (float) ($p['time_price'] ?? 0);
                        if ($processId > 0 && ($price > 0 || $timePrice > 0)) {
                            ProcessPriceModel::create([
                                'tenant_id' => $tenantId,
                                'model_id' => $modelRow->id,
                                'process_id' => $processId,
                                'price' => $price,
                                'time_price' => $timePrice,
                                'status' => 1
                            ]);
                        }
                    }
                }
            }
            Db::commit();
            $msg = '批量添加完成：成功 ' . $added . ' 个型号';
            if ($skipped > 0) {
                $msg .= '，跳过重复 ' . $skipped . ' 个';
            }
            return $this->success($msg, ['added' => $added, 'skipped' => $skipped]);
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('批量添加失败：' . $e->getMessage());
        }
    }

    // ---------- 工序工价 ----------
    public function createProcessPrice(): Response
    {
        $tenantId = $this->getTenantId();
        $params = $this->request->post('row/a') ?: [];
        if (empty($params) || empty($params['model_id']) || empty($params['process_id'])) {
            return $this->error('参数不完整');
        }
        $params['tenant_id'] = $tenantId;
        $exists = ProcessPriceModel::where('tenant_id', $tenantId)
            ->where('model_id', $params['model_id'])
            ->where('process_id', $params['process_id'])
            ->find();
        if ($exists) {
            return $this->error('该型号和工序的工价已存在');
        }
        $row = ProcessPriceModel::create($params);
        return $this->success('添加成功', ['id' => $row->id]);
    }

    public function updateProcessPrice(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $row = ProcessPriceModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('工价记录不存在');
        }
        $row->save($params);
        return $this->success('编辑成功');
    }

    public function deleteProcessPrice(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $row = ProcessPriceModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
            }
        }
        return $this->success('删除成功');
    }

    /** 批量设置工序工价 POST list: [{model_id, process_id, price}] */
    public function batchProcessPrice(): Response
    {
        $tenantId = $this->getTenantId();
        $list = $this->request->post('list/a') ?: [];
        if (empty($list)) {
            return $this->error('参数不能为空');
        }
        $count = 0;
        foreach ($list as $item) {
            $modelId = (int) ($item['model_id'] ?? 0);
            $processId = (int) ($item['process_id'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            if ($modelId <= 0 || $processId <= 0) {
                continue;
            }
            $exists = ProcessPriceModel::where('tenant_id', $tenantId)
                ->where('model_id', $modelId)
                ->where('process_id', $processId)
                ->find();
            if ($exists) {
                $exists->save(['price' => $price]);
            } else {
                ProcessPriceModel::create([
                    'tenant_id' => $tenantId,
                    'model_id' => $modelId,
                    'process_id' => $processId,
                    'price' => $price,
                ]);
            }
            $count++;
        }
        return $this->success('操作成功', ['count' => $count]);
    }

    // ---------- 物料 ----------
    public function getMaterialList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $name = trim((string) $this->request->get('name', ''));
        $status = $this->request->get('status', '');
        $query = MaterialModel::where('tenant_id', $tenantId)->order('id', 'desc');
        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getMaterialDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = MaterialModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('物料不存在');
        }
        return $this->success('', $row->toArray());
    }

    public function createMaterial(): Response
    {
        $tenantId = $this->getTenantId();
        $params = $this->request->post('row/a') ?: [];
        if (empty($params)) {
            return $this->error('参数不能为空');
        }
        $params['tenant_id'] = $tenantId;
        $params['name'] = $params['name'] ?? '未命名物料';
        $params['code'] = $params['code'] ?? ('M' . date('YmdHis') . rand(100, 999));
        $params['unit'] = $params['unit'] ?? 'pcs';
        $row = MaterialModel::create($params);
        return $this->success('添加成功', ['id' => $row->id]);
    }

    public function updateMaterial(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $row = MaterialModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('物料不存在');
        }
        $row->save($params);
        return $this->success('编辑成功');
    }

    public function deleteMaterial(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $row = MaterialModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
            }
        }
        return $this->success('删除成功');
    }

    // ---------- 仓库 ----------
    public function getWarehouseList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $status = $this->request->get('status', '');
        $query = WarehouseModel::where('tenant_id', $tenantId)->order('is_default', 'desc')->order('id', 'desc');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getWarehouseDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = WarehouseModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('仓库不存在');
        }
        return $this->success('', $row->toArray());
    }

    public function createWarehouse(): Response
    {
        $tenantId = $this->getTenantId();
        $params = $this->request->post('row/a') ?: [];
        if (empty($params)) {
            return $this->error('参数不能为空');
        }
        $params['tenant_id'] = $tenantId;
        $row = WarehouseModel::create($params);
        if (!empty($params['is_default']) && (int) $params['is_default'] === 1) {
            WarehouseModel::where('tenant_id', $tenantId)->where('id', '<>', $row->id)->save(['is_default' => 0]);
        }
        return $this->success('添加成功', ['id' => $row->id]);
    }

    public function updateWarehouse(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $row = WarehouseModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('仓库不存在');
        }
        $row->save($params);
        if (!empty($params['is_default']) && (int) $params['is_default'] === 1) {
            WarehouseModel::where('tenant_id', $tenantId)->where('id', '<>', $id)->save(['is_default' => 0]);
        }
        return $this->success('编辑成功');
    }

    public function deleteWarehouse(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $row = WarehouseModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
            }
        }
        return $this->success('删除成功');
    }

    // ---------- 库存 ----------
    public function getStockList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $name = trim((string) $this->request->get('name', ''));
        $query = MaterialModel::where('tenant_id', $tenantId)->order('id', 'desc');
        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['is_warning'] = (float) ($item['stock'] ?? 0) < (float) ($item['min_stock'] ?? 0);
        }
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getStockLog(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $materialId = $this->request->get('material_id', '');
        $businessType = $this->request->get('business_type', '');
        $query = StockLogModel::with(['material'])->where('tenant_id', $tenantId)->order('id', 'desc');
        if ($materialId !== '' && $materialId !== null) {
            $query->where('material_id', (int) $materialId);
        }
        if ($businessType !== '') {
            $query->where('business_type', $businessType);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /** 入库 POST material_id, quantity, remark */
    public function stockIn(): Response
    {
        $tenantId = $this->getTenantId();
        $adminId = $this->getAdminId();
        $materialId = (int) $this->request->post('material_id', 0);
        $quantity = (float) $this->request->post('quantity', 0);
        $remark = trim((string) $this->request->post('remark', ''));
        if ($materialId <= 0 || $quantity <= 0) {
            return $this->error('参数错误');
        }
        $material = MaterialModel::where('tenant_id', $tenantId)->find($materialId);
        if (!$material) {
            return $this->error('物料不存在');
        }
        Db::startTrans();
        try {
            $beforeQty = (float) $material->stock;
            $afterQty = $beforeQty + $quantity;
            $material->stock = $afterQty;
            $material->save();
            StockLogModel::log($tenantId, $materialId, $quantity, 'adjust_in', 0, $adminId, $remark ?: '调整入库', $beforeQty, $afterQty);
            Db::commit();
            return $this->success('入库成功');
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('入库失败：' . $e->getMessage());
        }
    }

    /** 出库 POST material_id, quantity, remark */
    public function stockOut(): Response
    {
        $tenantId = $this->getTenantId();
        $adminId = $this->getAdminId();
        $materialId = (int) $this->request->post('material_id', 0);
        $quantity = (float) $this->request->post('quantity', 0);
        $remark = trim((string) $this->request->post('remark', ''));
        if ($materialId <= 0 || $quantity <= 0) {
            return $this->error('参数错误');
        }
        $material = MaterialModel::where('tenant_id', $tenantId)->find($materialId);
        if (!$material) {
            return $this->error('物料不存在');
        }
        $stock = (float) $material->stock;
        if ($stock < $quantity) {
            return $this->error('库存不足，当前：' . $stock);
        }
        Db::startTrans();
        try {
            $afterQty = $stock - $quantity;
            $material->stock = $afterQty;
            $material->save();
            StockLogModel::log($tenantId, $materialId, -$quantity, 'adjust_out', 0, $adminId, $remark ?: '调整出库', $stock, $afterQty);
            Db::commit();
            return $this->success('出库成功');
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('出库失败：' . $e->getMessage());
        }
    }

    /** 盘点 POST material_id, actual_quantity, remark */
    public function stockCheck(): Response
    {
        $tenantId = $this->getTenantId();
        $adminId = $this->getAdminId();
        $materialId = (int) $this->request->post('material_id', 0);
        $actualQuantity = (float) $this->request->post('actual_quantity', 0);
        $remark = trim((string) $this->request->post('remark', ''));
        if ($materialId <= 0) {
            return $this->error('参数错误');
        }
        $material = MaterialModel::where('tenant_id', $tenantId)->find($materialId);
        if (!$material) {
            return $this->error('物料不存在');
        }
        $before = (float) $material->stock;
        $diff = $actualQuantity - $before;
        $businessType = $diff >= 0 ? 'check_in' : 'check_out';
        Db::startTrans();
        try {
            $material->stock = $actualQuantity;
            $material->save();
            StockLogModel::log($tenantId, $materialId, $diff, $businessType, 0, $adminId, $remark ?: '库存盘点', $before, $actualQuantity);
            Db::commit();
            return $this->success('盘点成功', ['diff' => $diff]);
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('盘点失败：' . $e->getMessage());
        }
    }

    // ---------- BOM ----------
    public function getBomList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $bomNo = trim((string) $this->request->get('bom_no', ''));
        $status = $this->request->get('status', '');
        $query = BomModel::with(['product', 'model'])->where('tenant_id', $tenantId)->order('id', 'desc');
        if ($bomNo !== '') {
            $query->where('bom_no', 'like', '%' . $bomNo . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getBomDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = BomModel::with(['product', 'model'])->where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('BOM不存在');
        }
        return $this->success('', $row->toArray());
    }

    public function getBomItems(): Response
    {
        $tenantId = $this->getTenantId();
        $bomId = (int) $this->request->get('bom_id', 0);
        if ($bomId <= 0) {
            return $this->error('参数错误');
        }
        $list = BomItemModel::with(['material', 'supplier'])
            ->where('tenant_id', $tenantId)
            ->where('bom_id', $bomId)
            ->order('level', 'asc')
            ->order('sequence', 'asc')
            ->select()
            ->toArray();
        return $this->success('', ['list' => $list]);
    }

    public function createBom(): Response
    {
        $tenantId = $this->getTenantId();
        $adminId = $this->getAdminId();
        $params = $this->request->post('row/a') ?: [];
        if (empty($params)) {
            return $this->error('参数不能为空');
        }
        $params['tenant_id'] = $tenantId;
        $params['creator_id'] = $adminId;
        $params['creator_name'] = $this->request->adminName ?? '';
        $params['bom_no'] = $params['bom_no'] ?? BomModel::generateBomNo();
        $params['bom_name'] = $params['bom_name'] ?? '未命名BOM';
        $row = BomModel::create($params);
        return $this->success('添加成功', ['id' => $row->id]);
    }

    public function updateBom(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $row = BomModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('BOM不存在');
        }
        $row->save($params);
        return $this->success('编辑成功');
    }

    public function deleteBom(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        Db::startTrans();
        try {
            foreach ($arr as $id) {
                $bom = BomModel::where('tenant_id', $tenantId)->find($id);
                if ($bom) {
                    BomItemModel::where('tenant_id', $tenantId)->where('bom_id', $id)->delete();
                    $bom->delete();
                }
            }
            Db::commit();
            return $this->success('删除成功');
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('删除失败');
        }
    }

    public function addBomItem(): Response
    {
        $tenantId = $this->getTenantId();
        $params = $this->request->post();
        if (empty($params)) {
            return $this->error('参数不能为空');
        }
        $params['tenant_id'] = $tenantId;
        $row = BomItemModel::create($params);
        return $this->success('添加成功', ['id' => $row->id]);
    }

    public function updateBomItem(): Response
    {
        $tenantId = $this->getTenantId();
        $params = $this->request->post();
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = BomItemModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('BOM明细不存在');
        }
        unset($params['id']);
        $row->save($params);
        return $this->success('更新成功');
    }

    public function deleteBomItem(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = BomItemModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('BOM明细不存在');
        }
        $row->delete();
        return $this->success('删除成功');
    }

    public function approveBom(): Response
    {
        $tenantId = $this->getTenantId();
        $adminId = $this->getAdminId();
        $ids = $this->request->post('ids');
        $approve = (int) $this->request->post('approve', 1);
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $id = is_array($ids) ? (int) ($ids[0] ?? 0) : (int) $ids;
        $bom = BomModel::where('tenant_id', $tenantId)->find($id);
        if (!$bom) {
            return $this->error('BOM不存在');
        }
        if ($approve === 1) {
            $bom->status = 2;
            $bom->approver_id = $adminId;
            $bom->approver_name = $this->request->adminName ?? '';
            $bom->approve_time = time();
            $bom->publish_time = time();
        } else {
            $bom->status = 0;
        }
        $bom->save();
        return $this->success($approve === 1 ? '审核通过' : '已退回');
    }

    // ---------- 生产计划 ----------
    public function getProductionPlanList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $status = $this->request->get('status', '');
        $query = ProductionPlanModel::with(['order', 'model.product'])->where('tenant_id', $tenantId)->order('id', 'desc');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['progress'] = $row['total_quantity'] > 0
                ? round((float) $row['completed_quantity'] / (float) $row['total_quantity'] * 100, 2)
                : 0;
        }
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getProductionPlanDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = ProductionPlanModel::with(['order', 'model.product'])->where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('生产计划不存在');
        }
        $arr = $row->toArray();
        $arr['progress'] = $row->total_quantity > 0
            ? round((float) $row->completed_quantity / (float) $row->total_quantity * 100, 2)
            : 0;
        return $this->success('', $arr);
    }

    public function getProductionPlanAllocations(): Response
    {
        $tenantId = $this->getTenantId();
        $planId = (int) $this->request->get('plan_id', 0);
        if ($planId <= 0) {
            return $this->error('参数错误');
        }
        $plan = ProductionPlanModel::where('tenant_id', $tenantId)->find($planId);
        if (!$plan) {
            return $this->error('生产计划不存在');
        }
        $list = AllocationModel::with(['process', 'user', 'model.product', 'order'])
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
            ->select()
            ->toArray();
        return $this->success('', ['list' => $list]);
    }

    public function getProductionPlanProgress(): Response
    {
        $tenantId = $this->getTenantId();
        $planId = (int) $this->request->get('plan_id', 0);
        if ($planId <= 0) {
            return $this->error('参数错误');
        }
        $plan = ProductionPlanModel::with(['order', 'model.product'])->where('tenant_id', $tenantId)->find($planId);
        if (!$plan) {
            return $this->error('生产计划不存在');
        }
        $allocationIds = AllocationModel::where('tenant_id', $tenantId)->where('plan_id', $planId)->column('id');
        $reportSum = empty($allocationIds)
            ? 0
            : (int) ReportModel::whereIn('allocation_id', $allocationIds)->where('status', 1)->sum('quantity');
        $totalQty = (int) $plan->total_quantity;
        $completed = (int) $plan->completed_quantity;
        return $this->success('', [
            'plan' => $plan->toArray(),
            'completed_quantity' => $completed,
            'reported_quantity' => (int) $reportSum,
            'progress' => $totalQty > 0 ? round($completed / $totalQty * 100, 2) : 0,
        ]);
    }

    public function getProductionPlanProgressStats(): Response
    {
        $tenantId = $this->getTenantId();
        $planId = (int) $this->request->get('plan_id', 0);
        if ($planId <= 0) {
            return $this->error('参数错误');
        }
        $plan = ProductionPlanModel::where('tenant_id', $tenantId)->find($planId);
        if (!$plan) {
            return $this->error('生产计划不存在');
        }
        $totalQty = (int) $plan->total_quantity;
        $completed = (int) $plan->completed_quantity;
        return $this->success('', [
            'total_quantity' => $totalQty,
            'completed_quantity' => $completed,
            'progress' => $totalQty > 0 ? round($completed / $totalQty * 100, 2) : 0,
        ]);
    }

    public function createProductionPlan(): Response
    {
        $tenantId = $this->getTenantId();
        $params = $this->request->post('row/a') ?: [];
        if (empty($params)) {
            return $this->error('参数不能为空');
        }
        $params['tenant_id'] = $tenantId;
        $params['plan_code'] = $params['plan_code'] ?? ProductionPlanModel::generatePlanCode();
        $params['plan_name'] = $params['plan_name'] ?? '未命名计划';
        $params['order_id'] = (int) ($params['order_id'] ?? 0);
        $params['model_id'] = (int) ($params['model_id'] ?? 0);
        $params['total_quantity'] = (int) ($params['total_quantity'] ?? 0);
        $params['create_time'] = time();
        $params['update_time'] = time();
        if ($params['order_id'] <= 0 || $params['model_id'] <= 0) {
            return $this->error('请选择订单和产品型号');
        }
        if ($params['total_quantity'] <= 0) {
            return $this->error('计划数量必须大于0');
        }
        if (!empty($params['planned_start_time'])) {
            $params['planned_start_time'] = is_numeric($params['planned_start_time'])
                ? (int) $params['planned_start_time']
                : strtotime($params['planned_start_time']);
        }
        if (!empty($params['planned_end_time'])) {
            $params['planned_end_time'] = is_numeric($params['planned_end_time'])
                ? (int) $params['planned_end_time']
                : strtotime($params['planned_end_time']);
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
        if ($plannedSum + $params['total_quantity'] > (int) $orderModel['quantity']) {
            return $this->error('计划数量超过订单该型号数量');
        }
        $plan = ProductionPlanModel::create($params);
        return $this->success('添加成功', ['id' => $plan->id]);
    }

    public function updateProductionPlan(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $plan = ProductionPlanModel::where('tenant_id', $tenantId)->find($id);
        if (!$plan) {
            return $this->error('生产计划不存在');
        }
        if (!empty($params['planned_start_time'])) {
            $params['planned_start_time'] = is_numeric($params['planned_start_time'])
                ? (int) $params['planned_start_time']
                : strtotime($params['planned_start_time']);
        }
        if (!empty($params['planned_end_time'])) {
            $params['planned_end_time'] = is_numeric($params['planned_end_time'])
                ? (int) $params['planned_end_time']
                : strtotime($params['planned_end_time']);
        }
        $plan->save($params);
        return $this->success('编辑成功');
    }

    public function deleteProductionPlan(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $plan = ProductionPlanModel::where('tenant_id', $tenantId)->find($id);
            if ($plan) {
                $plan->delete();
            }
        }
        return $this->success('删除成功');
    }

    // ---------- 分工分配 ----------
    public function getAllocations(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 10)));
        $userId = $this->request->get('user_id', '');
        $status = $this->request->get('status', '');
        $orderId = $this->request->get('order_id', '');

        $query = AllocationModel::with(['order', 'model.product', 'process', 'user'])
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc');
        if ($userId !== '' && $userId !== null) {
            $query->where('user_id', (int) $userId);
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        if ($orderId !== '' && $orderId !== null && (int) $orderId > 0) {
            $query->where('order_id', (int) $orderId);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('获取成功', ['total' => $total, 'list' => $list]);
    }

    public function getAllocationDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $allocationId = (int) $this->request->get('allocation_id', 0);
        if ($allocationId <= 0) {
            return $this->error('参数错误');
        }
        $allocation = AllocationModel::with(['order', 'model.product', 'process', 'user'])
            ->where('tenant_id', $tenantId)
            ->find($allocationId);
        if (!$allocation) {
            return $this->error('分工分配不存在');
        }
        $arr = $allocation->toArray();
        $arr['reported_quantity'] = (int) ReportModel::where('allocation_id', $allocationId)->sum('quantity');
        $arr['pending_quantity'] = max(0, (int) $allocation->quantity - $arr['reported_quantity']);
        return $this->success('获取成功', $arr);
    }

    public function createAllocation(): Response
    {
        $tenantId = $this->getTenantId();
        $orderId = (int) $this->request->post('order_id', 0);
        $modelId = (int) $this->request->post('model_id', 0);
        $processId = (int) $this->request->post('process_id', 0);
        $userId = (int) $this->request->post('user_id', 0);
        $quantity = (int) $this->request->post('quantity', 0);
        if ($orderId <= 0 || $modelId <= 0 || $processId <= 0 || $userId <= 0 || $quantity <= 0) {
            return $this->error('参数不完整');
        }
        $order = OrderModel::where('tenant_id', $tenantId)->find($orderId);
        if (!$order) {
            return $this->error('订单不存在');
        }
        $data = [
            'tenant_id'  => $tenantId,
            'order_id'   => $orderId,
            'model_id'   => $modelId,
            'process_id' => $processId,
            'user_id'    => $userId,
            'quantity'   => $quantity,
            'allocation_code' => AllocationModel::generateAllocationCode(),
            'create_time' => time(),
            'update_time' => time(),
        ];
        $allocation = AllocationModel::create($data);
        return $this->success('创建成功', ['id' => $allocation->id]);
    }

    public function updateAllocation(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $allocation = AllocationModel::where('tenant_id', $tenantId)->find($id);
        if (!$allocation) {
            return $this->error('分工分配不存在');
        }
        $params['update_time'] = time();
        $allocation->save($params);
        return $this->success('保存成功');
    }

    public function deleteAllocation(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        $reportCount = ReportModel::where('tenant_id', $tenantId)->whereIn('allocation_id', $arr)->count();
        if ($reportCount > 0) {
            return $this->error('存在关联报工记录，无法删除');
        }
        AllocationQrcodeModel::where('tenant_id', $tenantId)->whereIn('allocation_id', $arr)->delete();
        AllocationModel::where('tenant_id', $tenantId)->whereIn('id', $arr)->delete();
        return $this->success('删除成功');
    }

    /** 批量创建分配 POST order_id, plan_id(可选), allocations: [{model_id, process_id, user_id, quantity}] */
    public function batchCreateAllocation(): Response
    {
        $tenantId = $this->getTenantId();
        $orderId = (int) $this->request->post('order_id', 0);
        $planId = (int) $this->request->post('plan_id', 0);
        $allocations = $this->request->post('allocations/a') ?: [];
        if (is_string($this->request->post('allocations'))) {
            $decoded = json_decode($this->request->post('allocations'), true);
            if (is_array($decoded)) {
                $allocations = $decoded;
            }
        }
        if ($orderId <= 0 || empty($allocations)) {
            return $this->error('参数不完整');
        }
        $order = OrderModel::where('tenant_id', $tenantId)->find($orderId);
        if (!$order) {
            return $this->error('订单不存在');
        }
        if ($planId > 0) {
            $plan = ProductionPlanModel::where('tenant_id', $tenantId)->find($planId);
            if (!$plan || (int) $plan->order_id !== $orderId) {
                return $this->error('生产计划与订单不匹配');
            }
        }
        $created = [];
        foreach ($allocations as $item) {
            $modelId = (int) ($item['model_id'] ?? 0);
            $processId = (int) ($item['process_id'] ?? 0);
            $userId = (int) ($item['user_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($modelId <= 0 || $processId <= 0 || $userId <= 0 || $quantity <= 0) {
                continue;
            }
            $data = [
                'tenant_id' => $tenantId,
                'order_id' => $orderId,
                'plan_id' => $planId > 0 ? $planId : null,
                'model_id' => $modelId,
                'process_id' => $processId,
                'user_id' => $userId,
                'quantity' => $quantity,
                'allocation_code' => AllocationModel::generateAllocationCode(),
                'create_time' => time(),
                'update_time' => time(),
            ];
            $allocation = AllocationModel::create($data);
            $created[] = $allocation->id;
        }
        if (empty($created)) {
            return $this->error('没有有效的分配记录');
        }
        return $this->success('创建成功', ['ids' => $created]);
    }

    /** 生成分工二维码 POST id */
    public function generateQrcode(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $allocation = AllocationModel::with(['order', 'model.product', 'process'])->where('tenant_id', $tenantId)->find($id);
        if (!$allocation) {
            return $this->error('分工分配不存在');
        }
        $domain = $this->request->domain();
        $qrContent = $domain . '/index/worker/scan?allocation_id=' . $id;
        $qrImage = '';
        $allocation->qr_content = $qrContent;
        $allocation->qr_image = $qrImage;
        $allocation->save();
        $exists = AllocationQrcodeModel::where('tenant_id', $tenantId)->where('allocation_id', $id)->find();
        if ($exists) {
            $exists->qrcode_content = $qrContent;
            $exists->qrcode_image = $qrImage;
            $exists->qrcode_url = $qrContent;
            $exists->update_time = time();
            $exists->save();
        } else {
            AllocationQrcodeModel::create([
                'tenant_id' => $tenantId,
                'allocation_id' => $id,
                'qrcode_content' => $qrContent,
                'qrcode_image' => $qrImage,
                'qrcode_url' => $qrContent,
                'status' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ]);
        }
        return $this->success('二维码生成成功', ['qr_content' => $qrContent]);
    }

    // ---------- 报工与审核 ----------
    /** 列表项扁平化，与 report 项目一致便于前端展示 */
    private function flattenReportList($rows): array
    {
        $list = [];
        foreach ($rows as $r) {
            $arr = is_array($r) ? $r : $r->toArray();
            $allocation = is_object($r) ? $r->allocation : ($r['allocation'] ?? null);
            $order = $allocation && (is_object($allocation) ? $allocation->order : ($allocation['order'] ?? null));
            $model = $allocation && (is_object($allocation) ? $allocation->model : ($allocation['model'] ?? null));
            $product = $model && (is_object($model) ? $model->product : ($model['product'] ?? null));
            $process = $allocation && (is_object($allocation) ? $allocation->process : ($allocation['process'] ?? null));
            $user = is_object($r) ? $r->user : ($r['user'] ?? null);
            $arr['order_no'] = $order ? (is_object($order) ? ($order->order_no ?? '') : ($order['order_no'] ?? '')) : '';
            $arr['product_name'] = $product ? (is_object($product) ? ($product->name ?? '') : ($product['name'] ?? '')) : '';
            $arr['model_name'] = $model ? (is_object($model) ? ($model->name ?? '') : ($model['name'] ?? '')) : '';
            $arr['process_name'] = $process ? (is_object($process) ? ($process->name ?? '') : ($process['name'] ?? '')) : '';
            $arr['user_name'] = $user ? (is_object($user) ? ($user->nickname ?? $user->username ?? '') : ($user['nickname'] ?? $user['username'] ?? '')) : '';
            $list[] = $arr;
        }
        return $list;
    }

    public function getReports(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 10)));
        $status = $this->request->get('status', '');

        $query = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process', 'user'])
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $rows = $query->page($page, $limit)->select();
        $list = $this->flattenReportList($rows);
        return $this->success('获取成功', ['total' => $total, 'list' => $list]);
    }

    /** 待审核报工列表（status=0） */
    public function getActiveReports(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 10)));

        $query = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process', 'user'])
            ->where('tenant_id', $tenantId)
            ->where('status', 0)
            ->order('id', 'desc');
        $total = $query->count();
        $rows = $query->page($page, $limit)->select();
        $list = $this->flattenReportList($rows);
        return $this->success('获取成功', ['total' => $total, 'list' => $list]);
    }

    public function getReportDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $reportId = (int) $this->request->get('report_id', 0);
        if ($reportId <= 0) {
            return $this->error('参数错误');
        }
        $report = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process', 'user', 'media'])
            ->where('tenant_id', $tenantId)
            ->find($reportId);
        if (!$report) {
            return $this->error('报工记录不存在');
        }
        $out = $report->toArray();
        $out['order_no'] = $report->allocation && $report->allocation->order ? $report->allocation->order->order_no : '';
        $out['product_name'] = $report->allocation && $report->allocation->model && $report->allocation->model->product ? $report->allocation->model->product->name : '';
        $out['model_name'] = $report->allocation && $report->allocation->model ? $report->allocation->model->name : '';
        $out['process_name'] = $report->allocation && $report->allocation->process ? $report->allocation->process->name : '';
        $out['user_name'] = $report->user ? ($report->user->nickname ?? $report->user->username) : '';
        $out['images'] = [];
        $out['audit_images'] = [];
        $out['audit_videos'] = [];
        if ($report->media && !$report->media->isEmpty()) {
            foreach ($report->media as $m) {
                $url = trim((string) ($m->url ?? ''), " \t\n\r\0\x0B\"'");
                if ($url === '') {
                    continue;
                }
                $type = $m->type ?? 'image';
                $scene = $m->scene ?? '';
                if ($type === 'video' && ($scene === 'audit' || $scene === '')) {
                    $out['audit_videos'][] = $url;
                } elseif ($type === 'image' && $scene === 'audit') {
                    $out['audit_images'][] = $url;
                } else {
                    $out['images'][] = $url;
                }
            }
        }
        return $this->success('获取成功', $out);
    }

    /**
     * 报工统计（管理端报表：按日期范围汇总，可选按用户/工序）
     * GET start_date, end_date, [user_id], [process_id]
     */
    public function getReportStatistics(): Response
    {
        $tenantId = $this->getTenantId();
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-d'));
        $userId = $this->request->get('user_id', '');
        $processId = $this->request->get('process_id', '');

        $startTime = strtotime($startDate . ' 00:00:00');
        $endTime = strtotime($endDate . ' 23:59:59');

        $query = ReportModel::alias('r')
            ->join('mes_allocation a', 'r.allocation_id = a.id')
            ->where('r.tenant_id', $tenantId)
            ->where('r.status', 1)
            ->where('r.create_time', 'between', [$startTime, $endTime])
            ->field('r.user_id, a.process_id, SUM(r.quantity) as total_quantity, SUM(r.wage) as total_wage, COUNT(*) as report_count');
        if ($userId !== '' && $userId !== null) {
            $query->where('r.user_id', (int) $userId);
        }
        if ($processId !== '' && $processId !== null) {
            $query->where('a.process_id', (int) $processId);
        }
        $query->group('r.user_id, a.process_id');
        $rows = $query->select()->toArray();

        $summary = ['total_quantity' => 0, 'total_wage' => 0.0, 'report_count' => 0];
        foreach ($rows as $row) {
            $summary['total_quantity'] += (int) ($row['total_quantity'] ?? 0);
            $summary['total_wage'] += (float) ($row['total_wage'] ?? 0);
            $summary['report_count'] += (int) ($row['report_count'] ?? 0);
        }

        return $this->success('', [
            'summary' => $summary,
            'list' => $rows,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /** 审核报工 status 1通过 2拒绝；支持审核备注、质检状态、审核图片/视频（与PC对齐） */
    public function auditReport(): Response
    {
        $tenantId = $this->getTenantId();
        $adminId = $this->getAdminId();
        $reportId = (int) $this->request->post('report_id', 0);
        $status = (int) $this->request->post('status', 0);
        $auditReason = trim((string) $this->request->post('audit_reason', ''));
        $auditNotes = trim((string) $this->request->post('audit_notes', ''));
        $qualityStatus = (int) $this->request->post('quality_status', 1);
        if ($reportId <= 0 || !in_array($status, [1, 2], true)) {
            return $this->error('参数错误');
        }
        if ($status === 2 && $auditReason === '') {
            return $this->error('拒绝审核必须填写拒绝原因');
        }
        if (!in_array($qualityStatus, [0, 1], true)) {
            $qualityStatus = 1;
        }

        $report = ReportModel::where('tenant_id', $tenantId)->find($reportId);
        if (!$report) {
            return $this->error('报工记录不存在');
        }
        if ($report->status !== 0) {
            return $this->error('该记录已审核');
        }

        $auditImages = $this->parseMediaUrls($this->request->post('audit_images', ''));
        $auditVideos = $this->parseMediaUrls($this->request->post('audit_videos', ''));

        $report->status = $status;
        $report->audit_user_id = $adminId;
        $report->audit_time = time();
        $report->audit_reason = $auditReason;
        $report->audit_notes = $auditNotes;
        $report->quality_status = $qualityStatus;
        $report->save();

        foreach ($auditImages as $url) {
            $url = $this->normalizeReportMediaUrl($url);
            if ($url) {
                ReportMediaModel::create([
                    'tenant_id' => $tenantId,
                    'report_id' => $report->id,
                    'type' => 'image',
                    'scene' => 'audit',
                    'url' => $url,
                    'create_time' => time(),
                ]);
            }
        }
        foreach ($auditVideos as $url) {
            $url = $this->normalizeReportMediaUrl($url);
            if ($url) {
                ReportMediaModel::create([
                    'tenant_id' => $tenantId,
                    'report_id' => $report->id,
                    'type' => 'video',
                    'scene' => 'audit',
                    'url' => $url,
                    'create_time' => time(),
                ]);
            }
        }

        return $this->success('审核成功');
    }

    private function parseMediaUrls($raw): array
    {
        $trimQuotes = function ($u) {
            return trim((string) $u, " \t\n\r\0\x0B\"'");
        };
        if (is_array($raw)) {
            return array_filter(array_map($trimQuotes, $raw));
        }
        if (is_string($raw) && $raw !== '') {
            $dec = json_decode($raw, true);
            if (is_array($dec)) {
                return array_filter(array_map($trimQuotes, $dec));
            }
            return array_filter(array_map($trimQuotes, preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY)));
        }
        return [];
    }

    private function normalizeReportMediaUrl(string $url): string
    {
        $url = trim($url, " \t\n\r\0\x0B\"'");
        if ($url === '') {
            return '';
        }
        if (strpos($url, 'http') !== 0 && $url[0] !== '/') {
            $url = '/' . $url;
        }
        return $url;
    }

    public function deleteReport(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids') ?? $this->request->post('id');
        if ($ids === null || $ids === '') {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', (string) $ids);
        foreach ($arr as $id) {
            $report = ReportModel::where('tenant_id', $tenantId)->find($id);
            if ($report) {
                $report->delete();
            }
        }
        return $this->success('删除成功');
    }

    // ---------- 基础数据 ----------
    public function getProducts(): Response
    {
        $tenantId = $this->getTenantId();
        $list = ProductModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select()->toArray();
        return $this->success('获取成功', ['list' => $list]);
    }

    /** 产品型号列表（getModels） */
    public function getModels(): Response
    {
        $tenantId = $this->getTenantId();
        $productId = $this->request->get('product_id', '');
        $query = ProductModelModel::with('product')->where('tenant_id', $tenantId)->where('status', 1);
        if ($productId !== '' && $productId !== null) {
            $query->where('product_id', (int) $productId);
        }
        $list = $query->order('id', 'desc')->select()->toArray();
        return $this->success('获取成功', ['list' => $list]);
    }

    public function getProcesses(): Response
    {
        $tenantId = $this->getTenantId();
        $list = ProcessModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'asc')->select()->toArray();
        return $this->success('获取成功', ['list' => $list]);
    }

    public function getProcessPriceList(): Response
    {
        $tenantId = $this->getTenantId();
        $modelId = $this->request->get('model_id', '');
        $query = ProcessPriceModel::with(['model.product', 'process'])
            ->where('tenant_id', $tenantId)
            ->where('status', 1);
        if ($modelId !== '' && $modelId !== null) {
            $query->where('model_id', (int) $modelId);
        }
        $list = $query->select()->toArray();
        return $this->success('获取成功', ['list' => $list]);
    }

    /** 租户下用户列表（用于分配任务选人） */
    public function getUsers(): Response
    {
        $tenantId = $this->getTenantId();
        $list = UserModel::where('tenant_id', $tenantId)->where('status', 1)
            ->field('id,nickname,username,avatar')
            ->order('id', 'desc')
            ->select()
            ->toArray();
        return $this->success('获取成功', ['list' => $list]);
    }

    // ---------- 采购 ----------
    public function getPurchaseRequestList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $status = $this->request->get('status', '');
        $query = PurchaseRequestModel::with(['material', 'supplier', 'order'])->where('tenant_id', $tenantId)->order('id', 'desc');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getPurchaseList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $status = $this->request->get('status', '');
        $query = PurchaseInModel::with(['material', 'supplier', 'warehouse'])->where('tenant_id', $tenantId)->order('id', 'desc');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getPurchaseDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = PurchaseInModel::with(['material', 'supplier', 'warehouse'])->where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('入库单不存在');
        }
        return $this->success('', $row->toArray());
    }

    public function createPurchase(): Response
    {
        $tenantId = $this->getTenantId();
        $adminId = $this->getAdminId();
        $params = $this->request->post('row/a') ?: [];
        if (empty($params) || empty($params['material_id'])) {
            return $this->error('参数不完整');
        }
        $params['tenant_id'] = $tenantId;
        $params['operator_id'] = $adminId;
        $params['in_no'] = $params['in_no'] ?? PurchaseInModel::generateInNo();
        if (!empty($params['in_time'])) {
            $params['in_time'] = is_numeric($params['in_time']) ? (int) $params['in_time'] : strtotime($params['in_time']);
        }
        Db::startTrans();
        try {
            $inbound = PurchaseInModel::create($params);
            $material = MaterialModel::where('tenant_id', $tenantId)->find($params['material_id']);
            if ($material) {
                $qty = (float) ($params['in_quantity'] ?? 0);
                $beforeQty = (float) $material->stock;
                $material->stock = $beforeQty + $qty;
                $material->save();
                StockLogModel::log($tenantId, (int) $params['material_id'], $qty, 'purchase_in', $inbound->id, $adminId, '采购入库：' . $inbound->in_no, $beforeQty, $beforeQty + $qty);
            }
            if (!empty($params['purchase_request_id'])) {
                $req = PurchaseRequestModel::where('tenant_id', $tenantId)->find($params['purchase_request_id']);
                if ($req) {
                    $req->status = 2;
                    $req->save();
                }
            }
            Db::commit();
            return $this->success('入库成功', ['id' => $inbound->id]);
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('入库失败：' . $e->getMessage());
        }
    }

    public function updatePurchase(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $row = PurchaseInModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('入库单不存在');
        }
        $row->save($params);
        return $this->success('编辑成功');
    }

    public function deletePurchase(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $row = PurchaseInModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
            }
        }
        return $this->success('删除成功');
    }

    public function purchaseInbound(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $inbound = PurchaseInModel::where('tenant_id', $tenantId)->find($id);
        if (!$inbound) {
            return $this->error('入库单不存在');
        }
        if ((int) $inbound->status === 1) {
            return $this->error('该入库单已确认');
        }
        $inbound->status = 1;
        $inbound->in_time = time();
        $inbound->save();
        return $this->success('确认成功');
    }

    // ---------- 发货 ----------
    public function getShipmentList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $status = $this->request->get('status', '');
        $query = ShipmentModel::with(['order', 'customer'])->where('tenant_id', $tenantId)->order('id', 'desc');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getShipmentDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = ShipmentModel::with(['order', 'customer', 'items'])->where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('发货单不存在');
        }
        return $this->success('', $row->toArray());
    }

    public function createShipment(): Response
    {
        $tenantId = $this->getTenantId();
        $adminId = $this->getAdminId();
        $params = $this->request->post('row/a') ?: [];
        $items = $this->request->post('items/a') ?: [];
        if (empty($params) || empty($params['order_id'])) {
            return $this->error('参数不完整');
        }
        $orderId = (int) $params['order_id'];
        $order = OrderModel::where('tenant_id', $tenantId)->find($orderId);
        if (!$order) {
            return $this->error('订单不存在');
        }
        $params['tenant_id'] = $tenantId;
        $params['order_id'] = $orderId;
        $params['customer_id'] = $order->customer_id ?? 0;
        $params['shipment_no'] = $params['shipment_no'] ?? ShipmentModel::generateShipmentNo();
        $params['operator_id'] = $adminId;
        if (!empty($params['shipment_time'])) {
            $params['shipment_time'] = is_numeric($params['shipment_time']) ? (int) $params['shipment_time'] : strtotime($params['shipment_time']);
        }
        Db::startTrans();
        try {
            $shipment = ShipmentModel::create($params);
            foreach ($items as $it) {
                $modelId = (int) ($it['model_id'] ?? 0);
                $qty = (int) ($it['quantity'] ?? 0);
                if ($modelId > 0 && $qty > 0) {
                    ShipmentItemModel::create([
                        'tenant_id' => $tenantId,
                        'shipment_id' => $shipment->id,
                        'model_id' => $modelId,
                        'quantity' => $qty,
                    ]);
                }
            }
            Db::commit();
            return $this->success('添加成功', ['id' => $shipment->id]);
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('添加失败：' . $e->getMessage());
        }
    }

    public function updateShipment(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        $items = $this->request->post('items/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $row = ShipmentModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('发货单不存在');
        }
        $row->save($params);
        if (is_array($items)) {
            ShipmentItemModel::where('shipment_id', $id)->delete();
            foreach ($items as $it) {
                $modelId = (int) ($it['model_id'] ?? 0);
                $qty = (int) ($it['quantity'] ?? 0);
                if ($modelId > 0 && $qty > 0) {
                    ShipmentItemModel::create([
                        'tenant_id' => $tenantId,
                        'shipment_id' => $id,
                        'model_id' => $modelId,
                        'quantity' => $qty,
                    ]);
                }
            }
        }
        return $this->success('编辑成功');
    }

    public function deleteShipment(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $row = ShipmentModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                ShipmentItemModel::where('shipment_id', $id)->delete();
                $row->delete();
            }
        }
        return $this->success('删除成功');
    }

    // ---------- 质检 ----------
    public function getQualityStandards(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $query = QualityStandardModel::with(['process', 'model'])->where('tenant_id', $tenantId)->order('id', 'desc');
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getQualityChecks(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $status = $this->request->get('status', '');
        $query = QualityCheckModel::with(['report', 'standard'])->where('tenant_id', $tenantId)->order('id', 'desc');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    // ---------- 工资 ----------
    public function getWageList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $userId = $this->request->get('user_id', '');
        $workDate = $this->request->get('work_date', '');
        $query = WageModel::with(['user'])->where('tenant_id', $tenantId)->order('work_date', 'desc')->order('id', 'desc');
        if ($userId !== '' && $userId !== null) {
            $query->where('user_id', (int) $userId);
        }
        if ($workDate !== '') {
            $query->where('work_date', $workDate);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getWageStatistics(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $list = Db::name('mes_wage')
            ->alias('w')
            ->join('fa_user u', 'w.user_id = u.id')
            ->where('w.tenant_id', $tenantId)
            ->field('w.user_id, u.nickname, SUM(w.quantity) as total_quantity, SUM(w.total_wage) as total_wage, COUNT(*) as record_count')
            ->group('w.user_id')
            ->order('total_wage', 'desc')
            ->page($page, $limit)
            ->select()
            ->toArray();
        $total = Db::name('mes_wage')->where('tenant_id', $tenantId)->group('user_id')->count();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    // ---------- 追溯码 ----------
    public function getTraceCodeList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $traceCode = trim((string) $this->request->get('trace_code', ''));
        $query = TraceCodeModel::with(['order', 'model.product', 'process', 'report'])->where('tenant_id', $tenantId)->order('id', 'desc');
        if ($traceCode !== '') {
            $query->where('trace_code', 'like', '%' . $traceCode . '%');
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function generateTraceCode(): Response
    {
        $tenantId = $this->getTenantId();
        $reportId = (int) $this->request->post('report_id', 0);
        if ($reportId <= 0) {
            return $this->error('报工ID不能为空');
        }
        $report = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process'])->where('tenant_id', $tenantId)->find($reportId);
        if (!$report) {
            return $this->error('报工记录不存在');
        }
        $exists = TraceCodeModel::where('tenant_id', $tenantId)->where('report_id', $reportId)->find();
        if ($exists) {
            return $this->success('追溯码已存在', ['trace_code' => $exists->trace_code]);
        }
        $traceCode = TraceCodeModel::generateTraceCode();
        $allocation = $report->allocation;
        $domain = $this->request->domain();
        $qrUrl = $domain . '/index/trace/query?code=' . $traceCode;
        $trace = TraceCodeModel::create([
            'tenant_id' => $tenantId,
            'trace_code' => $traceCode,
            'report_id' => $reportId,
            'allocation_id' => $allocation->id ?? 0,
            'order_id' => $allocation->order_id ?? 0,
            'model_id' => $allocation->model_id ?? 0,
            'process_id' => $allocation->process_id ?? 0,
            'item_no' => $traceCode,
            'qr_url' => $qrUrl,
            'status' => 1,
        ]);
        return $this->success('生成成功', ['trace_code' => $traceCode, 'qr_url' => $qrUrl]);
    }

    /**
     * 扫码报工：扫工序/任务条码返回任务信息及建议报工数量
     * GET code= 分配ID 或 含 allocation_id= 的 URL
     */
    public function getTaskByScan(): Response
    {
        $tenantId = $this->getTenantId();
        $code = trim((string) $this->request->get('code', ''));
        if ($code === '') {
            return $this->error('请扫描任务条码或输入分配ID');
        }
        $allocationId = null;
        if (preg_match('/^[1-9]\d*$/', $code)) {
            $allocationId = (int) $code;
        } elseif (preg_match('/allocation_id=(\d+)/i', $code, $m)) {
            $allocationId = (int) $m[1];
        }
        if ($allocationId === null || $allocationId <= 0) {
            return $this->error('无效的条码内容');
        }
        $allocation = AllocationModel::with(['order', 'model.product', 'process'])->where('tenant_id', $tenantId)->find($allocationId);
        if (!$allocation) {
            return $this->error('任务不存在或已失效');
        }
        $quantity = (int) $allocation->quantity;
        $completed = (int) $allocation->completed_quantity;
        $defaultQuantity = max(0, $quantity - $completed);
        $orderNo = $allocation->order ? $allocation->order->order_no : '';
        $modelName = '';
        if ($allocation->model) {
            $modelName = $allocation->model->name ?? '';
            if ($modelName === '' && isset($allocation->model->product) && $allocation->model->product) {
                $modelName = $allocation->model->product->name ?? '';
            }
        }
        $processName = $allocation->process ? $allocation->process->name : '';
        $data = [
            'id' => $allocation->id,
            'order_id' => $allocation->order_id,
            'model_id' => $allocation->model_id,
            'process_id' => $allocation->process_id,
            'quantity' => $quantity,
            'completed_quantity' => $completed,
            'status' => $allocation->status,
            'order_no' => $orderNo,
            'model_name' => $modelName,
            'process_name' => $processName,
            'default_quantity' => $defaultQuantity,
        ];
        return $this->success('', $data);
    }

    public function queryTraceCode(): Response
    {
        $tenantId = $this->getTenantId();
        $code = trim((string) $this->request->get('code', ''));
        if ($code === '') {
            return $this->error('追溯码不能为空');
        }
        $row = TraceCodeModel::with(['order', 'model.product', 'process', 'report'])->where('tenant_id', $tenantId)->where('trace_code', $code)->find();
        if (!$row) {
            return $this->error('追溯码不存在');
        }
        return $this->success('', $row->toArray());
    }

    // ---------- 售后 ----------
    public function getAfterSalesList(): Response
    {
        $tenantId = $this->getTenantId();
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $status = $this->request->get('status', '');
        $query = AfterSalesModel::with(['order', 'customer'])->where('tenant_id', $tenantId)->order('id', 'desc');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function getAfterSalesDetail(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = AfterSalesModel::with(['order', 'customer'])->where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('售后单不存在');
        }
        return $this->success('', $row->toArray());
    }

    public function createAfterSales(): Response
    {
        $tenantId = $this->getTenantId();
        $adminId = $this->getAdminId();
        $params = $this->request->post('row/a') ?: [];
        if (empty($params)) {
            return $this->error('参数不能为空');
        }
        $params['tenant_id'] = $tenantId;
        $params['operator_id'] = $adminId;
        $params['after_sales_no'] = $params['after_sales_no'] ?? AfterSalesModel::generateAfterSalesNo();
        $row = AfterSalesModel::create($params);
        return $this->success('提交成功', ['id' => $row->id]);
    }

    public function updateAfterSales(): Response
    {
        $tenantId = $this->getTenantId();
        $id = (int) $this->request->post('id', 0);
        $params = $this->request->post('row/a') ?: [];
        if ($id <= 0 || empty($params)) {
            return $this->error('参数错误');
        }
        $row = AfterSalesModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('售后单不存在');
        }
        $row->save($params);
        return $this->success('编辑成功');
    }

    public function deleteAfterSales(): Response
    {
        $tenantId = $this->getTenantId();
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $arr = is_array($ids) ? $ids : explode(',', $ids);
        foreach ($arr as $id) {
            $row = AfterSalesModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
            }
        }
        return $this->success('删除成功');
    }

    // ---------- BI ----------
    public function getDashboardData(): Response
    {
        $tenantId = $this->getTenantId();
        $today = date('Y-m-d');
        $todayStart = strtotime($today . ' 00:00:00');
        $todayEnd = strtotime($today . ' 23:59:59');
        $todayReports = ReportModel::where('tenant_id', $tenantId)->where('status', 1)
            ->where('create_time', 'between', [$todayStart, $todayEnd])
            ->field('SUM(quantity) as total_quantity, SUM(wage) as total_wage, COUNT(*) as report_count')
            ->find();
        $orderStats = OrderModel::where('tenant_id', $tenantId)->field('status, COUNT(*) as count')->group('status')->select();
        $orderData = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
        foreach ($orderStats as $stat) {
            $orderData[$stat->status] = $stat->count;
        }
        $planStats = ProductionPlanModel::where('tenant_id', $tenantId)->field('status, COUNT(*) as count')->group('status')->select();
        $planData = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
        foreach ($planStats as $stat) {
            $planData[$stat->status] = $stat->count;
        }
        $activeAllocations = AllocationModel::where('tenant_id', $tenantId)->where('status', 1)->whereColumn('completed_quantity', '<', 'quantity')->count();
        $pendingReports = ReportModel::where('tenant_id', $tenantId)->where('status', 0)->count();
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dateStart = strtotime($date . ' 00:00:00');
            $dateEnd = strtotime($date . ' 23:59:59');
            $dayReport = ReportModel::where('tenant_id', $tenantId)->where('status', 1)
                ->where('create_time', 'between', [$dateStart, $dateEnd])
                ->field('SUM(quantity) as quantity, SUM(wage) as wage, COUNT(*) as count')
                ->find();
            $trendData[] = [
                'date' => $date,
                'quantity' => (float) ($dayReport->quantity ?? 0),
                'wage' => (float) ($dayReport->wage ?? 0),
                'count' => (int) ($dayReport->count ?? 0),
            ];
        }
        $data = [
            'today' => [
                'quantity' => (float) ($todayReports->total_quantity ?? 0),
                'wage' => (float) ($todayReports->total_wage ?? 0),
                'report_count' => (int) ($todayReports->report_count ?? 0),
            ],
            'order_data' => $orderData,
            'plan_data' => $planData,
            'active_allocations' => $activeAllocations,
            'pending_reports' => $pendingReports,
            'trend' => $trendData,
        ];
        return $this->success('', $data);
    }

    /**
     * BI - 生产效率报表（按日期）
     * GET start_date, end_date
     */
    public function getBiProductionEfficiency(): Response
    {
        $tenantId = $this->getTenantId();
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-d'));
        $startTime = strtotime($startDate . ' 00:00:00');
        $endTime = strtotime($endDate . ' 23:59:59');

        $query = ReportModel::alias('r')
            ->join('mes_allocation a', 'r.allocation_id = a.id')
            ->where('r.tenant_id', $tenantId)
            ->where('r.status', 1)
            ->where('r.create_time', 'between', [$startTime, $endTime])
            ->field("DATE(FROM_UNIXTIME(r.create_time)) as stat_date, COUNT(DISTINCT r.user_id) as worker_count, SUM(r.quantity) as total_quantity, SUM(r.work_hours) as total_hours, SUM(r.wage) as total_wage, COUNT(*) as report_count")
            ->group('stat_date')
            ->order('stat_date', 'desc');
        $list = $query->select()->toArray();
        return $this->success('', ['total' => count($list), 'list' => $list]);
    }

    /**
     * BI - 质量分析报表（按日期）
     * GET start_date, end_date
     */
    public function getBiQualityAnalysis(): Response
    {
        $tenantId = $this->getTenantId();
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-d'));
        $startTime = strtotime($startDate . ' 00:00:00');
        $endTime = strtotime($endDate . ' 23:59:59');

        $query = ReportModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->where('create_time', 'between', [$startTime, $endTime])
            ->field("DATE(FROM_UNIXTIME(create_time)) as stat_date, COUNT(*) as total_count, SUM(CASE WHEN quality_status = 1 THEN 1 ELSE 0 END) as qualified_count, SUM(CASE WHEN quality_status = 2 THEN 1 ELSE 0 END) as unqualified_count")
            ->group('stat_date')
            ->order('stat_date', 'desc');
        $list = $query->select()->toArray();
        foreach ($list as &$row) {
            $row['qualified_rate'] = ($row['total_count'] ?? 0) > 0
                ? round(((float) ($row['qualified_count'] ?? 0) / (float) $row['total_count']) * 100, 2)
                : 0;
        }
        return $this->success('', ['total' => count($list), 'list' => $list]);
    }

    /**
     * BI - 成本分析报表（按订单）
     * GET start_date, end_date
     */
    public function getBiCostAnalysis(): Response
    {
        $tenantId = $this->getTenantId();
        $startDate = $this->request->get('start_date', date('Y-m-01'));
        $endDate = $this->request->get('end_date', date('Y-m-d'));
        $startTime = strtotime($startDate . ' 00:00:00');
        $endTime = strtotime($endDate . ' 23:59:59');

        $wageTable = (new WageModel())->getTable();
        $reportTable = (new ReportModel())->getTable();
        $allocationTable = (new AllocationModel())->getTable();

        $list = OrderModel::alias('o')
            ->leftJoin('mes_order_material om', 'o.id = om.order_id')
            ->where('o.tenant_id', $tenantId)
            ->where('o.create_time', 'between', [$startTime, $endTime])
            ->field("o.id, o.order_no, o.order_name, SUM(COALESCE(om.estimated_amount,0)) as material_cost, (SELECT COALESCE(SUM(w.total_wage),0) FROM {$wageTable} w INNER JOIN {$reportTable} r ON w.report_id = r.id INNER JOIN {$allocationTable} a ON r.allocation_id = a.id WHERE a.order_id = o.id) as labor_cost")
            ->group('o.id')
            ->order('o.id', 'desc')
            ->select()
            ->toArray();

        foreach ($list as &$row) {
            $row['material_cost'] = (float) ($row['material_cost'] ?? 0);
            $row['labor_cost'] = (float) ($row['labor_cost'] ?? 0);
            $row['total_cost'] = $row['material_cost'] + $row['labor_cost'];
        }
        return $this->success('', ['total' => count($list), 'list' => $list]);
    }

    // ---------- 上传（审核图/报工图） ----------
    public function uploadAuditImage(): Response
    {
        return $this->uploadImage('audit');
    }

    public function uploadReportImage(): Response
    {
        return $this->uploadImage('report');
    }

    /** 审核视频上传（与PC对齐） */
    public function uploadAuditVideo(): Response
    {
        $file = $this->request->file('file') ?? $this->request->file('video');
        if (!$file || !$file->isValid()) {
            return $this->error('请选择视频');
        }
        $ext = strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION));
        if (!in_array($ext, ['mp4', 'mov', 'avi', 'webm', 'm4v'], true)) {
            return $this->error('仅支持 mp4/mov/avi/webm/m4v');
        }
        $relDir = 'uploads/scanwork/audit/' . date('Ymd') . '/';
        $root = app()->getRootPath();
        $fullDir = $root . 'public/' . $relDir;
        if (!is_dir($fullDir)) {
            @mkdir($fullDir, 0755, true);
        }
        $filename = uniqid() . '.' . $ext;
        $info = $file->move($fullDir, $filename);
        if (!$info) {
            return $this->error('上传失败');
        }
        $url = '/' . $relDir . $filename;
        return $this->success('上传成功', ['url' => $url]);
    }

    private function uploadImage(string $subDir): Response
    {
        $file = $this->request->file('file') ?? $this->request->file('image');
        if (!$file || !$file->isValid()) {
            return $this->error('请选择图片');
        }
        $ext = strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return $this->error('仅支持 jpg/png/gif/webp');
        }
        $relDir = 'uploads/scanwork/' . $subDir . '/' . date('Ymd') . '/';
        $root = app()->getRootPath();
        $fullDir = $root . 'public/' . $relDir;
        if (!is_dir($fullDir)) {
            @mkdir($fullDir, 0755, true);
        }
        $filename = uniqid() . '.' . $ext;
        $info = $file->move($fullDir, $filename);
        if (!$info) {
            return $this->error('上传失败');
        }
        $url = '/' . $relDir . $filename;
        return $this->success('上传成功', ['url' => $url]);
    }
}
