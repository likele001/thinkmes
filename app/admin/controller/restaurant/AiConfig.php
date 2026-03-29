<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\admin\model\restaurant\RestaurantAiConfigModel;
use app\common\lib\restaurant\RestaurantAiService;
use think\facade\View;
use think\Response;

class AiConfig extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '餐饮AI配置');
            return $this->fetchWithLayout('restaurant/ai_config/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = RestaurantAiConfigModel::order('id', 'desc')->where('tenant_id', $tenantId);
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $key = (string) ($row['api_key'] ?? '');
            $row['api_key_masked'] = $key ? ('***' . substr($key, -4)) : '';
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        $tenantId = $this->getTenantId();
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['provider'])) return $this->error('请选择供应商');
            $data = [
                'tenant_id' => $tenantId,
                'provider' => trim((string) ($params['provider'] ?? '')),
                'api_key' => trim((string) ($params['api_key'] ?? '')),
                'api_base' => trim((string) ($params['api_base'] ?? '')),
                'model' => trim((string) ($params['model'] ?? 'gpt-3.5-turbo')),
                'status' => isset($params['status']) ? (int) $params['status'] : 1,
                'create_time' => time(),
                'update_time' => time(),
            ];
            try {
                RestaurantAiConfigModel::create($data);
                return $this->success('添加成功');
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        View::assign('title', '添加餐饮AI配置');
        return $this->fetchWithLayout('restaurant/ai_config/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) return $this->error('参数错误');
        $tenantId = $this->getTenantId();
        $row = RestaurantAiConfigModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) return $this->error('记录不存在');
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['provider'])) return $this->error('请选择供应商');
            $apiKey = trim((string) ($params['api_key'] ?? ''));
            if ($apiKey === '' || $apiKey === '***') $apiKey = (string) $row->api_key;
            $data = [
                'provider' => trim((string) ($params['provider'] ?? '')),
                'api_key' => $apiKey,
                'api_base' => trim((string) ($params['api_base'] ?? '')),
                'model' => trim((string) ($params['model'] ?? 'gpt-3.5-turbo')),
                'status' => isset($params['status']) ? (int) $params['status'] : (int) $row->status,
                'update_time' => time(),
            ];
            try {
                $row->save($data);
                return $this->success('保存成功');
            } catch (\Throwable $e) {
                return $this->error('保存失败：' . $e->getMessage());
            }
        }
        $masked = $row->api_key ? ('***' . substr((string) $row->api_key, -4)) : '';
        View::assign('row', $row);
        View::assign('api_key_masked', $masked);
        View::assign('title', '编辑餐饮AI配置');
        return $this->fetchWithLayout('restaurant/ai_config/edit');
    }

    public function del(): Response
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $ids = $this->request->post('ids');
        if (empty($ids)) return $this->error('参数错误');
        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : explode(',', (string) $ids);
        $count = 0;
        foreach ($idsArr as $id) {
            $row = RestaurantAiConfigModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }

    public function test(): Response
    {
        $ids = (int) $this->request->post('id', 0);
        if ($ids <= 0) return $this->error('请指定配置');
        $tenantId = $this->getTenantId();
        $row = RestaurantAiConfigModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) return $this->error('配置不存在');
        $svc = (new RestaurantAiService($tenantId))->setModule('restaurant_ai_config', 'test');
        $text = $svc->chatWithConfig([
            'api_key' => (string) $row->api_key,
            'api_base' => (string) $row->api_base,
            'model' => (string) $row->model,
        ], [['role' => 'user', 'content' => '你好，请回复：连接成功']], ['temperature' => 0.1, 'max_tokens' => 50]);
        if (!$text) return $this->error('测试失败');
        return $this->success('测试成功', ['reply' => $text]);
    }
}

