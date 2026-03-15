<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\BomModel;
use app\admin\model\mes\BomItemModel;
use app\admin\model\mes\ProductModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\MaterialModel;
use app\admin\model\mes\MaterialCategoryModel;
use app\admin\model\mes\SupplierModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * BOM物料清单管理
 * 
 * @icon fa fa-sitemap
 * @remark 管理产品物料清单，支持多层级BOM结构
 */
class Bom extends Backend
{
    /**
     * BOM列表
     */
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', 'BOM管理');
            return $this->fetchWithLayout('mes/bom/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $bomNo = trim((string) $this->request->get('bom_no'));
        $status = $this->request->get('status');
        $bomType = $this->request->get('bom_type');

        $tenantId = $this->getTenantId();
        $query = BomModel::with(['product', 'model'])
            ->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }

        if ($bomNo !== '') {
            $query->where('bom_no', 'like', '%' . $bomNo . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        if ($bomType !== '' && $bomType !== null) {
            $query->where('bom_type', (int) $bomType);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['bom_type'] = isset($item['bom_type']) ? (int) $item['bom_type'] : 0;
            if (empty($item['model_id']) || (isset($item['model']) && empty($item['model']['name']))) {
                $item['model'] = $item['model'] ?? [];
                $item['model']['name'] = '通用（默认）';
            }
            if ($item['bom_type'] === 1) {
                $item['product'] = $item['product'] ?? [];
                $item['product']['name'] = '通用模板';
                $item['model'] = $item['model'] ?? [];
                $item['model']['name'] = '-';
            }
        }
        unset($item);

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 添加BOM
     */
    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $tenantId = $this->getTenantId();
            $params['tenant_id'] = $tenantId;
            $params['bom_type'] = isset($params['bom_type']) ? (int) $params['bom_type'] : 0;
            
            if (empty($params['bom_no'])) {
                $params['bom_no'] = BomModel::generateBomNo();
            }
            
            $params['creator_id'] = $this->auth->id ?? 0;
            $params['creator_name'] = $this->auth->username ?? '';
            
            // 填充默认值，避免数据库 NOT NULL 约束导致失败
            $params['bom_name'] = $params['bom_name'] ?? '未命名BOM';
            $params['approver_name'] = $params['approver_name'] ?? '';

            if ($params['bom_type'] === 1) {
                // 通用模板：不绑定产品/型号
                $params['product_id'] = 0;
                $params['model_id'] = 0;
            } else {
                $params['product_id'] = (int) ($params['product_id'] ?? 0);
                $params['model_id'] = (int) ($params['model_id'] ?? 0);
                // 产品BOM：model_id=0 表示同产品通用，必须选择产品
                if ($params['model_id'] === 0) {
                    if (empty($params['product_id'])) {
                        return $this->error('选择通用（默认）型号时请先选择产品');
                    }
                } else {
                    if (empty($params['product_id'])) {
                        $model = ProductModelModel::where('tenant_id', $tenantId)->find($params['model_id']);
                        if ($model) {
                            $params['product_id'] = $model->product_id;
                        }
                    }
                }
            }

            try {
                $bom = BomModel::create($params);
                return $this->success('添加成功', ['id' => $bom->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败');
            }
        }

        // 获取产品列表
        $tenantId = $this->getTenantId();
        $productList = ProductModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        View::assign('productList', $productList);

        $modelList = [];
        $models = ProductModelModel::with('product')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->select();
        if ($models->isEmpty()) {
            $models = ProductModelModel::with('product')
                ->where('status', 1)
                ->select();
        }
        foreach ($models as $model) {
            $productName = $model->product->name ?? '';
            $modelName = $model->name ?? '';
            $modelCode = $model->model_code ?? '';
            $displayName = $productName ? ($productName . ' - ' . $modelName) : $modelName;
            if ($modelCode) {
                $displayName .= ' (' . $modelCode . ')';
            }
            $modelList[$model->id] = $displayName;
        }
        $modelList = [0 => '通用（默认）'] + $modelList;
        View::assign('modelList', $modelList);
        View::assign('bomTypeList', (new BomModel())->getBomTypeList());

        View::assign('title', '添加BOM');
        return $this->fetchWithLayout('mes/bom/add');
    }

    /**
     * 编辑BOM
     */
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
        $row = BomModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('BOM不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            try {
                if (isset($params['bom_type'])) {
                    $params['bom_type'] = (int) $params['bom_type'];
                }
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败');
            }
        }

        // 获取产品列表
        $productList = ProductModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        View::assign('productList', $productList);

        $modelList = [];
        $models = ProductModelModel::with('product')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->select();
        if ($models->isEmpty()) {
            $models = ProductModelModel::with('product')
                ->where('status', 1)
                ->select();
        }
        foreach ($models as $model) {
            $productName = $model->product->name ?? '';
            $modelName = $model->name ?? '';
            $modelCode = $model->model_code ?? '';
            $displayName = $productName ? ($productName . ' - ' . $modelName) : $modelName;
            if ($modelCode) {
                $displayName .= ' (' . $modelCode . ')';
            }
            $modelList[$model->id] = $displayName;
        }
        $modelList = [0 => '通用（默认）'] + $modelList;
        View::assign('modelList', $modelList);
        View::assign('bomTypeList', (new BomModel())->getBomTypeList());

        View::assign('row', $row);
        View::assign('title', '编辑BOM');
        return $this->fetchWithLayout('mes/bom/edit');
    }

