<?php
declare(strict_types=1);

namespace app\api\controller\restaurant;

use app\common\controller\BaseController;
use app\admin\model\restaurant\CartModel;
use app\admin\model\restaurant\ComboModel;
use app\admin\model\restaurant\ItemModel;
use app\admin\model\restaurant\OrderItemModel;
use app\admin\model\restaurant\OrderModel;
use app\admin\model\restaurant\TableModel;
use think\facade\Db;
use think\Response;

class Order extends BaseController
{
    protected function tenantId(): int
    {
        $tenantId = (int) ($this->request->tenantId ?? 0);
        if ($tenantId <= 0) {
            $tenantId = (int) $this->request->param('tenant_id', 0);
        }
        return $tenantId;
    }

    protected function tableByToken(int $tenantId, string $token): ?TableModel
    {
        return TableModel::where('tenant_id', $tenantId)->where('qr_token', $token)->where('status', 1)->find();
    }

    public function create(): Response
    {
        $tenantId = $this->tenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $token = trim((string) $this->request->post('token', ''));
        $remark = trim((string) $this->request->post('remark', ''));
        if ($token === '') {
            return $this->error('缺少 token');
        }
        $table = $this->tableByToken($tenantId, $token);
        if (!$table) {
            return $this->error('桌台不存在或已禁用');
        }

        $cartRows = CartModel::with(['item'])
            ->where('tenant_id', $tenantId)
            ->where('table_id', (int) $table->id)
            ->select();
        if ($cartRows->isEmpty()) {
            return $this->error('购物车为空');
        }

        $now = time();
        Db::startTrans();
        try {
            $orderNo = OrderModel::generateOrderNo();
            $total = 0.0;
            foreach ($cartRows as $r) {
                $qty = (float) $r->quantity;
                if ($qty <= 0) continue;
                $productType = (string) ($r->product_type ?? 'item');
                if ($productType === 'combo') {
                    $combo = ComboModel::where('tenant_id', $tenantId)->where('id', (int) $r->combo_id)->where('status', 1)->find();
                    if (!$combo || (int) $combo->sold_out === 1) {
                        throw new \RuntimeException('存在已下架/售罄套餐');
                    }
                } else {
                    $item = $r->item;
                    if (!$item || (int) $item->status !== 1 || (int) $item->sold_out === 1) {
                        throw new \RuntimeException('存在已下架/售罄菜品');
                    }
                }
                $total += (float) $r->line_amount;
            }
            if ($total <= 0) {
                throw new \RuntimeException('金额错误');
            }

            $order = OrderModel::create([
                'tenant_id' => $tenantId,
                'store_id' => (int) $table->store_id,
                'table_id' => (int) $table->id,
                'order_no' => $orderNo,
                'status' => 0,
                'total_amount' => $total,
                'remark' => $remark,
                'create_time' => $now,
                'update_time' => $now,
            ]);

            foreach ($cartRows as $r) {
                $qty = (float) $r->quantity;
                if ($qty <= 0) {
                    continue;
                }
                $productType = (string) ($r->product_type ?? 'item');
                $price = (float) $r->unit_price;
                $amount = (float) $r->line_amount;
                $optionKey = (string) ($r->option_key ?? '');
                $name = '';
                if ($productType === 'combo') {
                    $combo = ComboModel::where('tenant_id', $tenantId)->find((int) $r->combo_id);
                    $name = $combo ? (string) $combo->name : '';
                } else {
                    $item = $r->item;
                    $name = $item ? (string) $item->name : '';
                }
                OrderItemModel::create([
                    'tenant_id' => $tenantId,
                    'order_id' => (int) $order->id,
                    'item_id' => (int) $r->item_id,
                    'product_type' => $productType,
                    'combo_id' => (int) $r->combo_id,
                    'option_key' => $optionKey,
                    'name_snapshot' => $name,
                    'option_snapshot' => (string) ($r->option_snapshot ?? ''),
                    'price' => $price,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'amount' => $amount,
                    'line_amount' => $amount,
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
            }

            CartModel::where('tenant_id', $tenantId)->where('table_id', (int) $table->id)->delete();
            Db::commit();
            return $this->success('下单成功', ['order_id' => (int) $order->id, 'order_no' => $orderNo, 'total_amount' => $total]);
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('下单失败');
        }
    }

    public function list(): Response
    {
        $tenantId = $this->tenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $token = trim((string) $this->request->get('token', ''));
        if ($token === '') {
            return $this->error('缺少 token');
        }
        $table = $this->tableByToken($tenantId, $token);
        if (!$table) {
            return $this->error('桌台不存在或已禁用');
        }

        $rows = OrderModel::where('tenant_id', $tenantId)
            ->where('table_id', (int) $table->id)
            ->order('id', 'desc')
            ->limit(50)
            ->select()
            ->toArray();
        return $this->success('', ['list' => $rows]);
    }

    public function detail(): Response
    {
        $tenantId = $this->tenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $token = trim((string) $this->request->get('token', ''));
        $orderId = (int) $this->request->get('id', 0);
        if ($token === '' || $orderId <= 0) {
            return $this->error('参数错误');
        }
        $table = $this->tableByToken($tenantId, $token);
        if (!$table) {
            return $this->error('桌台不存在或已禁用');
        }

        $order = OrderModel::where('tenant_id', $tenantId)->where('table_id', (int) $table->id)->find($orderId);
        if (!$order) {
            return $this->error('订单不存在');
        }

        $items = OrderItemModel::with(['item'])
            ->where('tenant_id', $tenantId)
            ->where('order_id', (int) $order->id)
            ->select();
        $list = [];
        foreach ($items as $it) {
            $item = $it->item;
            $snap = $it->option_snapshot ? json_decode((string) $it->option_snapshot, true) : null;
            $name = (string) ($it->name_snapshot ?: ($item ? $item->name : ''));
            $optionsText = '';
            if (is_array($snap) && isset($snap['type']) && $snap['type'] === 'combo' && isset($snap['items']) && is_array($snap['items'])) {
                $parts = [];
                foreach ($snap['items'] as $ci) {
                    $n = (string) ($ci['name'] ?? '');
                    $q = (float) ($ci['quantity'] ?? 1);
                    if ($n === '') continue;
                    $parts[] = $n . '*' . rtrim(rtrim(number_format($q, 2, '.', ''), '0'), '.');
                }
                if ($parts) $optionsText = '含：' . implode('，', $parts);
            } elseif (is_array($snap) && isset($snap['options']) && is_array($snap['options'])) {
                $names = [];
                foreach ($snap['options'] as $o) {
                    if (!empty($o['name'])) $names[] = (string) $o['name'];
                }
                if ($names) $optionsText = implode(' / ', $names);
            }
            $list[] = [
                'item_id' => (int) $it->item_id,
                'product_type' => (string) ($it->product_type ?? 'item'),
                'combo_id' => (int) ($it->combo_id ?? 0),
                'name' => $name,
                'price' => (float) $it->price,
                'quantity' => (float) $it->quantity,
                'amount' => (float) $it->amount,
                'option_snapshot' => $snap,
                'options_text' => $optionsText,
            ];
        }

        return $this->success('', [
            'order' => $order->toArray(),
            'items' => $list,
        ]);
    }
}
