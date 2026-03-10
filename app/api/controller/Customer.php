<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\admin\model\mes\CustomerModel;
use app\admin\model\mes\CustomerProductModel;
use app\admin\model\mes\CustomerOrderModel;
use app\admin\model\mes\CustomerOrderItemModel;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\OrderModelModel;
use app\admin\model\mes\ShipmentModel;
use app\admin\model\mes\AllocationModel;
use app\admin\model\TenantModel;
use app\api\middleware\CustomerAuth;
use think\facade\Db;
use think\Response;

class Customer extends BaseController
{
    protected function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    protected function getCustomerId(): int
    {
        return (int) ($this->request->customerId ?? 0);
    }

    /**
     * 客户登录：不依赖域名绑定，先凭账号密码登录，再按客户所属租户使用。
     * 未传租户时按 login_account 全局查找，唯一则用其 tenant_id；多个则提示指定租户。
     */
    public function login(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) {
            $tenantId = max(0, (int) $this->request->post('tenant_id', 0));
        }

        $account = trim((string) $this->request->post('login_account', ''));
        $password = (string) $this->request->post('password', '');

        if ($account === '' || $password === '') {
            return $this->error('请输入账号和密码');
        }
        if (strlen($password) < 6 || strlen($password) > 64) {
            return $this->error('密码长度需在 6-64 位之间');
        }

        $customer = null;
        if ($tenantId > 0) {
            $customer = CustomerModel::where('tenant_id', $tenantId)
                ->where('login_account', $account)
                ->where('status', 1)
                ->find();
        } else {
            $list = CustomerModel::where('login_account', $account)->where('status', 1)->select();
            $n = $list->count();
            if ($n === 1) {
                $customer = $list[0];
                $tenantId = (int) $customer->tenant_id;
            } elseif ($n > 1) {
                return $this->error('存在多个账号，请通过指定租户的链接登录（如 ?tenant_id=1）');
            }
        }
        if (!$customer) {
            return $this->error('账号不存在或已禁用');
        }

        $hash = (string) $customer->getData('login_password');
        if ($hash === '' || !password_verify($password, $hash)) {
            return $this->error('密码错误');
        }

        $token = CustomerAuth::makeToken((int) $customer->id, $tenantId);
        $out = $customer->toArray();
        unset($out['login_password']);
        $out['token'] = $token;

