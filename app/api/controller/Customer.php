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

    public function login(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }

        $account = trim((string) $this->request->post('login_account', ''));
        $password = (string) $this->request->post('password', '');

        if ($account === '' || $password === '') {
            return $this->error('请输入账号和密码');
        }
        if (strlen($password) < 6 || strlen($password) > 64) {
            return $this->error('密码长度需在 6-64 位之间');
        }

        $customer = CustomerModel::where('tenant_id', $tenantId)
            ->where('login_account', $account)
            ->where('status', 1)
            ->find();
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

        $itemsRaw = $this->request->post('items');
        $items = [];
        if (is_string($itemsRaw) && $itemsRaw !== '') {
            $decoded = json_decode($itemsRaw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $items = $decoded;
            }
        } elseif (is_array($itemsRaw)) {
            $items = $itemsRaw;
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
                if (!isset($modelQtyMap[$mid])) {
                    $modelQtyMap[$mid] = 0;
                }
                $modelQtyMap[$mid] += $qty;
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

            $internalParams = [
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
                'total_amount' => $totalAmount,
            ];

            $internalOrder = OrderModel::create($internalParams);

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
                OrderModel::where('id', (int) $internalOrder->id)
                    ->update(['total_quantity' => $totalQuantity]);
            }

            CustomerOrderModel::where('id', (int) $customerOrder->id)
                ->update([
                    'internal_order_id' => (int) $internalOrder->id,
                    'status' => 0,
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

    public function orders(): Response
    {
        $tenantId = $this->getTenantId();
        $customerId = $this->getCustomerId();
        if ($tenantId <= 0 || $customerId <= 0) {
            return $this->error('未识别租户或客户', 0);
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

        $orderIds = [];
        $internalIds = [];
        foreach ($orders as $o) {
            $orderIds[] = (int) $o->id;
            $iid = (int) ($o->internal_order_id ?? 0);
            if ($iid > 0) {
                $internalIds[] = $iid;
            }
        }

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

            $list[] = [
                'id' => $oid,
                'customer_order_no' => (string) $o->customer_order_no,
                'status' => $customerStatus,
                'total_amount' => (float) $o->total_amount,
                'currency' => (string) $o->currency,
                'remark' => (string) $o->remark,
                'create_time' => (int) $o->create_time,
                'update_time' => (int) $o->update_time,
                'items' => $itemsMap[$oid] ?? [],
            ];
        }

        return $this->success('', ['total' => $total, 'list' => $list]);
    }
}
