<?php
declare(strict_types=1);

namespace app\admin\controller\crm;

use app\admin\controller\Backend;
use app\admin\model\crm\SalesOrderModel;
use app\admin\model\crm\SalesOrderItemModel;
use app\admin\model\crm\CustomerModel;
use app\admin\model\crm\ContractModel;
use app\admin\model\crm\ProductModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * CRM 销售订单管理
 */
class SalesOrder extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $mesInstalled = $this->isMesInstalled();
            View::assign('mesInstalled', $mesInstalled);
            View::assign('title', '销售订单');
            return $this->fetchWithLayout('crm/sales_order/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $customerId = (int) $this->request->get('customer_id', 0);
        $status = trim((string) $this->request->get('status'));

        $tenantId = $this->getTenantId();
        $query = SalesOrderModel::order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        }
        if ($customerId > 0) {
            $query->where('customer_id', $customerId);
        }
        if ($status !== '') {
            $query->where('status', $status);
        }

        $total = $query->count();
        $rows = $query->page($page, $limit)->select();

        $list = [];
        foreach ($rows as $row) {
            $arr = $row->toArray();
            if ($row->customer_id > 0) {
                $customer = CustomerModel::find($row->customer_id);
                $arr['customer_name'] = $customer ? $customer->name : '';
            }
            $list[] = $arr;
        }

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $items = $this->request->post('items/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $tenantId = $this->getTenantId();
            $customerId = (int) ($params['customer_id'] ?? 0);

            if ($customerId <= 0) {
                return $this->error('请选择客户');
            }

            // 生成订单号
            $orderNo = $params['order_no'] ?? '';
            if ($orderNo === '') {
                $orderNo = 'SO' . date('YmdHis') . sprintf('%04d', mt_rand(1, 9999));
            }
            $exists = SalesOrderModel::where('tenant_id', $tenantId)->where('order_no', $orderNo)->find();
            if ($exists) {
                $orderNo = 'SO' . date('YmdHis') . sprintf('%04d', mt_rand(1, 9999));
            }

            $params['tenant_id'] = $tenantId;
            $params['order_no'] = $orderNo;
            $params['status'] = $params['status'] ?? 'draft';
            if (isset($params['delivery_date']) && $params['delivery_date'] !== '') {
                $params['delivery_date'] = strtotime($params['delivery_date']);
            }

            if (!empty($items)) {
                $totalAmount = 0;
                foreach ($items as $it) {
                    $qty = (int) ($it['quantity'] ?? 0);
                    $price = (float) ($it['price'] ?? 0);
                    $totalAmount += $qty * $price;
                }
                $params['total_amount'] = $totalAmount;
            }

            Db::startTrans();
            try {
                $order = SalesOrderModel::create($params);
                if (!empty($items)) {
                    foreach ($items as $it) {
                        $productId = (int) ($it['product_id'] ?? 0);
                        $productName = trim((string) ($it['product_name'] ?? ''));
                        $productCode = trim((string) ($it['product_code'] ?? ''));
                        $quantity = (int) ($it['quantity'] ?? 0);
                        $unit = trim((string) ($it['unit'] ?? '个'));
                        $price = (float) ($it['price'] ?? 0);
                        $amount = $quantity * $price;
                        if ($quantity <= 0) continue;
                        SalesOrderItemModel::create([
                            'tenant_id' => $tenantId,
                            'sales_order_id' => $order->id,
                            'product_id' => $productId,
                            'product_name' => $productName,
                            'product_code' => $productCode,
                            'quantity' => $quantity,
                            'unit' => $unit,
                            'price' => $price,
                            'amount' => $amount,
                        ]);
                    }
                }
                Db::commit();
                return $this->success('添加成功', ['id' => $order->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        $tenantId = $this->getTenantId();
        $customers = CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->select()->toArray();
        $contracts = ContractModel::where('tenant_id', $tenantId)->select()->toArray();
        foreach ($contracts as &$c) {
            $c['customer_name'] = '';
            if (!empty($c['customer_id'])) {
                $customer = CustomerModel::find($c['customer_id']);
                $c['customer_name'] = $customer ? $customer->name : '';
            }
        }
        unset($c);
        $products = ProductModel::where('tenant_id', $tenantId)->where('status', 1)->select()->toArray();

        View::assign('customers', $customers);
        View::assign('contracts', $contracts);
        View::assign('products', $products);
        View::assign('title', '添加销售订单');
        return $this->fetchWithLayout('crm/sales_order/add');
    }

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
        $row = SalesOrderModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('订单不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $items = $this->request->post('items/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            if (isset($params['delivery_date']) && $params['delivery_date'] !== '') {
                $params['delivery_date'] = strtotime($params['delivery_date']);
            }

            if (!empty($items)) {
                $totalAmount = 0;
                foreach ($items as $it) {
                    $qty = (int) ($it['quantity'] ?? 0);
                    $price = (float) ($it['price'] ?? 0);
                    $totalAmount += $qty * $price;
                }
                $params['total_amount'] = $totalAmount;
            }

            Db::startTrans();
            try {
                $row->save($params);
                SalesOrderItemModel::where('tenant_id', $tenantId)->where('sales_order_id', $row->id)->delete();
                if (!empty($items)) {
                    foreach ($items as $it) {
                        $quantity = (int) ($it['quantity'] ?? 0);
                        if ($quantity <= 0) continue;
                        $price = (float) ($it['price'] ?? 0);
                        $amount = $quantity * $price;
                        SalesOrderItemModel::create([
                            'tenant_id' => $tenantId,
                            'sales_order_id' => $row->id,
                            'product_id' => (int) ($it['product_id'] ?? 0),
                            'product_name' => trim((string) ($it['product_name'] ?? '')),
                            'product_code' => trim((string) ($it['product_code'] ?? '')),
                            'quantity' => $quantity,
                            'unit' => trim((string) ($it['unit'] ?? '个')),
                            'price' => $price,
                            'amount' => $amount,
                        ]);
                    }
                }
                Db::commit();
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        $items = SalesOrderItemModel::where('tenant_id', $tenantId)->where('sales_order_id', $row->id)->select()->toArray();
        $tenantId = $this->getTenantId();
        $customers = CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->select()->toArray();
        $contracts = ContractModel::where('tenant_id', $tenantId)->select()->toArray();
        foreach ($contracts as &$c) {
            $c['customer_name'] = '';
            if (!empty($c['customer_id'])) {
                $customer = CustomerModel::find($c['customer_id']);
                $c['customer_name'] = $customer ? $customer->name : '';
            }
        }
        unset($c);
        $products = ProductModel::where('tenant_id', $tenantId)->where('status', 1)->select()->toArray();

        View::assign('customers', $customers);
        View::assign('contracts', $contracts);
        View::assign('products', $products);
        View::assign('row', $row);
        View::assign('items', $items);
        View::assign('mesInstalled', $this->isMesInstalled());
        View::assign('title', '编辑销售订单');
        return $this->fetchWithLayout('crm/sales_order/edit');
    }

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
        
        try {
            foreach ($idsArr as $id) {
                $order = SalesOrderModel::where('tenant_id', $tenantId)->find($id);
                if ($order) {
                    SalesOrderItemModel::where('tenant_id', $tenantId)->where('sales_order_id', $id)->delete();
                    $order->delete();
                }
            }
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 转 MES 生产订单（需 MES 已安装）
     */
    public function toMes(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $id = (int) $this->request->post('id');
        if ($id <= 0) {
            return $this->error('参数错误');
        }

        if (!$this->isMesInstalled()) {
            return $this->error('MES 未安装，无法转生产订单');
        }

        $tenantId = $this->getTenantId();
        $order = SalesOrderModel::where('tenant_id', $tenantId)->find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }
        if ($order->mes_order_id > 0) {
            return $this->error('该订单已转生产订单');
        }

        $items = SalesOrderItemModel::where('tenant_id', $tenantId)->where('sales_order_id', $id)->select();
        if ($items->isEmpty()) {
            return $this->error('订单无明细，无法转生产');
        }

        $customer = CustomerModel::find($order->customer_id);
        $customerName = $customer ? $customer->name : '';
        $prefix = (string) (Db::connect()->getConfig()['prefix'] ?? 'fa_');

        Db::startTrans();
        try {
            $mesOrderNo = 'MO' . date('YmdHis') . sprintf('%04d', mt_rand(1, 9999));
            Db::execute("INSERT INTO `{$prefix}mes_order` (`tenant_id`,`order_no`,`order_name`,`customer_id`,`customer_name`,`total_quantity`,`status`,`delivery_time`,`remark`,`create_time`,`update_time`) VALUES (?,?,?,?,?,?,0,?,?,?,?)", [
                $tenantId,
                $mesOrderNo,
                $order->order_no . ' - ' . $customerName,
                $order->customer_id,
                $customerName,
                $items->sum('quantity'),
                $order->delivery_date,
                'CRM销售订单转生产',
                time(),
                time(),
            ]);
            $mesOrderId = (int) Db::getLastInsID();

            foreach ($items as $it) {
                Db::execute("INSERT INTO `{$prefix}mes_order_model` (`tenant_id`,`order_id`,`model_name`,`quantity`,`remark`,`create_time`,`update_time`) VALUES (?,?,?,?,?,?,?)", [
                    $tenantId,
                    $mesOrderId,
                    $it->product_name ?: ($it->product_code ?: '产品' . $it->product_id),
                    $it->quantity,
                    '',
                    time(),
                    time(),
                ]);
            }

            $order->mes_order_id = $mesOrderId;
            $order->status = 'producing';
            $order->save();

            Db::commit();
            return $this->success('已转生产订单，订单号：' . $mesOrderNo);
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('转生产失败：' . $e->getMessage());
        }
    }

    protected function isMesInstalled(): bool
    {
        try {
            $prefix = (string) (Db::connect()->getConfig()['prefix'] ?? 'fa_');
            $r = Db::query("SHOW TABLES LIKE '" . addslashes($prefix . 'mes_order') . "'");
            return !empty($r);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
