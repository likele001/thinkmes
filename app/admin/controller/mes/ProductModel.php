<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\ProductModel as Product;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 产品型号管理
 */
class ProductModel extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '产品型号管理');
            return $this->fetchWithLayout('mes/product_model/index');
        }
        
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $tenantId = $this->getTenantId();
        $query = ProductModelModel::with('product')
            ->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }
        
        // 搜索条件
        $search = trim((string) $this->request->get('search'));
        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->whereOr('model_code', 'like', '%' . $search . '%');
            });
        }
        
        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        
        // 格式化显示名称
        foreach ($list as &$row) {
            $productName = $row['product']['name'] ?? '';
            $modelName = $row['name'] ?? '';
            $modelCode = $row['model_code'] ?? '';
            $row['full_name'] = $productName ? ($productName . ' - ' . $modelName) : $modelName;
            if ($modelCode) {
                $row['full_name'] .= ' (' . $modelCode . ')';
            }
        }
        
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            
            $tenantId = $this->getTenantId();
            $params['tenant_id'] = $tenantId;
            $params['create_time'] = time();
            $params['update_time'] = time();
            
            try {
                $model = ProductModelModel::create($params);
                return $this->success('添加成功', ['id' => $model->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败');
            }
        }
        
        // 获取产品列表
        $tenantId = $this->getTenantId();
        $productList = Product::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        View::assign('productList', $productList ?: []);
        View::assign('title', '添加产品型号');
        return $this->fetchWithLayout('mes/product_model/add');
    }

    public function edit(): string|Response
    {
        $id = (int) $this->request->get('id');
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            
            $tenantId = $this->getTenantId();
            $model = ProductModelModel::where('tenant_id', $tenantId)->find($id);
            if (!$model) {
                return $this->error('记录不存在');
            }
            
            $params['update_time'] = time();
            try {
                $model->save($params);
                return $this->success('保存成功', ['id' => $model->id]);
            } catch (\Exception $e) {
                return $this->error('保存失败');
            }
        }
        
        $tenantId = $this->getTenantId();
        $data = ProductModelModel::where('tenant_id', $tenantId)->find($id);
        if (!$data) {
            return $this->error('记录不存在');
        }
        
        // 获取产品列表
        $productList = Product::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        View::assign('productList', $productList ?: []);
        View::assign('data', $data->toArray());
        View::assign('title', '编辑产品型号');
        return $this->fetchWithLayout('mes/product_model/edit');
    }

    public function del(): Response
    {
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('请选择要删除的记录');
        }
        
        $tenantId = $this->getTenantId();
        $ids = is_array($ids) ? $ids : explode(',', (string) $ids);
        
        try {
            ProductModelModel::where('tenant_id', $tenantId)
                ->whereIn('id', $ids)
                ->delete();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败');
        }
    }
}
