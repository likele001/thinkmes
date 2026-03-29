<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\admin\model\restaurant\OrderModel;
use app\admin\model\restaurant\OrderItemModel;
use app\admin\model\restaurant\TableModel;
use think\facade\View;
use think\Response;

class Order extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '餐饮订单');
            return $this->fetchWithLayout('restaurant/order/index');
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

        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $orderNo = trim((string) $this->request->get('order_no', ''));
        if ($orderNo !== '') {
            $query->where('order_no', 'like', '%' . $orderNo . '%');
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function detail(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = OrderModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('订单不存在');
        }
        $items = OrderItemModel::with(['item'])->where('tenant_id', $tenantId)->where('order_id', (int) $row->id)->select();
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
                'options_text' => $optionsText,
                'price' => (float) $it->price,
                'quantity' => (float) $it->quantity,
                'amount' => (float) $it->amount,
            ];
        }
        $table = TableModel::where('tenant_id', $tenantId)->find((int) $row->table_id);
        View::assign('row', $row);
        View::assign('table', $table);
        View::assign('items', $list);
        View::assign('title', '订单详情');
        return $this->fetchWithLayout('restaurant/order/detail');
    }

    public function updateStatus(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $id = (int) $this->request->post('id', 0);
        $status = (int) $this->request->post('status', -1);
        if ($id <= 0 || $status < 0) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = OrderModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('订单不存在');
        }
        $row->save(['status' => $status, 'update_time' => time()]);
        return $this->success('已更新状态');
    }
}