        return $this->success('登录成功', $out);
    }

    public function profile(): Response
    {
        $info = $this->request->customerInfo ?? [];
        if (empty($info)) {
            return $this->error('请先登录', 0);
        }
        unset($info['login_password']);

        $tenantId = (int) ($this->request->tenantId ?? 0);
        if ($tenantId > 0) {
            try {
                $tenant = TenantModel::find($tenantId);
                if ($tenant) {
                    $tenantArr = $tenant->toArray();
                    $companyName = (string) ($tenantArr['company_name'] ?? '');
                    $name = (string) ($tenantArr['name'] ?? '');
                    $info['tenant_id'] = $tenantId;
                    $info['tenant_name'] = $name;
                    $info['tenant_company_name'] = $companyName !== '' ? $companyName : $name;
                }
            } catch (\Throwable $e) {
            }
        }

        return $this->success('', $info);
    }

    public function products(): Response
    {
        $tenantId = $this->getTenantId();
        $customerId = $this->getCustomerId();
        if ($tenantId <= 0 || $customerId <= 0) {
            return $this->error('未识别租户或客户', 0);
        }

        $list = CustomerProductModel::with(['model.product'])
            ->where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->where('status', 1)
            ->order('id', 'desc')
            ->select();

        $out = [];
        foreach ($list as $row) {
            $arr = $row->toArray();
            $productName = $row->model && $row->model->product ? (string) $row->model->product->name : '';
            $modelName = $row->model ? (string) $row->model->name : '';
            $modelCode = $row->model ? (string) $row->model->model_code : '';
            $displayName = $productName !== '' ? ($productName . ' - ' . $modelName) : $modelName;
            if ($modelCode !== '') {
                $displayName .= ' (' . $modelCode . ')';
            }
            $out[] = [
                'id' => (int) $row->id,
                'product_id' => (int) $row->product_id,
                'model_id' => (int) $row->model_id,
                'name' => $displayName,
                'price' => (float) $row->price,
                'currency' => (string) $row->currency,
                'min_qty' => (int) $row->min_qty,
                'remark' => (string) $row->remark,
                'status' => (int) $row->status,
            ];
        }

        return $this->success('', ['list' => $out]);
    }

    public function createOrder(): Response
    {
        $tenantId = $this->getTenantId();
        $customerId = $this->getCustomerId();
        if ($tenantId <= 0 || $customerId <= 0) {
            return $this->error('未识别租户或客户');
        }

        $items = [];
        $itemsJson = $this->request->post('items_json');
        if (is_string($itemsJson) && $itemsJson !== '') {
            $decoded = json_decode($itemsJson, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $items = $decoded;
            }
        }
        if (empty($items)) {
            $itemsRaw = $this->request->post('items');
            if (is_string($itemsRaw) && $itemsRaw !== '') {
                $decoded = json_decode($itemsRaw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $items = $decoded;
                }
            } elseif (is_array($itemsRaw)) {
                $items = $itemsRaw;
            }
        }
        if (empty($items)) {
            $raw = (string) $this->request->getContent();
            if ($raw !== '') {
                parse_str($raw, $parsed);
                $itemsJson = $parsed['items_json'] ?? $parsed['items'] ?? '';
                if (is_string($itemsJson) && $itemsJson !== '') {
                    $decoded = json_decode($itemsJson, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $items = $decoded;
                    }
                }
            }
        }
        if (empty($items)) {
            return $this->error('订单明细不能为空');
        }

        $customer = CustomerModel::where('tenant_id', $tenantId)
            ->where('id', $customerId)
            ->where('status', 1)
            ->find();
        if (!$customer) {
            return $this->error('客户不存在或已禁用');
        }

        $detailRows = [];
        $totalAmount = 0.0;
        $currency = 'CNY';

        foreach ($items as $item) {
            $cpId = (int) ($item['customer_product_id'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 0);
            if ($cpId <= 0 || $qty <= 0) {
                continue;
            }

            $cp = CustomerProductModel::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->where('id', $cpId)
                ->where('status', 1)
                ->find();
            if (!$cp) {
                return $this->error('有无效产品，请刷新页面后重试');
            }

            $minQty = (int) $cp->min_qty;
            if ($qty < $minQty) {
                return $this->error('产品 ' . (string) $cp->id . ' 数量不能小于起订量 ' . $minQty);
            }

            $price = (float) $cp->price;
            $amount = $price * $qty;
            $totalAmount += $amount;
            $currency = (string) $cp->currency;

            $detailRows[] = [
                'customer_product_id' => (int) $cp->id,
                'product_id' => (int) $cp->product_id,
                'model_id' => (int) $cp->model_id,
                'quantity' => $qty,
                'price' => $price,
                'amount' => $amount,
            ];
        }

        if (empty($detailRows)) {
            return $this->error('有效订单明细为空');
        }

        if ($totalAmount <= 0) {
            return $this->error('订单金额需大于 0');
        }

        $orderName = trim((string) $this->request->post('order_name', ''));
        $remark = trim((string) $this->request->post('remark', ''));

        $now = time();
        $customerOrderNo = CustomerOrderModel::generateCustomerOrderNo();

        $modelQtyMap = [];
        foreach ($detailRows as $row) {
            $mid = (int) ($row['model_id'] ?? 0);
            $qty = (int) ($row['quantity'] ?? 0);
            if ($mid > 0 && $qty > 0) {
                $modelQtyMap[$mid] = ($modelQtyMap[$mid] ?? 0) + $qty;
            }
        }

        Db::startTrans();
        try {
            $customerOrder = CustomerOrderModel::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'customer_order_no' => $customerOrderNo,
                'internal_order_id' => 0,
                'status' => 0,
                'total_amount' => $totalAmount,
                'currency' => $currency,
                'remark' => $remark !== '' ? $remark : $orderName,
                'create_time' => $now,
                'update_time' => $now,
            ]);

            foreach ($detailRows as $row) {
                $row['tenant_id'] = $tenantId;
                $row['customer_order_id'] = (int) $customerOrder->id;
                $row['create_time'] = $now;
                $row['update_time'] = $now;
                CustomerOrderItemModel::create($row);
            }

            // 同时创建内部订单，使后台「订单管理」立即可见（入库）
            $internalOrder = OrderModel::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'order_no' => OrderModel::generateOrderNo(),
                'order_name' => $orderName !== '' ? $orderName : ('客户订单 ' . $customerOrderNo),
                'status' => 0,
                'customer_name' => (string) ($customer->customer_name ?? ''),
                'customer_phone' => (string) ($customer->contact_phone ?? ''),
                'total_quantity' => 0,
                'create_time' => $now,
                'update_time' => $now,
                'remark' => $remark !== '' ? $remark : $orderName,
            ]);
            $totalQuantity = 0;
            foreach ($modelQtyMap as $mid => $qty) {
                if ($mid <= 0 || $qty <= 0) {
                    continue;
                }
                OrderModelModel::create([
                    'tenant_id' => $tenantId,
                    'order_id' => (int) $internalOrder->id,
                    'model_id' => $mid,
                    'quantity' => $qty,
                    'create_time' => $now,
                ]);
                $totalQuantity += $qty;
            }
            if ($totalQuantity > 0) {
                OrderModel::where('id', (int) $internalOrder->id)->update(['total_quantity' => $totalQuantity]);
            }
            CustomerOrderModel::where('id', (int) $customerOrder->id)->update([
                'internal_order_id' => (int) $internalOrder->id,
                'status' => 1,
                'update_time' => $now,
            ]);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('创建订单失败');
        }

        return $this->success('下单成功', [
            'id' => (int) $customerOrder->id,
            'customer_order_no' => $customerOrderNo,
            'total_amount' => $totalAmount,
            'currency' => $currency,
        ]);
    }

    public function confirmOrder(): Response
    {
        $tenantId = $this->getTenantId();
        $customerId = $this->getCustomerId();
        if ($tenantId <= 0 || $customerId <= 0) {
            return $this->error('未识别租户或客户');
        }
        $customerOrderId = (int) $this->request->post('customer_order_id', 0);
        if ($customerOrderId <= 0) {
            return $this->error('请指定客户订单');
        }
        $customerOrder = CustomerOrderModel::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->find($customerOrderId);
        if (!$customerOrder || (int) $customerOrder->internal_order_id > 0) {
            return $this->error('订单不存在或已确认');
        }
        if ((int) $customerOrder->status !== 0) {
            return $this->error('订单状态不允许确认');
        }
        $customer = CustomerModel::where('tenant_id', $tenantId)->where('id', $customerId)->where('status', 1)->find();
        if (!$customer) {
            return $this->error('客户不存在或已禁用');
        }
        $items = CustomerOrderItemModel::where('tenant_id', $tenantId)
            ->where('customer_order_id', $customerOrderId)
            ->select();
        $modelQtyMap = [];
        $totalAmount = 0.0;
        foreach ($items as $row) {
            $mid = (int) $row->model_id;
            $qty = (int) $row->quantity;
            if ($mid > 0 && $qty > 0) {
                if (!isset($modelQtyMap[$mid])) {
                    $modelQtyMap[$mid] = 0;
                }
                $modelQtyMap[$mid] += $qty;
                $totalAmount += (float) $row->amount;
            }
        }
        if (empty($modelQtyMap)) {
            return $this->error('订单无有效明细');
        }
        $orderName = trim((string) $customerOrder->remark);
        $now = time();
        Db::startTrans();
        try {
            $internalOrder = OrderModel::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'order_no' => OrderModel::generateOrderNo(),
                'order_name' => $orderName !== '' ? $orderName : ('客户订单 ' . $customerOrder->customer_order_no),
                'status' => 0,
                'customer_name' => (string) ($customer->customer_name ?? ''),
                'customer_phone' => (string) ($customer->contact_phone ?? ''),
                'total_quantity' => 0,
                'create_time' => $now,
                'update_time' => $now,
                'remark' => (string) $customerOrder->remark,
                'total_amount' => $totalAmount,
            ]);
            $totalQuantity = 0;
            foreach ($modelQtyMap as $mid => $qty) {
                if ($mid <= 0 || $qty <= 0) {
                    continue;
                }
                OrderModelModel::create([
                    'tenant_id' => $tenantId,
                    'order_id' => (int) $internalOrder->id,
                    'model_id' => $mid,
                    'quantity' => $qty,
                    'create_time' => $now,
                ]);
                $totalQuantity += $qty;
            }
            if ($totalQuantity > 0) {
                OrderModel::where('id', (int) $internalOrder->id)->update(['total_quantity' => $totalQuantity]);
            }
            CustomerOrderModel::where('id', $customerOrderId)->update([
                'internal_order_id' => (int) $internalOrder->id,
                'status' => 1,
                'update_time' => $now,
            ]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('确认订单失败');
        }
        return $this->success('订单已确认');
    }

    public function updateOrder(): Response
    {
        $tenantId = $this->getTenantId();
        $customerId = $this->getCustomerId();
        if ($tenantId <= 0 || $customerId <= 0) {
            return $this->error('未识别租户或客户');
        }
        $customerOrderId = (int) $this->request->post('customer_order_id', 0);
        if ($customerOrderId <= 0) {
            return $this->error('请指定客户订单');
        }
        $customerOrder = CustomerOrderModel::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->find($customerOrderId);
        if (!$customerOrder || (int) $customerOrder->status !== 0) {
            return $this->error('订单不存在或不可修改');
        }
        $items = [];
        $itemsJson = $this->request->post('items_json');
        if (is_string($itemsJson) && $itemsJson !== '') {
            $decoded = json_decode($itemsJson, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $items = $decoded;
            }
        }
        if (empty($items)) {
            $itemsRaw = $this->request->post('items');
            if (is_string($itemsRaw) && $itemsRaw !== '') {
                $decoded = json_decode($itemsRaw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $items = $decoded;
                }
            }
        }
        if (empty($items)) {
            $raw = (string) $this->request->getContent();
            if ($raw !== '') {
                parse_str($raw, $parsed);
                $itemsJson = $parsed['items_json'] ?? $parsed['items'] ?? '';
                if (is_string($itemsJson) && $itemsJson !== '') {
                    $decoded = json_decode($itemsJson, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $items = $decoded;
                    }
                }
            }
        }
        if (empty($items)) {
            return $this->error('请提交要保留的明细');
        }
        $existingItems = CustomerOrderItemModel::where('tenant_id', $tenantId)
            ->where('customer_order_id', $customerOrderId)
            ->select();
        $existingMap = [];
        foreach ($existingItems as $row) {
            $existingMap[(int) $row->id] = $row;
        }
        $newTotal = 0.0;
        $toUpdate = [];
        foreach ($items as $it) {
            $id = (int) ($it['id'] ?? 0);
            $qty = (int) ($it['quantity'] ?? 0);
            if ($id <= 0 || $qty <= 0 || !isset($existingMap[$id])) {
                continue;
            }
            $row = $existingMap[$id];
            $price = (float) $row->price;
            $amount = $price * $qty;
            $newTotal += $amount;
            $toUpdate[] = ['id' => $id, 'quantity' => $qty, 'amount' => $amount];
        }
        if (empty($toUpdate)) {
            return $this->error('有效明细为空');
        }
        $now = time();
        foreach ($toUpdate as $u) {
            CustomerOrderItemModel::where('tenant_id', $tenantId)
                ->where('customer_order_id', $customerOrderId)
                ->where('id', $u['id'])
                ->update(['quantity' => $u['quantity'], 'amount' => $u['amount'], 'update_time' => $now]);
        }
        $toDelete = array_diff(array_keys($existingMap), array_column($toUpdate, 'id'));
        if (!empty($toDelete)) {
            CustomerOrderItemModel::where('tenant_id', $tenantId)
                ->where('customer_order_id', $customerOrderId)
                ->whereIn('id', $toDelete)
                ->delete();
        }
        CustomerOrderModel::where('id', $customerOrderId)->update([
            'total_amount' => $newTotal,
            'update_time' => $now,
        ]);
        return $this->success('已保存');
    }

    public function orders(): Response
    {
        $tenantId = $this->getTenantId();
        $customerId = $this->getCustomerId();
        if ($customerId <= 0) {
            return $this->error('未识别客户', 0);
        }
        // 若 token 未带租户（如部分环境），用客户所属租户兜底
        if ($tenantId <= 0) {
            $customer = CustomerModel::where('id', $customerId)->find();
            if ($customer) {
                $tenantId = (int) $customer->tenant_id;
            }
        }
        if ($tenantId <= 0) {
            return $this->error('未识别租户', 0);
        }

        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $status = $this->request->get('status');

        $query = CustomerOrderModel::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->order('id', 'desc');
        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $orders = $query->page($page, $limit)->select();

        // 已关联到客户订单的内部订单 id（避免重复展示）
        $linkedInternalIds = CustomerOrderModel::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->where('internal_order_id', '>', 0)
            ->column('internal_order_id');
        $linkedInternalIds = array_filter(array_map('intval', $linkedInternalIds));

        // 始终拉取「仅内部订单」：后台为该客户建的单（未走客户下单）与客户订单一起展示
        $internalOnlyList = [];
        $internalOnlyQuery = OrderModel::with(['orderModels.model.product'])
            ->where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->order('id', 'desc')
            ->limit((int) $limit);
        if (!empty($linkedInternalIds)) {
            $internalOnlyQuery->whereNotIn('id', $linkedInternalIds);
        }
        $internalOnlyOrders = $internalOnlyQuery->select();
        foreach ($internalOnlyOrders as $io) {
            $internalOnlyList[] = [
                'order' => $io,
                'synthetic_id' => -(int) $io->id,
                'customer_order_no' => (string) ($io->order_no ?? ''),
                'create_time' => (int) ($io->create_time ?? 0),
            ];
        }
        $total += count($internalOnlyList);

        $orderIds = [];
        $internalIds = [];
        foreach ($orders as $o) {
            $orderIds[] = (int) $o->id;
            $iid = (int) ($o->internal_order_id ?? 0);
            if ($iid > 0) {
                $internalIds[] = $iid;
            }
        }
        foreach ($internalOnlyList as $ent) {
            $internalIds[] = (int) $ent['order']->id;
        }
        $internalIds = array_values(array_unique(array_filter($internalIds)));

        $itemsMap = [];
        if (!empty($orderIds)) {
            $items = CustomerOrderItemModel::with(['customerProduct.model.product'])
                ->where('tenant_id', $tenantId)
                ->whereIn('customer_order_id', $orderIds)
                ->select();
            foreach ($items as $item) {
                $oid = (int) $item->customer_order_id;
                if (!isset($itemsMap[$oid])) {
                    $itemsMap[$oid] = [];
                }
                $cp = $item->customerProduct;
                $displayName = '';
                if ($cp && $cp->model) {
                    $productName = $cp->model->product ? (string) $cp->model->product->name : '';
                    $modelName = (string) $cp->model->name;
                    $modelCode = (string) $cp->model->model_code;
                    $displayName = $productName !== '' ? ($productName . ' - ' . $modelName) : $modelName;
                    if ($modelCode !== '') {
                        $displayName .= ' (' . $modelCode . ')';
                    }
                }
                $itemsMap[$oid][] = [
                    'id' => (int) $item->id,
                    'customer_product_id' => (int) $item->customer_product_id,
                    'product_id' => (int) $item->product_id,
                    'model_id' => (int) $item->model_id,
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                    'amount' => (float) $item->amount,
                    'name' => $displayName,
                ];
            }
        }

        $internalIds = array_values(array_unique(array_filter($internalIds)));
        $internalMap = [];
        $shipmentMap = [];
        $progressMap = [];
        if (!empty($internalIds)) {
            $allocations = AllocationModel::where('tenant_id', $tenantId)
                ->whereIn('order_id', $internalIds)
                ->field('order_id,model_id,quantity,completed_quantity')
                ->select();
            foreach ($allocations as $a) {
                $oid = (int) $a->order_id;
                $mid = (int) $a->model_id;
                if (!isset($progressMap[$oid])) {
                    $progressMap[$oid] = [];
                }
                if (!isset($progressMap[$oid][$mid])) {
                    $progressMap[$oid][$mid] = ['quantity' => 0, 'completed' => 0];
                }
                $progressMap[$oid][$mid]['quantity'] += (int) $a->quantity;
                $progressMap[$oid][$mid]['completed'] += (int) ($a->completed_quantity ?? 0);
            }
        }
        if (!empty($internalIds)) {
            $internalOrders = OrderModel::where('tenant_id', $tenantId)
                ->whereIn('id', $internalIds)
                ->select();
            foreach ($internalOrders as $io) {
                $internalMap[(int) $io->id] = $io;
            }

            $shipments = ShipmentModel::where('tenant_id', $tenantId)
                ->whereIn('order_id', $internalIds)
                ->select();
            foreach ($shipments as $sp) {
                $oid = (int) $sp->order_id;
                if (!isset($shipmentMap[$oid])) {
                    $shipmentMap[$oid] = false;
                }
                if ((int) $sp->status >= 1) {
                    $shipmentMap[$oid] = true;
                }
            }
        }

        $list = [];
        foreach ($orders as $o) {
            $oid = (int) $o->id;
            $customerStatus = (int) $o->status;
            $internalOrderId = (int) ($o->internal_order_id ?? 0);
            if ($internalOrderId > 0 && isset($internalMap[$internalOrderId])) {
                $io = $internalMap[$internalOrderId];
                $ioStatus = (int) $io->status;
                if (!empty($shipmentMap[$internalOrderId])) {
                    $customerStatus = 3;
                } else {
                    if ($ioStatus === 0) {
                        $customerStatus = 1;
                    } elseif ($ioStatus === 1) {
                        $customerStatus = 2;
                    } elseif ($ioStatus === 2) {
                        $customerStatus = 4;
                    } elseif ($ioStatus === 3) {
                        $customerStatus = 5;
                    }
                }
            }

            $orderItems = $itemsMap[$oid] ?? [];
            foreach ($orderItems as &$it) {
                $mid = (int) ($it['model_id'] ?? 0);
                $it['progress_quantity'] = 0;
                $it['progress_completed'] = 0;
                if ($internalOrderId > 0 && isset($progressMap[$internalOrderId][$mid])) {
                    $it['progress_quantity'] = $progressMap[$internalOrderId][$mid]['quantity'];
                    $it['progress_completed'] = $progressMap[$internalOrderId][$mid]['completed'];
                }
            }
            unset($it);
            $list[] = [
                'id' => $oid,
                'customer_order_no' => (string) $o->customer_order_no,
                'status' => $customerStatus,
                'total_amount' => (float) $o->total_amount,
                'currency' => (string) $o->currency,
                'remark' => (string) $o->remark,
                'create_time' => (int) $o->create_time,
                'update_time' => (int) $o->update_time,
                'internal_order_id' => $internalOrderId,
                'items' => $orderItems,
            ];
        }

        // 仅内部订单（后台为该客户创建）：无客户订单时已查，这里拼成与上面一致的结构
        foreach ($internalOnlyList as $ent) {
            $io = $ent['order'];
            $ioid = (int) $io->id;
            $ioStatus = (int) ($io->status ?? 0);
            $customerStatus = 1;
            if (!empty($shipmentMap[$ioid])) {
                $customerStatus = 3;
            } else {
                if ($ioStatus === 0) {
                    $customerStatus = 1;
                } elseif ($ioStatus === 1) {
                    $customerStatus = 2;
                } elseif ($ioStatus === 2) {
                    $customerStatus = 4;
                } elseif ($ioStatus === 3) {
                    $customerStatus = 5;
                }
            }
            $orderItems = [];
            foreach ($io->orderModels ?? [] as $om) {
                $mid = (int) ($om->model_id ?? 0);
                $displayName = '';
                if ($om->model) {
                    $productName = $om->model->product ? (string) $om->model->product->name : '';
                    $modelName = (string) ($om->model->name ?? '');
                    $modelCode = (string) ($om->model->model_code ?? '');
                    $displayName = $productName !== '' ? ($productName . ' - ' . $modelName) : $modelName;
                    if ($modelCode !== '') {
                        $displayName .= ' (' . $modelCode . ')';
                    }
                }
                $qty = (int) ($om->quantity ?? 0);
                $orderItems[] = [
                    'id' => 0,
                    'customer_product_id' => 0,
                    'product_id' => (int) ($om->model->product_id ?? 0),
                    'model_id' => $mid,
                    'quantity' => $qty,
                    'price' => 0,
                    'amount' => 0,
                    'name' => $displayName,
                    'progress_quantity' => isset($progressMap[$ioid][$mid]) ? $progressMap[$ioid][$mid]['quantity'] : 0,
                    'progress_completed' => isset($progressMap[$ioid][$mid]) ? $progressMap[$ioid][$mid]['completed'] : 0,
                ];
            }
            $list[] = [
                'id' => $ent['synthetic_id'],
                'customer_order_no' => $ent['customer_order_no'],
                'status' => $customerStatus,
                'total_amount' => (float) ($io->total_amount ?? 0),
                'currency' => 'CNY',
                'remark' => (string) ($io->remark ?? ''),
                'create_time' => $ent['create_time'],
                'update_time' => (int) ($io->update_time ?? 0),
                'internal_order_id' => $ioid,
                'items' => $orderItems,
            ];
        }

        return $this->success('', ['total' => $total, 'list' => $list]);
    }
}