    /**
     * 从通用模板导入 BOM 明细（会覆盖当前 BOM 原有明细）
     */
    public function importTemplateItems(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $bomId = (int) $this->request->post('bom_id', 0);
        $templateBomId = (int) $this->request->post('template_bom_id', 0);
        if ($bomId <= 0 || $templateBomId <= 0) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $target = BomModel::where('tenant_id', $tenantId)->find($bomId);
        if (!$target) {
            return $this->error('目标BOM不存在');
        }
        $template = BomModel::where('tenant_id', $tenantId)->where('id', $templateBomId)->where('bom_type', 1)->find();
        if (!$template) {
            return $this->error('模板BOM不存在');
        }

        $items = BomItemModel::where('tenant_id', $tenantId)->where('bom_id', $templateBomId)
            ->order('level', 'asc')->order('sequence', 'asc')->select()->toArray();
        if (empty($items)) {
            return $this->error('模板没有明细');
        }

        Db::startTrans();
        try {
            BomItemModel::where('tenant_id', $tenantId)->where('bom_id', $bomId)->delete();

            $idMap = [];
            foreach ($items as $it) {
                $oldId = (int) ($it['id'] ?? 0);
                unset($it['id']);
                $it['bom_id'] = $bomId;
                $it['create_time'] = time();
                $it['parent_id'] = 0;
                $new = BomItemModel::create($it);
                if ($oldId > 0) {
                    $idMap[$oldId] = (int) $new->id;
                }
            }
            foreach ($items as $it) {
                $oldId = (int) ($it['id'] ?? 0);
                $oldParent = (int) ($it['parent_id'] ?? 0);
                if ($oldId > 0 && $oldParent > 0 && isset($idMap[$oldId]) && isset($idMap[$oldParent])) {
                    BomItemModel::where('tenant_id', $tenantId)->where('id', $idMap[$oldId])->update(['parent_id' => $idMap[$oldParent]]);
                }
            }

            Db::commit();
            return $this->success('导入成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('导入失败');
        }
    }

    /**
     * 删除BOM
     */
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
        
        Db::startTrans();
        try {
            foreach ($idsArr as $id) {
                $bom = BomModel::where('tenant_id', $tenantId)->find($id);
                if (!$bom) {
                    continue;
                }

                // 删除BOM明细
                BomItemModel::where('tenant_id', $tenantId)
                    ->where('bom_id', $id)
                    ->delete();

                $bom->delete();
            }

            Db::commit();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败');
        }
    }

    /**
     * BOM明细管理
     */
    public function items(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            $ids = $this->request->param('id');
        }
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $bom = BomModel::where('tenant_id', $tenantId)->find($ids);
        if (!$bom) {
            return $this->error('BOM不存在');
        }

        if ($this->request->isAjax()) {
            $items = BomItemModel::where('tenant_id', $tenantId)
                ->where('bom_id', $ids)
                ->with(['material', 'supplier'])
                ->order('level', 'asc')
                ->order('sequence', 'asc')
                ->select();

            return $this->success('', ['total' => count($items), 'list' => $items]);
        }

