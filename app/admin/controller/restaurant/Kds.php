<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\admin\model\restaurant\ItemModel;
use app\admin\model\restaurant\KdsEventModel;
use app\admin\model\restaurant\OrderItemModel;
use app\admin\model\restaurant\OrderModel;
use app\admin\model\restaurant\TableModel;
use think\facade\View;
use think\Response;

class Kds extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '后厨KDS');
            return $this->fetchWithLayout('restaurant/kds/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = OrderModel::order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tp = (int) $this->request->get('tenant_id', 0);
            if ($tp > 0) {
                $query->where('tenant_id', $tp);
            }
        }
        $query->where('status', '<', 4);

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        $tableIds = [];
        foreach ($list as $row) {
            $tableIds[] = (int) ($row['table_id'] ?? 0);
        }
        $tableMap = [];
        if ($tableIds) {
            $rows = TableModel::where('tenant_id', $tenantId)->whereIn('id', array_unique($tableIds))->select()->toArray();
            foreach ($rows as $t) {
                $tableMap[(int) $t['id']] = $t;
            }
        }
        $now = time();
        foreach ($list as &$row) {
            $tid = (int) ($row['table_id'] ?? 0);
            $row['table_name'] = $tableMap[$tid]['name'] ?? ('#' . $tid);
            $row['age_seconds'] = $now - (int) ($row['create_time'] ?? 0);
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function call(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $id = (int) $this->request->post('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $order = OrderModel::where('tenant_id', $tenantId)->find($id);
        if (!$order) {
            return $this->error('订单不存在');
        }
        try {
            KdsEventModel::create([
                'tenant_id' => $tenantId,
                'store_id' => (int) $order->store_id,
                'order_id' => (int) $order->id,
                'event_type' => 'call',
                'payload' => json_encode(['operator_id' => (int) ($this->auth->id ?? 0)], JSON_UNESCAPED_UNICODE),
                'create_time' => time(),
            ]);
            return $this->success('已叫号');
        } catch (\Throwable $e) {
            return $this->error('叫号失败');
        }
    }

    public function setSoldOut(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $itemId = (int) $this->request->post('item_id', 0);
        $soldOut = (int) $this->request->post('sold_out', 0);
        if ($itemId <= 0) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $item = ItemModel::where('tenant_id', $tenantId)->find($itemId);
        if (!$item) {
            return $this->error('菜品不存在');
        }
        $item->save(['sold_out' => $soldOut ? 1 : 0, 'update_time' => time()]);
        return $this->success('已更新');
    }

    public function items(): Response
    {
        $tenantId = $this->getTenantId();
        $orderId = (int) $this->request->get('id', 0);
        if ($orderId <= 0) {
            return $this->error('参数错误');
        }
        $order = OrderModel::where('tenant_id', $tenantId)->find($orderId);
        if (!$order) {
            return $this->error('订单不存在');
        }
        $items = OrderItemModel::with(['item'])
            ->where('tenant_id', $tenantId)
            ->where('order_id', $orderId)
            ->order('id', 'asc')
            ->select();
        $list = [];
        foreach ($items as $it) {
            $snap = $it->option_snapshot ? json_decode((string) $it->option_snapshot, true) : null;
            $name = (string) ($it->name_snapshot ?: ($it->item ? $it->item->name : ''));
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
                'id' => (int) $it->id,
                'name' => $name,
                'options_text' => $optionsText,
                'quantity' => (float) $it->quantity,
            ];
        }
        return $this->success('', ['list' => $list]);
    }
}
