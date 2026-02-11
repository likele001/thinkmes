<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ProcessPriceModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\ProcessModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 工序工价管理
 */
class ProcessPrice extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '工序工价管理');
            return $this->fetchWithLayout('mes/process_price/index');
        }
        
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $tenantId = $this->getTenantId();
        $query = ProcessPriceModel::with(['model.product', 'process'])
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc');
        
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        
        // 格式化显示
        foreach ($list as &$row) {
            $productName = $row['model']['product']['name'] ?? '';
            $modelName = $row['model']['name'] ?? '';
            $row['model_name'] = $productName ? ($productName . ' - ' . $modelName) : $modelName;
            $row['process_name'] = $row['process']['name'] ?? '';
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
            
            // 检查是否已存在
            $exists = ProcessPriceModel::where('tenant_id', $tenantId)
                ->where('model_id', $params['model_id'])
                ->where('process_id', $params['process_id'])
                ->find();
            if ($exists) {
                return $this->error('该型号和工序的工价已存在');
            }
            
            try {
                $model = ProcessPriceModel::create($params);
                return $this->success('添加成功', ['id' => $model->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        
        // 获取型号列表
        $tenantId = $this->getTenantId();
        $modelList = [];
        $models = ProductModelModel::with('product')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->select();
        foreach ($models as $model) {
            $displayName = $model->product->name . ' - ' . $model->name;
            if ($model->model_code) {
                $displayName .= ' (' . $model->model_code . ')';
            }
            $modelList[$model->id] = $displayName;
        }
        
        // 获取工序列表
        $processList = ProcessModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        
        View::assign('modelList', $modelList);
        View::assign('processList', $processList ?: []);
        View::assign('title', '添加工序工价');
        return $this->fetchWithLayout('mes/process_price/add');
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
            $model = ProcessPriceModel::where('tenant_id', $tenantId)->find($id);
            if (!$model) {
                return $this->error('记录不存在');
            }
            
            // 检查是否与其他记录冲突
            $exists = ProcessPriceModel::where('tenant_id', $tenantId)
                ->where('model_id', $params['model_id'])
                ->where('process_id', $params['process_id'])
                ->where('id', '<>', $id)
                ->find();
            if ($exists) {
                return $this->error('该型号和工序的工价已存在');
            }
            
            $params['update_time'] = time();
            try {
                $model->save($params);
                return $this->success('保存成功', ['id' => $model->id]);
            } catch (\Exception $e) {
                return $this->error('保存失败：' . $e->getMessage());
            }
        }
        
        $tenantId = $this->getTenantId();
        $data = ProcessPriceModel::where('tenant_id', $tenantId)->find($id);
        if (!$data) {
            return $this->error('记录不存在');
        }
        
        // 获取型号列表
        $modelList = [];
        $models = ProductModelModel::with('product')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->select();
        foreach ($models as $model) {
            $displayName = $model->product->name . ' - ' . $model->name;
            if ($model->model_code) {
                $displayName .= ' (' . $model->model_code . ')';
            }
            $modelList[$model->id] = $displayName;
        }
        
        // 获取工序列表
        $processList = ProcessModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        
        View::assign('modelList', $modelList);
        View::assign('processList', $processList ?: []);
        View::assign('data', $data->toArray());
        View::assign('title', '编辑工序工价');
        return $this->fetchWithLayout('mes/process_price/edit');
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
            ProcessPriceModel::where('tenant_id', $tenantId)
                ->whereIn('id', $ids)
                ->delete();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 批量设置工价
     */
    public function batch(): string|Response
    {
        if ($this->request->isPost()) {
            $modelIds = $this->request->post('model_ids/a', []);
            $processIds = $this->request->post('process_ids/a', []);
            $price = (float) $this->request->post('price', 0);
            $timePrice = (float) $this->request->post('time_price', 0);
            
            if (empty($modelIds) || empty($processIds)) {
                return $this->error('请选择型号和工序');
            }
            
            $tenantId = $this->getTenantId();
            $successCount = 0;
            $skipCount = 0;
            
            Db::startTrans();
            try {
                foreach ($modelIds as $modelId) {
                    foreach ($processIds as $processId) {
                        $exists = ProcessPriceModel::where('tenant_id', $tenantId)
                            ->where('model_id', $modelId)
                            ->where('process_id', $processId)
                            ->find();
                        
                        if ($exists) {
                            $exists->price = $price;
                            $exists->time_price = $timePrice;
                            $exists->update_time = time();
                            $exists->save();
                            $skipCount++;
                        } else {
                            ProcessPriceModel::create([
                                'tenant_id' => $tenantId,
                                'model_id' => $modelId,
                                'process_id' => $processId,
                                'price' => $price,
                                'time_price' => $timePrice,
                                'status' => 1,
                                'create_time' => time(),
                                'update_time' => time(),
                            ]);
                            $successCount++;
                        }
                    }
                }
                Db::commit();
                return $this->success("批量设置成功：新增 {$successCount} 条，更新 {$skipCount} 条");
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('批量设置失败：' . $e->getMessage());
            }
        }
        
        // 获取型号和工序列表
        $tenantId = $this->getTenantId();
        $modelList = [];
        $models = ProductModelModel::with('product')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->select();
        foreach ($models as $model) {
            $displayName = $model->product->name . ' - ' . $model->name;
            if ($model->model_code) {
                $displayName .= ' (' . $model->model_code . ')';
            }
            $modelList[$model->id] = $displayName;
        }
        
        $processList = ProcessModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->column('name', 'id');
        
        View::assign('modelList', $modelList);
        View::assign('processList', $processList ?: []);
        View::assign('title', '批量设置工价');
        return $this->fetchWithLayout('mes/process_price/batch');
    }
}
