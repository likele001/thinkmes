<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\OrderMaterialModel;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\MaterialModel;
use think\facade\View;
use think\Response;

/**
 * 简单MRP：按订单汇总物料需求，减当前库存得净需求（缺料）
 */
class Mrp extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '缺料计算(MRP)');
            return $this->fetchWithLayout('mes/mrp/index');
        }

        $tenantId = $this->getTenantId();
        $orderIds = $this->request->get('order_ids');
        if ($orderIds !== '' && $orderIds !== null) {
            $orderIdArr = array_map('intval', explode(',', $orderIds));
            $orderIdArr = array_filter($orderIdArr);
        } else {
            $query = OrderModel::whereIn('status', [0, 1]);
            if ($tenantId > 0) {
                $query->where('tenant_id', $tenantId);
            }
            $orderIdArr = $query->column('id');
        }

        $demandByMaterial = [];
        if (!empty($orderIdArr)) {
            $rows = OrderMaterialModel::with('material')
                ->whereIn('order_id', $orderIdArr)
                ->select();
            foreach ($rows as $row) {
                $mid = $row->material_id;
                if (!$mid) {
                    continue;
                }
                if (!isset($demandByMaterial[$mid])) {
                    $demandByMaterial[$mid] = [
                        'material_id'   => $mid,
                        'material_code' => $row->material->code ?? '',
                        'material_name' => $row->material->name ?? '',
                        'unit'          => $row->material->unit ?? '',
                        'required'      => 0,
                        'stock'         => (float) ($row->material->stock ?? 0),
                        'shortage'      => 0,
                    ];
                }
                $demandByMaterial[$mid]['required'] += (float) $row->required_quantity;
            }
        }

        foreach ($demandByMaterial as &$item) {
            $item['shortage'] = max(0, $item['required'] - $item['stock']);
        }
        unset($item);

        $list = array_values(array_filter($demandByMaterial, function ($v) {
            return $v['shortage'] > 0;
        }));

        return $this->success('', ['total' => count($list), 'list' => $list]);
    }
}
