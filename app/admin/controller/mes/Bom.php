<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\BomModel;
use app\admin\model\mes\BomItemModel;
use app\admin\model\mes\ProductModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\MaterialModel;
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

        $tenantId = $this->getTenantId();
        $query = BomModel::with(['product', 'model'])
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc');

        if ($bomNo !== '') {
            $query->where('bom_no', 'like', '%' . $bomNo . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

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
            
            if (empty($params['bom_no'])) {
                $params['bom_no'] = BomModel::generateBomNo();
            }
            
            $params['creator_id'] = $this->auth->id ?? 0;
            $params['creator_name'] = $this->auth->username ?? '';

            try {
                $bom = BomModel::create($params);
                return $this->success('添加成功', ['id' => $bom->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        // 获取产品列表
        $tenantId = $this->getTenantId();
        $productList = ProductModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        View::assign('productList', $productList);

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
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        // 获取产品列表
        $productList = ProductModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        View::assign('productList', $productList);

        View::assign('row', $row);
        View::assign('title', '编辑BOM');
        return $this->fetchWithLayout('mes/bom/edit');
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
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * BOM明细管理
     */
    public function items(): string|Response
    {
        $ids = $this->request->param('ids');
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

        // 获取物料列表
        $materialList = MaterialModel::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->column('name', 'id');
        View::assign('materialList', $materialList);
        
        // 获取供应商列表
        $supplierList = SupplierModel::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->column('name', 'id');
        View::assign('supplierList', $supplierList);

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
            return $this->error('添加失败：' . $e->getMessage());
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
            return $this->error('更新失败：' . $e->getMessage());
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
            return $this->error('删除失败：' . $e->getMessage());
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
            return $this->error('操作失败：' . $e->getMessage());
        }
    }
}
