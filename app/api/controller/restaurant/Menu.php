<?php
declare(strict_types=1);

namespace app\api\controller\restaurant;

use app\common\controller\BaseController;
use app\admin\model\restaurant\CategoryModel;
use app\admin\model\restaurant\ComboItemModel;
use app\admin\model\restaurant\ComboModel;
use app\admin\model\restaurant\ItemOptionGroupModel;
use app\admin\model\restaurant\ItemOptionModel;
use app\admin\model\restaurant\ItemModel;
use app\admin\model\restaurant\TableModel;
use think\Response;

class Menu extends BaseController
{
    protected function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    protected function resolveTenantId(): int
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) {
            $tenantId = (int) $this->request->get('tenant_id', 0);
        }
        return $tenantId;
    }

    protected function resolveTable(int $tenantId, string $token): ?TableModel
    {
        return TableModel::where('tenant_id', $tenantId)->where('qr_token', $token)->where('status', 1)->find();
    }

    public function index(): Response
    {
        $tenantId = $this->resolveTenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $token = trim((string) $this->request->get('token', ''));
        if ($token === '') {
            return $this->error('缺少 token');
        }
        $table = $this->resolveTable($tenantId, $token);
        if (!$table) {
            return $this->error('桌台不存在或已禁用');
        }

        $storeId = (int) $table->store_id;

        $categories = CategoryModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->where(function ($q) use ($storeId) { $q->where('store_id', 0)->whereOr('store_id', $storeId); })
            ->order('sort', 'desc')->order('id', 'desc')
            ->select()
            ->toArray();

        $items = ItemModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->where('sold_out', 0)
            ->where(function ($q) use ($storeId) { $q->where('store_id', 0)->whereOr('store_id', $storeId); })
            ->order('sort', 'desc')->order('id', 'desc')
            ->select()
            ->toArray();

        $itemIds = [];
        foreach ($items as $it) {
            $itemIds[] = (int) ($it['id'] ?? 0);
        }
        $itemIds = array_values(array_filter(array_unique($itemIds)));
        $groupsByItem = [];
        if ($itemIds) {
            $groups = ItemOptionGroupModel::where('tenant_id', $tenantId)
                ->where('status', 1)
                ->whereIn('item_id', $itemIds)
                ->order('sort', 'desc')->order('id', 'asc')
                ->select()
                ->toArray();
            $groupIds = [];
            foreach ($groups as $g) {
                $gid = (int) ($g['id'] ?? 0);
                if ($gid > 0) $groupIds[] = $gid;
            }
            $groupIds = array_values(array_filter(array_unique($groupIds)));
            $optionsByGroup = [];
            if ($groupIds) {
                $opts = ItemOptionModel::where('tenant_id', $tenantId)
                    ->where('status', 1)
                    ->whereIn('group_id', $groupIds)
                    ->order('sort', 'desc')->order('id', 'asc')
                    ->select()
                    ->toArray();
                foreach ($opts as $o) {
                    $gid = (int) ($o['group_id'] ?? 0);
                    if (!isset($optionsByGroup[$gid])) $optionsByGroup[$gid] = [];
                    $optionsByGroup[$gid][] = [
                        'id' => (int) ($o['id'] ?? 0),
                        'name' => (string) ($o['name'] ?? ''),
                        'price_delta' => (float) ($o['price_delta'] ?? 0),
                        'sort' => (int) ($o['sort'] ?? 0),
                    ];
                }
            }
            foreach ($groups as $g) {
                $iid = (int) ($g['item_id'] ?? 0);
                $gid = (int) ($g['id'] ?? 0);
                if ($iid <= 0 || $gid <= 0) continue;
                if (!isset($groupsByItem[$iid])) $groupsByItem[$iid] = [];
                $groupsByItem[$iid][] = [
                    'id' => $gid,
                    'name' => (string) ($g['name'] ?? ''),
                    'required' => (int) ($g['required'] ?? 0),
                    'min_select' => (int) ($g['min_select'] ?? 0),
                    'max_select' => (int) ($g['max_select'] ?? 1),
                    'sort' => (int) ($g['sort'] ?? 0),
                    'options' => $optionsByGroup[$gid] ?? [],
                ];
            }
        }

        $combos = ComboModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->where('sold_out', 0)
            ->where(function ($q) use ($storeId) { $q->where('store_id', 0)->whereOr('store_id', $storeId); })
            ->order('sort', 'desc')->order('id', 'desc')
            ->select()
            ->toArray();
        $comboIds = [];
        foreach ($combos as $c) {
            $comboIds[] = (int) ($c['id'] ?? 0);
        }
        $comboIds = array_values(array_filter(array_unique($comboIds)));
        $comboItemsByCombo = [];
        if ($comboIds) {
            $cis = ComboItemModel::with(['item'])
                ->where('tenant_id', $tenantId)
                ->whereIn('combo_id', $comboIds)
                ->select();
            foreach ($cis as $ci) {
                $cid = (int) $ci->combo_id;
                if (!isset($comboItemsByCombo[$cid])) $comboItemsByCombo[$cid] = [];
                $it = $ci->item;
                $comboItemsByCombo[$cid][] = [
                    'item_id' => (int) $ci->item_id,
                    'name' => (string) ($it ? $it->name : ''),
                    'quantity' => (float) $ci->quantity,
                ];
            }
        }
        $combosByCat = [];
        foreach ($combos as $c) {
            $cid = (int) ($c['category_id'] ?? 0);
            $id = (int) ($c['id'] ?? 0);
            if (!isset($combosByCat[$cid])) $combosByCat[$cid] = [];
            $combosByCat[$cid][] = [
                'id' => $id,
                'type' => 'combo',
                'name' => (string) ($c['name'] ?? ''),
                'price' => (float) ($c['price'] ?? 0),
                'items' => $comboItemsByCombo[$id] ?? [],
            ];
        }

        $itemsByCat = [];
        foreach ($items as $it) {
            $iid = (int) ($it['id'] ?? 0);
            if ($iid > 0) {
                $it['option_groups'] = $groupsByItem[$iid] ?? [];
            } else {
                $it['option_groups'] = [];
            }
            $cid = (int) ($it['category_id'] ?? 0);
            if (!isset($itemsByCat[$cid])) {
                $itemsByCat[$cid] = [];
            }
            $itemsByCat[$cid][] = $it;
        }

        $catList = [];
        foreach ($categories as $c) {
            $cid = (int) ($c['id'] ?? 0);
            $catList[] = [
                'id' => $cid,
                'name' => (string) ($c['name'] ?? ''),
                'sort' => (int) ($c['sort'] ?? 0),
                'items' => $itemsByCat[$cid] ?? [],
                'combos' => $combosByCat[$cid] ?? [],
            ];
        }
        if (isset($itemsByCat[0]) && $itemsByCat[0]) {
            $catList[] = ['id' => 0, 'name' => '未分类', 'sort' => -9999, 'items' => $itemsByCat[0], 'combos' => $combosByCat[0] ?? []];
        } elseif (isset($combosByCat[0]) && $combosByCat[0]) {
            $catList[] = ['id' => 0, 'name' => '未分类', 'sort' => -9999, 'items' => [], 'combos' => $combosByCat[0]];
        }

        return $this->success('', [
            'store_id' => $storeId,
            'table_id' => (int) $table->id,
            'categories' => $catList,
        ]);
    }
}
