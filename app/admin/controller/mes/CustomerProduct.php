<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\CustomerProductModel;
use app\admin\model\mes\CustomerModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\ProductModel;
use think\facade\View;
use think\Response;

class CustomerProduct extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '客户产品配置');
            return $this->fetchWithLayout('mes/customer_product/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $customerId = (int) $this->request->get('customer_id', 0);
        $status = $this->request->get('status');

        $query = CustomerProductModel::with(['customer', 'model.product'])
            ->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }
        if ($customerId > 0) {
            $query->where('customer_id', $customerId);
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        foreach ($list as &$row) {
            $row['customer_name'] = $row['customer']['customer_name'] ?? '';
            $productName = $row['model']['product']['name'] ?? '';
            $modelName = $row['model']['name'] ?? '';
            $modelCode = $row['model']['model_code'] ?? '';
            $fullName = $productName ? ($productName . ' - ' . $modelName) : $modelName;
            if ($modelCode) {
                $fullName .= ' (' . $modelCode . ')';
            }
            $row['product_model_name'] = $fullName;
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
            $customerId = (int) ($params['customer_id'] ?? 0);
            $modelId = (int) ($params['model_id'] ?? 0);
            $price = (float) ($params['price'] ?? 0);
            $minQty = (int) ($params['min_qty'] ?? 1);

            if ($customerId <= 0 || $modelId <= 0) {
                return $this->error('请选择客户和产品型号');
            }
            if ($price <= 0) {
                return $this->error('请输入正确的单价');
            }
            if ($minQty <= 0) {
                $minQty = 1;
            }

            $customer = CustomerModel::where('tenant_id', $tenantId)
                ->where('id', $customerId)
                ->find();
            if (!$customer) {
                return $this->error('客户不存在');
            }

            $model = ProductModelModel::where('tenant_id', $tenantId)
                ->where('id', $modelId)
                ->find();
            if (!$model) {
                return $this->error('产品型号不存在');
            }

            $exists = CustomerProductModel::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->where('model_id', $modelId)
                ->find();
            if ($exists) {
                return $this->error('该客户已配置该产品型号');
            }

            $params['tenant_id'] = $tenantId;
            $params['customer_id'] = $customerId;
            $params['product_id'] = (int) $model->product_id;
            $params['model_id'] = $modelId;
            $params['price'] = $price;
            $params['min_qty'] = $minQty;
            if (!isset($params['currency']) || $params['currency'] === '') {
                $params['currency'] = 'CNY';
            }
            if (!isset($params['status']) || $params['status'] === '') {
                $params['status'] = 1;
            }
            $params['create_time'] = time();
            $params['update_time'] = time();

            try {
                $row = CustomerProductModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败');
            }
        }

        $tenantId = $this->getTenantId();
        $customerList = CustomerModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->order('id', 'desc')
            ->column('customer_name', 'id');

        $modelList = [];
        $models = ProductModelModel::with('product')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->select();
        foreach ($models as $model) {
            $productName = $model->product->name ?? '';
            $modelName = $model->name ?? '';
            $modelCode = $model->model_code ?? '';
            $fullName = $productName ? ($productName . ' - ' . $modelName) : $modelName;
            if ($modelCode) {
                $fullName .= ' (' . $modelCode . ')';
            }
            $modelList[$model->id] = $fullName;
        }

        View::assign('customerList', $customerList ?: []);
        View::assign('modelList', $modelList);
        View::assign('title', '添加客户产品配置');
        return $this->fetchWithLayout('mes/customer_product/add');
    }

    public function edit(): string|Response
    {
        $id = (int) $this->request->get('id');
        $tenantId = $this->getTenantId();

        $row = CustomerProductModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $customerId = (int) ($params['customer_id'] ?? $row->customer_id);
            $modelId = (int) ($params['model_id'] ?? $row->model_id);
            $price = isset($params['price']) ? (float) $params['price'] : (float) $row->price;
            $minQty = isset($params['min_qty']) ? (int) $params['min_qty'] : (int) $row->min_qty;

            if ($customerId <= 0 || $modelId <= 0) {
                return $this->error('请选择客户和产品型号');
            }
            if ($price <= 0) {
                return $this->error('请输入正确的单价');
            }
            if ($minQty <= 0) {
                $minQty = 1;
            }

            $customer = CustomerModel::where('tenant_id', $tenantId)
                ->where('id', $customerId)
                ->find();
            if (!$customer) {
                return $this->error('客户不存在');
            }
            $model = ProductModelModel::where('tenant_id', $tenantId)
                ->where('id', $modelId)
                ->find();
            if (!$model) {
                return $this->error('产品型号不存在');
            }

            $exists = CustomerProductModel::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->where('model_id', $modelId)
                ->where('id', '<>', $row->id)
                ->find();
            if ($exists) {
                return $this->error('该客户已配置该产品型号');
            }

            $params['customer_id'] = $customerId;
            $params['product_id'] = (int) $model->product_id;
            $params['model_id'] = $modelId;
            $params['price'] = $price;
            $params['min_qty'] = $minQty;
            if (!isset($params['currency']) || $params['currency'] === '') {
                $params['currency'] = $row->currency ?: 'CNY';
            }
            if (!isset($params['status']) || $params['status'] === '') {
                $params['status'] = $row->status;
            }
            $params['update_time'] = time();

            try {
                $row->save($params);
                return $this->success('保存成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('保存失败');
            }
        }

        $customerList = CustomerModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->order('id', 'desc')
            ->column('customer_name', 'id');

        $modelList = [];
        $models = ProductModelModel::with('product')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->select();
        foreach ($models as $model) {
            $productName = $model->product->name ?? '';
            $modelName = $model->name ?? '';
            $modelCode = $model->model_code ?? '';
            $fullName = $productName ? ($productName . ' - ' . $modelName) : $modelName;
            if ($modelCode) {
                $fullName .= ' (' . $modelCode . ')';
            }
            $modelList[$model->id] = $fullName;
        }

        View::assign('customerList', $customerList ?: []);
        View::assign('modelList', $modelList);
        View::assign('row', $row);
        View::assign('title', '编辑客户产品配置');
        return $this->fetchWithLayout('mes/customer_product/edit');
    }

    public function del(): Response
    {
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('请选择要删除的记录');
        }

        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : explode(',', (string) $ids);

        try {
            CustomerProductModel::where('tenant_id', $tenantId)
                ->whereIn('id', $idsArr)
                ->delete();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败');
        }
    }
}

