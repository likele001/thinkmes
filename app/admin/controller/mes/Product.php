<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ProductModel;
use app\admin\model\mes\ProductModelModel;
use app\admin\model\mes\ProcessModel;
use app\admin\model\mes\ProcessPriceModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 产品管理
 * 
 * @icon fa fa-cube
 * @remark 管理工厂生产的产品信息
 */
class Product extends Backend
{
    /**
     * 产品列表
     */
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '产品管理');
            return $this->fetchWithLayout('mes/product/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $name = trim((string) $this->request->get('name'));
        $status = $this->request->get('status');

        $tenantId = $this->getTenantId();
        $query = ProductModel::with(['models'])
            ->where('tenant_id', $tenantId)
            ->order('id', 'desc');

        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 添加产品
     */
    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $models = $this->request->post('models/a');
            $prices = $this->request->post('prices/a');

            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $tenantId = $this->getTenantId();
            $params['tenant_id'] = $tenantId;

            Db::startTrans();
            try {
                $product = ProductModel::create($params);

                // 如果产品保存成功且有型号数据，则保存型号和工价
                if (!empty($models)) {
                    foreach ($models as $index => $modelData) {
                        if (!empty($modelData['name'])) {
                            // 保存型号
                            $modelModel = ProductModelModel::create([
                                'tenant_id' => $tenantId,
                                'product_id' => $product->id,
                                'name' => $modelData['name'],
                                'model_code' => $modelData['model_code'] ?? '',
                                'description' => $modelData['description'] ?? '',
                                'status' => 1
                            ]);

                            // 如果型号保存成功且有工价数据，则保存工价
                            if (!empty($prices)) {
                                foreach ($prices as $processId => $priceData) {
                                    // 将 $processId 转换为字符串，因为表单提交的键可能是整数
                                    $processId = (string) $processId;
                                    // 跳过时间工价字段，只处理计件工价
                                    if (strpos($processId, '_time') !== false) {
                                        continue;
                                    }

                                    if (isset($priceData[$index]) && $priceData[$index] > 0) {
                                        $timePrice = isset($prices[$processId . '_time'][$index]) ? $prices[$processId . '_time'][$index] : 0;
                                        ProcessPriceModel::create([
                                            'tenant_id' => $tenantId,
                                            'model_id' => $modelModel->id,
                                            'process_id' => (int) $processId, // 保存到数据库时转换为整数
                                            'price' => $priceData[$index],
                                            'time_price' => $timePrice,
                                            'status' => 1
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                Db::commit();
                return $this->success('添加成功', ['id' => $product->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        // 获取工序列表用于工价设置
        $tenantId = $this->getTenantId();
        $processList = ProcessModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select();
        View::assign('processList', $processList);
        // 只传递 id 和 name 给 JS，避免数据过大
        $processListForJs = $processList->map(function($item) {
            return ['id' => $item->id, 'name' => $item->name];
        })->toArray();
        View::assign('processListJson', json_encode($processListForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        View::assign('title', '添加产品');
        return $this->fetchWithLayout('mes/product/add');
    }

    /**
     * 编辑产品
     */
    public function edit(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = ProductModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('产品不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $models = $this->request->post('models/a');
            $prices = $this->request->post('prices/a');

            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            Db::startTrans();
            try {
                $row->save($params);

                // 如果产品更新成功且有型号数据，则更新型号和工价
                if (!empty($models)) {
                    // 先删除现有的型号和工价
                    $existingModels = ProductModelModel::where('tenant_id', $tenantId)
                        ->where('product_id', $ids)
                        ->select();
                    foreach ($existingModels as $existingModel) {
                        ProcessPriceModel::where('tenant_id', $tenantId)
                            ->where('model_id', $existingModel->id)
                            ->delete();
                        $existingModel->delete();
                    }

                    // 重新创建型号和工价
                    foreach ($models as $index => $modelData) {
                        if (!empty($modelData['name'])) {
                            // 保存型号
                            $modelModel = ProductModelModel::create([
                                'tenant_id' => $tenantId,
                                'product_id' => $ids,
                                'name' => $modelData['name'],
                                'model_code' => $modelData['model_code'] ?? '',
                                'description' => $modelData['description'] ?? '',
                                'status' => 1
                            ]);

                            // 如果型号保存成功且有工价数据，则保存工价
                            if (!empty($prices)) {
                                foreach ($prices as $processId => $priceData) {
                                    // 将 $processId 转换为字符串，因为表单提交的键可能是整数
                                    $processId = (string) $processId;
                                    // 跳过时间工价字段，只处理计件工价
                                    if (strpos($processId, '_time') !== false) {
                                        continue;
                                    }

                                    if (isset($priceData[$index]) && $priceData[$index] > 0) {
                                        $timePrice = isset($prices[$processId . '_time'][$index]) ? $prices[$processId . '_time'][$index] : 0;
                                        ProcessPriceModel::create([
                                            'tenant_id' => $tenantId,
                                            'model_id' => $modelModel->id,
                                            'process_id' => (int) $processId, // 保存到数据库时转换为整数
                                            'price' => $priceData[$index],
                                            'time_price' => $timePrice,
                                            'status' => 1
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                Db::commit();
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        // 获取工序列表用于工价设置
        $processList = ProcessModel::where('tenant_id', $tenantId)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select();
        View::assign('processList', $processList);
        // 只传递 id 和 name 给 JS，避免数据过大
        $processListForJs = $processList->map(function($item) {
            return ['id' => $item->id, 'name' => $item->name];
        })->toArray();
        View::assign('processListJson', json_encode($processListForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        // 获取产品的型号列表
        $models = ProductModelModel::where('tenant_id', $tenantId)
            ->where('product_id', $ids)
            ->select();

        // 获取每个型号的工价数据
        $modelsWithPrices = [];
        foreach ($models as $model) {
            $prices = ProcessPriceModel::where('tenant_id', $tenantId)
                ->where('model_id', $model->id)
                ->select();
            $priceData = [];
            foreach ($prices as $price) {
                $priceData[$price->process_id] = [
                    'price' => $price->price,
                    'time_price' => $price->time_price
                ];
            }
            $modelsWithPrices[] = [
                'model' => $model,
                'prices' => $priceData
            ];
        }
        View::assign('models', $modelsWithPrices);
        View::assign('modelsJson', json_encode($modelsWithPrices, JSON_UNESCAPED_UNICODE));

        View::assign('row', $row);
        View::assign('title', '编辑产品');
        return $this->fetchWithLayout('mes/product/edit');
    }

    /**
     * 删除产品
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
                $product = ProductModel::where('tenant_id', $tenantId)->find($id);
                if (!$product) {
                    continue;
                }

                // 检查是否有关联的型号
                $modelCount = ProductModelModel::where('tenant_id', $tenantId)
                    ->where('product_id', $id)
                    ->count();
                if ($modelCount > 0) {
                    throw new \Exception("产品【{$product->name}】下还有{$modelCount}个型号，请先删除相关型号");
                }

                $product->delete();
            }

            Db::commit();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
