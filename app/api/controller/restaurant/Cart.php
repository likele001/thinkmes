<?php
declare(strict_types=1);

namespace app\api\controller\restaurant;

use app\common\controller\BaseController;
use app\admin\model\restaurant\CartModel;
use app\admin\model\restaurant\ComboItemModel;
use app\admin\model\restaurant\ComboModel;
use app\admin\model\restaurant\ItemOptionGroupModel;
use app\admin\model\restaurant\ItemOptionModel;
use app\admin\model\restaurant\ItemModel;
use app\admin\model\restaurant\TableModel;
use think\facade\Db;
use think\Response;

class Cart extends BaseController
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

    private function calcItemWithOptions(int $tenantId, int $itemId, array $optionIds): array
    {
        $item = ItemModel::where('tenant_id', $tenantId)->where('id', $itemId)->where('status', 1)->find();
        if (!$item || (int) $item->sold_out === 1) {
            throw new \RuntimeException('菜品不存在或已售罄');
        }

        $basePrice = (float) $item->price;
        $optionIds = array_values(array_filter(array_map('intval', $optionIds)));
        $options = [];
        $delta = 0.0;
        if ($optionIds) {
            $rows = ItemOptionModel::with(['group'])
                ->where('tenant_id', $tenantId)
                ->where('status', 1)
                ->whereIn('id', $optionIds)
                ->select();
            foreach ($rows as $r) {
                $g = $r->group;
                if (!$g || (int) $g->item_id !== (int) $item->id) {
                    continue;
                }
                $options[] = [
                    'id' => (int) $r->id,
                    'name' => (string) $r->name,
                    'price_delta' => (float) $r->price_delta,
                    'group_id' => (int) $r->group_id,
                    'group_name' => (string) ($g ? $g->name : ''),
                ];
                $delta += (float) $r->price_delta;
            }
        }

        $validOptionIds = [];
        foreach ($options as $o) {
            $validOptionIds[] = (int) $o['id'];
        }
        sort($validOptionIds);
        $optionKey = $validOptionIds ? hash('sha256', implode(',', $validOptionIds)) : '';

        $unitPrice = $basePrice + $delta;
        return [
            'product_type' => 'item',
            'item' => $item,
            'unit_price' => $unitPrice,
            'option_key' => $optionKey,
            'option_snapshot' => [
                'type' => 'item',
                'item_id' => (int) $item->id,
                'base_price' => $basePrice,
                'options' => $options,
            ],
        ];
    }

    private function calcCombo(int $tenantId, int $comboId): array
    {
        $combo = ComboModel::where('tenant_id', $tenantId)->where('id', $comboId)->where('status', 1)->find();
        if (!$combo || (int) $combo->sold_out === 1) {
            throw new \RuntimeException('套餐不存在或已售罄');
        }
        $cis = ComboItemModel::with(['item'])
            ->where('tenant_id', $tenantId)
            ->where('combo_id', $comboId)
            ->select();
        $items = [];
        foreach ($cis as $ci) {
            $it = $ci->item;
            $items[] = [
                'item_id' => (int) $ci->item_id,
                'name' => (string) ($it ? $it->name : ''),
                'quantity' => (float) $ci->quantity,
            ];
        }
        return [
            'product_type' => 'combo',
            'combo' => $combo,
            'unit_price' => (float) $combo->price,
            'option_key' => '',
            'option_snapshot' => [
                'type' => 'combo',
                'combo_id' => (int) $combo->id,
                'items' => $items,
            ],
        ];
    }

    public function get(): Response
    {
        $tenantId = $this->tenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $token = trim((string) $this->request->param('token', ''));
        if ($token === '') {
            return $this->error('缺少 token');
        }
        $table = $this->tableByToken($tenantId, $token);
        if (!$table) {
            return $this->error('桌台不存在或已禁用');
        }

        $rows = CartModel::with(['item'])
            ->where('tenant_id', $tenantId)
            ->where('table_id', (int) $table->id)
            ->order('id', 'desc')
            ->select();

        $list = [];
        $total = 0.0;
        foreach ($rows as $r) {
            $item = $r->item;
            $price = (float) $r->unit_price;
            $qty = (float) $r->quantity;
            $amount = (float) $r->line_amount;
            $total += $amount;
            $snapArr = $r->option_snapshot ? json_decode((string) $r->option_snapshot, true) : null;
            $displayName = (string) ($item ? $item->name : '');
            if (is_array($snapArr) && isset($snapArr['type']) && $snapArr['type'] === 'combo') {
                $displayName = isset($snapArr['combo_id']) ? ('套餐#' . (int) $snapArr['combo_id']) : '套餐';
            } elseif (is_array($snapArr) && isset($snapArr['options']) && is_array($snapArr['options'])) {
                $names = [];
                foreach ($snapArr['options'] as $o) {
                    if (!empty($o['name'])) $names[] = (string) $o['name'];
                }
                if ($names) {
                    $displayName = $displayName . '（' . implode(' / ', $names) . '）';
                }
            }
            $list[] = [
                'id' => (int) $r->id,
                'product_type' => (string) ($r->product_type ?? 'item'),
                'item_id' => (int) $r->item_id,
                'combo_id' => (int) ($r->combo_id ?? 0),
                'name' => $displayName,
                'price' => $price,
                'quantity' => $qty,
                'amount' => $amount,
                'option_snapshot' => $snapArr,
            ];
        }

        return $this->success('', [
            'table_id' => (int) $table->id,
            'store_id' => (int) $table->store_id,
            'total_amount' => $total,
            'items' => $list,
        ]);
    }

    public function add(): Response
    {
        $tenantId = $this->tenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $token = trim((string) $this->request->post('token', ''));
        $productType = trim((string) $this->request->post('product_type', 'item'));
        $itemId = (int) $this->request->post('item_id', 0);
        $comboId = (int) $this->request->post('combo_id', 0);
        $quantity = (float) $this->request->post('quantity', 1);
        $optionIds = $this->request->post('option_ids/a', []);
        if ($token === '' || $itemId <= 0) {
            if ($productType !== 'combo' || $comboId <= 0) {
                return $this->error('参数错误');
            }
        }
        if ($quantity <= 0) {
            return $this->error('数量必须大于 0');
        }

        $table = $this->tableByToken($tenantId, $token);
        if (!$table) {
            return $this->error('桌台不存在或已禁用');
        }

        try {
            if ($productType === 'combo') {
                $calc = $this->calcCombo($tenantId, $comboId);
                $productType = 'combo';
                $itemId = 0;
            } else {
                $calc = $this->calcItemWithOptions($tenantId, $itemId, is_array($optionIds) ? $optionIds : []);
                $productType = 'item';
                $comboId = 0;
            }
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }

        $now = time();
        Db::startTrans();
        try {
            $row = CartModel::where('tenant_id', $tenantId)
                ->where('table_id', (int) $table->id)
                ->where('product_type', $productType)
                ->where('item_id', $itemId)
                ->where('combo_id', $comboId)
                ->where('option_key', (string) ($calc['option_key'] ?? ''))
                ->find();
            $unitPrice = (float) $calc['unit_price'];
            $optionKey = (string) ($calc['option_key'] ?? '');
            $snap = json_encode($calc['option_snapshot'], JSON_UNESCAPED_UNICODE);
            if ($row) {
                $newQty = (float) $row->quantity + $quantity;
                $row->save([
                    'quantity' => $newQty,
                    'unit_price' => $unitPrice,
                    'line_amount' => $unitPrice * $newQty,
                    'option_snapshot' => $snap,
                    'option_key' => $optionKey,
                    'update_time' => $now
                ]);
            } else {
                CartModel::create([
                    'tenant_id' => $tenantId,
                    'store_id' => (int) $table->store_id,
                    'table_id' => (int) $table->id,
                    'item_id' => $itemId,
                    'product_type' => $productType,
                    'combo_id' => $comboId,
                    'option_key' => $optionKey,
                    'option_snapshot' => $snap,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_amount' => $unitPrice * $quantity,
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
            }
            Db::commit();
            return $this->success('已加入购物车');
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('加入失败');
        }
    }

    public function update(): Response
    {
        $tenantId = $this->tenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $token = trim((string) $this->request->post('token', ''));
        $id = (int) $this->request->post('id', 0);
        $quantity = (float) $this->request->post('quantity', 0);
        if ($token === '' || $id <= 0) {
            return $this->error('参数错误');
        }
        if ($quantity < 0) {
            return $this->error('数量必须大于等于 0');
        }

        $table = $this->tableByToken($tenantId, $token);
        if (!$table) {
            return $this->error('桌台不存在或已禁用');
        }

        $row = CartModel::where('tenant_id', $tenantId)->where('table_id', (int) $table->id)->find($id);
        if (!$row) {
            return $this->error('购物车不存在该菜品');
        }

        if ($quantity == 0.0) {
            $row->delete();
            return $this->success('已移除');
        }
        $unitPrice = (float) $row->unit_price;
        $row->save(['quantity' => $quantity, 'line_amount' => $unitPrice * $quantity, 'update_time' => time()]);
        return $this->success('已更新');
    }

    public function remove(): Response
    {
        $tenantId = $this->tenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $token = trim((string) $this->request->post('token', ''));
        $id = (int) $this->request->post('id', 0);
        if ($token === '' || $id <= 0) {
            return $this->error('参数错误');
        }

        $table = $this->tableByToken($tenantId, $token);
        if (!$table) {
            return $this->error('桌台不存在或已禁用');
        }

        CartModel::where('tenant_id', $tenantId)->where('table_id', (int) $table->id)->where('id', $id)->delete();
        return $this->success('已移除');
    }

    public function clear(): Response
    {
        $tenantId = $this->tenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $token = trim((string) $this->request->post('token', ''));
        if ($token === '') {
            return $this->error('缺少 token');
        }
        $table = $this->tableByToken($tenantId, $token);
        if (!$table) {
            return $this->error('桌台不存在或已禁用');
        }
        CartModel::where('tenant_id', $tenantId)->where('table_id', (int) $table->id)->delete();
        return $this->success('已清空');
    }
}