        // 物料分类（用于按分类筛选）
        $categoryList = MaterialCategoryModel::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->whereOr('tenant_id', 0);
        })->where('status', 1)->order('sort', 'asc')->column('name', 'id');
        View::assign('categoryList', $categoryList ?: []);
        View::assign('categoryListJson', json_encode($categoryList ?: [], JSON_UNESCAPED_UNICODE));

        // 获取物料列表（含 category_id 便于前端按分类筛选）
        $materialList = MaterialModel::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->column('name', 'id');
        View::assign('materialList', $materialList);
        View::assign('materialListJson', json_encode($materialList ?: [], JSON_UNESCAPED_UNICODE));
        $materialListWithCategory = MaterialModel::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->field('id,name,category_id')
            ->order('category_id', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();
        View::assign('materialListWithCategory', $materialListWithCategory);
        View::assign('materialListWithCategoryJson', json_encode($materialListWithCategory ?: [], JSON_UNESCAPED_UNICODE));
        // 获取供应商列表
        $supplierList = SupplierModel::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->column('name', 'id');
        View::assign('supplierList', $supplierList);
        View::assign('supplierListJson', json_encode($supplierList ?: [], JSON_UNESCAPED_UNICODE));

        // 通用模板列表（仅已发布）
        $templateBomList = [];
        $tpls = BomModel::where('tenant_id', $tenantId)->where('bom_type', 1)->where('status', 2)->order('id', 'desc')->select();
        foreach ($tpls as $tpl) {
            $name = $tpl->bom_name ?: $tpl->bom_no;
            $templateBomList[$tpl->id] = $name . '（' . $tpl->bom_no . '）';
        }
        View::assign('templateBomList', $templateBomList);

        View::assign('bom', $bom);
        View::assign('title', 'BOM明细管理');
        return $this->fetchWithLayout('mes/bom/items');
    }

    /**
     * 添加BOM明细
     */
    public function addItem(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $params = $this->request->post();
        if (empty($params)) {
            return $this->error('参数不能为空');
        }

        $tenantId = $this->getTenantId();
        $params['tenant_id'] = $tenantId;

        try {
            $bomItem = BomItemModel::create($params);
            return $this->success('添加成功', ['id' => $bomItem->id]);
        } catch (\Exception $e) {
            return $this->error('添加失败');
        }
    }

    /**
     * 批量添加BOM明细
     */
    public function addItemBatch(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $items = $this->request->post('items/a');
        if (empty($items) || !is_array($items)) {
            return $this->error('请至少添加一行物料');
        }
        $tenantId = $this->getTenantId();
        $bomId = (int) $this->request->post('bom_id', 0);
        if ($bomId <= 0) {
            return $this->error('参数错误');
        }
        $bom = BomModel::where('tenant_id', $tenantId)->find($bomId);
        if (!$bom) {
            return $this->error('BOM不存在');
        }
        $valid = [];
        foreach ($items as $row) {
            $materialId = (int) ($row['material_id'] ?? 0);
            $qty = isset($row['quantity']) ? (float) $row['quantity'] : 0;
            if ($materialId <= 0 || $qty <= 0) {
                continue;
            }
            $valid[] = [
                'tenant_id'   => $tenantId,
                'bom_id'      => $bomId,
                'parent_id'   => 0,
                'material_id' => $materialId,
                'quantity'    => $qty,
                'loss_rate'   => isset($row['loss_rate']) ? (float) $row['loss_rate'] : 0,
                'unit_price'  => 0,
                'supplier_id' => (int) ($row['supplier_id'] ?? 0),
                'level'       => (int) ($row['level'] ?? 1),
                'sequence'    => (int) ($row['sequence'] ?? 0),
                'create_time' => time(),
            ];
        }
        if (empty($valid)) {
            return $this->error('请至少填写一行有效的物料与用量');
        }
        try {
            foreach ($valid as $v) {
                BomItemModel::create($v);
            }
            return $this->success('批量添加成功', ['count' => count($valid)]);
        } catch (\Exception $e) {
            return $this->error('批量添加失败');
        }
    }

    /**
     * 更新BOM明细
     */
    public function updateItem(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $params = $this->request->post();
        $id = $params['id'] ?? 0;
        
        if (empty($id)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $bomItem = BomItemModel::where('tenant_id', $tenantId)->find($id);
        if (!$bomItem) {
            return $this->error('BOM明细不存在');
        }

        try {
            unset($params['id']);
            $bomItem->save($params);
            return $this->success('更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败');
        }
    }

    /**
     * 删除BOM明细
     */
    public function deleteItem(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $id = $this->request->post('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $bomItem = BomItemModel::where('tenant_id', $tenantId)->find($id);
        if (!$bomItem) {
            return $this->error('BOM明细不存在');
        }

        try {
            $bomItem->delete();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败');
        }
    }

    /**
     * 审核BOM
     */
    public function approve(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $ids = $this->request->post('ids');
        $approve = $this->request->post('approve', 1);
        $remark = $this->request->post('remark', '');

        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $bom = BomModel::where('tenant_id', $tenantId)->find($ids);
        if (!$bom) {
            return $this->error('BOM不存在');
        }

        try {
            if ($approve == 1) {
                $bom->status = 2; // 已发布
                $bom->approver_id = $this->auth->id ?? 0;
                $bom->approver_name = $this->auth->username ?? '';
                $bom->approve_time = time();
                $bom->publish_time = time();
            } else {
                $bom->status = 0; // 退回草稿
            }
            $bom->save();

            return $this->success($approve == 1 ? '审核通过' : '已退回');
        } catch (\Exception $e) {
            return $this->error('操作失败');
        }
    }
}
