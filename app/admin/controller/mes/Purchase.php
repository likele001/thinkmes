<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\PurchaseRequestModel;
use app\admin\model\mes\PurchaseInModel;
use app\admin\model\mes\PurchaseInboundModel;
use app\admin\model\mes\PurchaseInboundItemModel;
use app\admin\model\mes\StockLogModel;
use app\admin\model\mes\SupplierModel;
use app\admin\model\mes\MaterialModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 采购管理
 *
 * @icon fa fa-shopping-cart
 * @remark 管理采购申请和入库
 */
class Purchase extends Backend
{
    /**
     * 采购申请列表
     */
    public function requestList(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '采购申请管理');
            return $this->fetchWithLayout('mes/purchase/request');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = PurchaseRequestModel::with(['material', 'supplier', 'order'])
            ->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }

        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            if (isset($row['create_time']) && (is_string($row['create_time']) || is_int($row['create_time']))) {
                $row['create_time'] = (int) $row['create_time'];
            }
        }
        unset($row);

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 审核采购申请
     */
    public function auditRequest(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $ids = $this->request->post('ids');
        $status = $this->request->post('status'); // 1通过 2驳回
        $remark = $this->request->post('remark', '');

        if (empty($ids) || !in_array($status, ['1', '2'])) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : explode(',', $ids);

        try {
            foreach ($idsArr as $id) {
                $request = PurchaseRequestModel::where('tenant_id', $tenantId)->find($id);
                if (!$request) {
                    continue;
                }

                if ($status == 1) {
                    $request->status = 1;
                    if ($request->order_material_id) {
                        $om = \app\admin\model\mes\OrderMaterialModel::where('tenant_id', $tenantId)
                            ->find($request->order_material_id);
                        if ($om) {
                            $om->purchase_status = 1;
                            $om->save();
                        }
                    }
                } else {
                    $request->status = 3;
                    if ($request->order_material_id) {
                        $om = \app\admin\model\mes\OrderMaterialModel::where('tenant_id', $tenantId)
                            ->find($request->order_material_id);
                        if ($om) {
                            $om->purchase_status = 0;
                            $om->save();
                        }
                    }
                }
                $request->save();
            }

            return $this->success('操作成功');
        } catch (\Exception $e) {
            return $this->error('操作失败');
        }
    }

    /**
     * 按库存预警批量创建采购申请（缺货物料 → 勾选 → 创建申请，无订单）
     */
    public function createRequestFromStockAlert(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('请使用 POST 提交');
        }
        $materialIds = $this->request->post('material_ids');
        if (empty($materialIds)) {
            return $this->error('请选择要申请采购的物料');
        }
        $idsArr = is_array($materialIds) ? $materialIds : array_filter(explode(',', (string) $materialIds));
        $quantities = $this->request->post('quantities'); // 可选：{"12":100,"51":200} 物料id=>数量
        if (is_string($quantities)) {
            $quantities = json_decode($quantities, true) ?: [];
        }
        $tenantId = $this->getTenantId();
        $created = 0;
        $skipped = [];
        foreach ($idsArr as $mid) {
            $materialId = (int) $mid;
            if ($materialId <= 0) {
                continue;
            }
            $material = MaterialModel::where('tenant_id', $tenantId)->find($materialId);
            if (!$material) {
                $skipped[] = "物料#{$materialId}不存在";
                continue;
            }
            $stock = (float) ($material->stock ?? 0);
            $minStock = (float) ($material->min_stock ?? 0);
            if ($minStock <= 0) {
                $skipped[] = $material->name . '：未设安全库存';
                continue;
            }
            $shortage = isset($quantities[$materialId]) ? (float) $quantities[$materialId] : max(0, $minStock - $stock);
            if ($shortage <= 0) {
                $skipped[] = $material->name . '：无需补货';
                continue;
            }
            $supplierId = (int) ($material->default_supplier_id ?? 0);
            $price = (float) ($material->current_price ?? 0);
            $amount = $shortage * $price;
            $exists = PurchaseRequestModel::where('tenant_id', $tenantId)
                ->where('material_id', $materialId)
                ->where('order_id', 0)
                ->where('order_material_id', 0)
                ->whereIn('status', [0, 1])
                ->find();
            if ($exists) {
                $skipped[] = $material->name . '：已有未完成申请';
                continue;
            }
            PurchaseRequestModel::create([
                'tenant_id'          => $tenantId,
                'request_no'        => PurchaseRequestModel::generateRequestNo(),
                'material_id'       => $materialId,
                'order_id'          => 0,
                'order_material_id' => 0,
                'required_quantity' => $shortage,
                'estimated_price'   => $price,
                'estimated_amount'  => $amount,
                'supplier_id'       => $supplierId,
                'status'            => 0,
                'create_time'       => time(),
                'update_time'       => time(),
            ]);
            $created++;
        }
        $msg = '已创建 ' . $created . ' 条采购申请';
        if (!empty($skipped)) {
            $msg .= '；跳过：' . implode('、', array_slice($skipped, 0, 5));
            if (count($skipped) > 5) {
                $msg .= ' 等' . count($skipped) . '条';
            }
        }
        return $this->success($msg, ['created' => $created]);
    }

    /**
     * 采购入库列表（report 流程：主表+明细，待入库/已入库）
     */
    public function inbound(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '采购入库管理');
            return $this->fetchWithLayout('mes/purchase/inbound');
        }

        $tenantId = $this->getTenantId();
        if (!$this->hasInboundTable()) {
            return $this->success('', ['total' => 0, 'list' => []]);
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $query = PurchaseInboundModel::with(['supplier'])->where('tenant_id', $tenantId)->order('id', 'desc');
        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        $statusList = (new PurchaseInboundModel())->getStatusList();
        foreach ($list as &$row) {
            $row['status_text'] = $statusList[$row['status'] ?? 1] ?? '';
            $row['item_count'] = (int) PurchaseInboundItemModel::where('inbound_id', $row['id'])->count();
            if (empty($row['inbound_no'])) {
                $row['inbound_no'] = '单#' . ($row['id'] ?? '');
            }
        }
        unset($row);

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 从采购申请生成入库单（report 流程：选已审核申请 → 按供应商生成入库单+明细，申请状态→已采购）
     */
    public function generateFromRequest(): string|Response
    {
        if ($this->request->isPost()) {
            $requestIds = $this->request->post('request_ids');
            if (empty($requestIds)) {
                return $this->error('请选择要生成入库单的采购申请');
            }
            $tenantId = $this->getTenantId();
            if (!$this->hasInboundTable()) {
                return $this->error('请先执行数据库迁移：database/migrate_purchase_inbound_like_report.sql');
            }

            $idsArr = is_array($requestIds) ? $requestIds : (is_string($requestIds) ? array_filter(explode(',', $requestIds)) : []);
            if (empty($idsArr)) {
                return $this->error('请选择采购申请');
            }

            $requests = PurchaseRequestModel::with(['material', 'supplier'])
                ->where('tenant_id', $tenantId)
                ->whereIn('id', $idsArr)
                ->where('status', 1) // 已审核
                ->select();
            if ($requests->isEmpty()) {
                return $this->error('没有找到已审核的采购申请');
            }

            $bySupplier = [];
            foreach ($requests as $req) {
                $sid = (int) $req->supplier_id;
                if (!isset($bySupplier[$sid])) {
                    $bySupplier[$sid] = [];
                }
                $bySupplier[$sid][] = $req;
            }

            Db::startTrans();
            try {
                $inboundIds = [];
                foreach ($bySupplier as $supplierId => $reqList) {
                    $inbound = PurchaseInboundModel::create([
                        'tenant_id'     => $tenantId,
                        'inbound_no'    => PurchaseInboundModel::generateInboundNo(),
                        'supplier_id'   => $supplierId,
                        'inbound_date'  => time(),
                        'total_amount'  => 0,
                        'status'        => 1, // 待入库
                        'warehouse_id'   => 1,
                        'create_time'   => time(),
                        'update_time'   => time(),
                    ]);
                    $inboundIds[] = $inbound->id;
                    $totalAmount = 0;
                    foreach ($reqList as $req) {
                        $qty = (float) ($req->required_quantity ?? 0);
                        $price = (float) ($req->estimated_price ?? 0);
                        $amount = $qty * $price;
                        PurchaseInboundItemModel::create([
                            'tenant_id'            => $tenantId,
                            'inbound_id'           => $inbound->id,
                            'purchase_request_id'  => $req->id,
                            'material_id'          => $req->material_id,
                            'request_quantity'     => $qty,
                            'actual_quantity'      => $qty,
                            'unit_price'           => $price,
                            'total_amount'         => $amount,
                            'quality_status'       => 1,
                            'create_time'          => time(),
                            'update_time'          => time(),
                        ]);
                        $totalAmount += $amount;
                        $req->status = 2; // 已采购
                        $req->save();
                        if ($req->order_material_id) {
                            $om = \app\admin\model\mes\OrderMaterialModel::where('tenant_id', $tenantId)->find($req->order_material_id);
                            if ($om) {
                                $om->purchase_status = 2;
                                $om->save();
                            }
                        }
                    }
                    $inbound->total_amount = $totalAmount;
                    $inbound->save();
                }
                Db::commit();
                return $this->success('入库单生成成功', ['inbound_ids' => $inboundIds]);
            } catch (\Throwable $e) {
                Db::rollback();
                return $this->error('生成失败：' . $e->getMessage());
            }
        }

        $tenantId = $this->getTenantId();
        $requests = PurchaseRequestModel::with(['material', 'supplier'])
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->order('id', 'desc')
            ->select()
            ->toArray();
        View::assign('requests', $requests);
        View::assign('title', '从采购申请生成入库单');
        return $this->fetchWithLayout('mes/purchase/generate_from_request');
    }

    /**
     * 添加入库单
     */
    public function addInbound(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $tenantId = $this->getTenantId();
            $params['tenant_id'] = $tenantId;
            $params['in_no'] = PurchaseInModel::generateInNo();
            $params['operator_id'] = $this->auth->id ?? 0;

            // 处理入库时间
            if (!empty($params['in_time'])) {
                $params['in_time'] = strtotime($params['in_time']);
            }

            Db::startTrans();
            try {
                $inbound = PurchaseInModel::create($params);

                // 更新物料库存
                $material = MaterialModel::where('tenant_id', $tenantId)->find($params['material_id']);
                if ($material) {
                    $beforeQty = (float)$material->stock;
                    $material->stock += $params['in_quantity'];
                    $material->save();

                    // 记录库存流水（仅记流水，库存已在上方更新）
                    StockLogModel::log(
                        $tenantId,
                        (int) $params['material_id'],
                        (float) $params['in_quantity'],
                        'purchase_in',
                        (int) $inbound->id,
                        (int) ($params['operator_id'] ?? 0),
                        '采购入库：' . ($params['in_no'] ?? ''),
                        $beforeQty,
                        $beforeQty + (float) $params['in_quantity']
                    );
                }

                if (!empty($params['purchase_request_id'])) {
                    $request = PurchaseRequestModel::where('tenant_id', $tenantId)
                        ->find($params['purchase_request_id']);
                    if ($request) {
                        $request->status = 2;
                        $request->save();

                        if ($request->order_material_id) {
                            $om = \app\admin\model\mes\OrderMaterialModel::where('tenant_id', $tenantId)
                                ->find($request->order_material_id);
                            if ($om) {
                                $om->purchase_status = 2;
                                $om->stock_status = 0;
                                $om->save();
                            }
                        }
                    }
                }

                Db::commit();
                return $this->success('入库成功', ['id' => $inbound->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('入库失败');
            }
        }

        $tenantId = $this->getTenantId();
        // 获取已审核的采购申请（可关联入库）
        $requestList = PurchaseRequestModel::with(['material', 'supplier'])
            ->where('tenant_id', $tenantId)
            ->where('status', 1) // 已审核
            ->select()
            ->toArray();
        View::assign('requestList', $requestList);

        // 获取物料列表
        $materialList = MaterialModel::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->column('name', 'id');
        View::assign('materialList', $materialList ?: []);

        // 从采购申请列表点「入库」带过来的 request_id，预填关联申请、物料、数量
        $preselectRequestId = (int) $this->request->get('request_id', 0);
        $preselectMaterialId = 0;
        $preselectQuantity = 1;
        if ($preselectRequestId > 0) {
            foreach ($requestList as $req) {
                if ((int) ($req['id'] ?? 0) === $preselectRequestId) {
                    $preselectMaterialId = (int) ($req['material_id'] ?? 0);
                    $preselectQuantity = (float) ($req['required_quantity'] ?? 1);
                    if ($preselectQuantity <= 0) {
                        $preselectQuantity = 1;
                    }
                    break;
                }
            }
        }
        View::assign('preselect_request_id', $preselectRequestId);
        View::assign('preselect_material_id', $preselectMaterialId);
        View::assign('preselect_quantity', $preselectQuantity);

        View::assign('title', '添加入库单');
        return $this->fetchWithLayout('mes/purchase/add_inbound');
    }

    /**
     * 编辑入库单
     */
    public function editInbound(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = PurchaseInModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('入库单不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败');
            }
        }

        View::assign('row', $row);
        View::assign('ids', $ids);
        View::assign('title', '编辑入库单');
        return $this->fetchWithLayout('mes/purchase/edit_inbound');
    }

    /**
     * 确认入库（report 流程：按明细加库存、记流水，入库单状态→已入库）
     */
    public function confirmInbound(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            $ids = $this->request->post('id');
        }
        if (empty($ids)) {
            return $this->error('请选择要确认的入库单');
        }
        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : array_filter(explode(',', (string) $ids));

        if (!$this->hasInboundTable()) {
            return $this->error('入库表未就绪');
        }

        $inbounds = PurchaseInboundModel::with(['items'])->where('tenant_id', $tenantId)->whereIn('id', $idsArr)->where('status', 1)->select();
        if ($inbounds->isEmpty()) {
            return $this->error('没有找到待入库的单据');
        }

        Db::startTrans();
        try {
            $operatorId = (int) ($this->auth->id ?? 0);
            foreach ($inbounds as $inbound) {
                foreach ($inbound->items as $item) {
                    $material = MaterialModel::where('tenant_id', $tenantId)->find($item->material_id);
                    if ($material) {
                        $beforeQty = (float) $material->stock;
                        $qty = (float) $item->actual_quantity;
                        $afterQty = $beforeQty + $qty;
                        $material->stock = $afterQty;
                        $material->save();
                        StockLogModel::log(
                            $tenantId,
                            (int) $item->material_id,
                            $qty,
                            'purchase_in',
                            (int) $inbound->id,
                            $operatorId,
                            '采购入库：' . $inbound->inbound_no,
                            $beforeQty,
                            $afterQty
                        );
                    }
                    if ($item->purchase_request_id) {
                        $req = PurchaseRequestModel::where('tenant_id', $tenantId)->find($item->purchase_request_id);
                        if ($req && $req->order_material_id) {
                            $om = \app\admin\model\mes\OrderMaterialModel::where('tenant_id', $tenantId)->find($req->order_material_id);
                            if ($om) {
                                $om->stock_status = 0; // 已备料
                                $om->save();
                            }
                        }
                    }
                }
                $inbound->status = 2; // 已入库
                $inbound->inbound_user_id = $operatorId;
                $inbound->inbound_date = time();
                $inbound->update_time = time();
                $inbound->save();
            }
            Db::commit();
            return $this->success('确认入库成功');
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->error('确认失败：' . $e->getMessage());
        }
    }

    /**
     * 查看入库单明细
     */
    public function viewInboundItems($id = null): string|Response
    {
        $id = $id ?: $this->request->param('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $inbound = PurchaseInboundModel::with(['supplier', 'items.material'])->where('tenant_id', $tenantId)->find($id);
        if (!$inbound) {
            return $this->error('入库单不存在');
        }
        $row = $inbound->toArray();
        $row['items'] = $inbound->items ? $inbound->items->toArray() : [];
        foreach ($row['items'] as &$it) {
            $it['material_name'] = $it['material']['name'] ?? '-';
        }
        unset($it);
        View::assign('row', $row);
        View::assign('title', '入库单明细');
        return $this->fetchWithLayout('mes/purchase/view_inbound_items');
    }

    /**
     * 根据供应商ID获取物料列表
     */
    public function getMaterials(): Response
    {
        $supplierId = $this->request->get('supplier_id');
        if (empty($supplierId)) {
            return $this->success('', []);
        }

        $tenantId = $this->getTenantId();
        // 这里可以根据实际业务逻辑获取该供应商的物料列表
        // 暂时返回所有活跃的物料
        $materials = MaterialModel::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->field('id, name')
            ->select()
            ->toArray();

        return $this->success('', $materials);
    }

    /**
     * 保存入库单
     */
    public function saveInbound(): Response
    {
        return $this->addInbound();
    }

    /**
     * 判断采购入库单主表是否存在（查 information_schema，与配置前缀一致）
     */
    private function hasInboundTable(): bool
    {
        try {
            $conn = config('database.connections.mysql');
            $prefix = $conn['prefix'] ?? '';
            $dbName = $conn['database'] ?? '';
            $candidates = [($prefix ?: 'fa_') . 'mes_purchase_inbound'];
            if ($prefix !== 'fa_') {
                $candidates[] = 'fa_mes_purchase_inbound';
            }
            foreach ($candidates as $tableName) {
                if ($dbName !== '') {
                    $row = Db::query(
                        'SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1',
                        [$dbName, $tableName]
                    );
                    if (!empty($row)) {
                        return true;
                    }
                } else {
                    $tables = Db::query('SHOW TABLES LIKE ?', [$tableName]);
                    if (!empty($tables)) {
                        return true;
                    }
                }
            }
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
