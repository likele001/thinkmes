<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\ProcessRouteModel;
use app\admin\model\mes\ProcessModel;
use app\admin\model\mes\ProductModel;
use app\admin\model\mes\ProductModelModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

class ProcessRoute extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->resolveTenantId();
            $isPlatform = $this->isPlatformAdmin() && $this->getTenantId() === 0;
            View::assign('is_platform', $isPlatform ? 1 : 0);
            View::assign('tenant_id', $tenantId);
            if ($isPlatform) {
                $tenants = Db::name('tenant')->where('status', 1)->order('id', 'asc')->field('id,name')->select()->toArray();
                View::assign('tenants', $tenants);
            } else {
                View::assign('tenants', []);
            }

            $models = [];
            if ($tenantId > 0) {
                $models = ProductModelModel::with(['product'])
                    ->where('tenant_id', $tenantId)
                    ->where('status', 1)
                    ->select();
            }
            $modelList = [];
            foreach ($models as $model) {
                $productName = '';
                if (isset($model->product) && $model->product) {
                    $productName = $model->product->name ?? '';
                }
                $modelName = $model->name ?? '';
                $modelCode = $model->model_code ?? '';
                $fullName = $productName ? ($productName . ' - ' . $modelName) : $modelName;
                if ($modelCode) {
                    $fullName .= ' (' . $modelCode . ')';
                }
                $modelList[$model->id] = $fullName;
            }
            View::assign('modelList', $modelList);
            View::assign('routeTypeList', ProcessRouteModel::getRouteTypeList());
            View::assign('statusList', ProcessRouteModel::getStatusList());
            View::assign('title', '工艺路线管理');
            return $this->fetchWithLayout('mes/process_route/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = ProcessRouteModel::with(['product', 'model'])->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }

        $modelId = (int) $this->request->get('model_id', 0);
        if ($modelId > 0) {
            $query->where('model_id', $modelId);
        }

        $status = (string) $this->request->get('status', '');
        if ($status !== '') {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $row = $this->request->post('row/a');
            if (empty($row)) {
                return $this->error('参数不能为空');
            }

            $tenantId = $this->resolveTenantId();
            if ($tenantId <= 0) {
                return $this->error('tenant_id required');
            }
            $row['tenant_id'] = $tenantId;
            $row['route_name'] = trim((string) ($row['route_name'] ?? ''));
            $row['route_code'] = trim((string) ($row['route_code'] ?? ''));
            $row['steps_json'] = $this->decodeStepsJson(trim((string) ($row['steps_json'] ?? '')));
            $row['create_time'] = time();
            $row['update_time'] = time();

            if ($row['route_name'] === '') {
                return $this->error('路线名称不能为空');
            }

            $modelId = (int) ($row['model_id'] ?? 0);
            if ($modelId <= 0) {
                return $this->error('产品型号不能为空');
            }

            if ($row['route_code'] === '') {
                $row['route_code'] = $this->generateRouteCode();
            } else {
                $exists = ProcessRouteModel::where('tenant_id', $tenantId)
                    ->where('route_code', $row['route_code'])
                    ->find();
                if ($exists) {
                    return $this->error('路线编码已存在');
                }
            }

            $row['is_default'] = (int) ($row['is_default'] ?? 0) ? 1 : 0;

            try {
                if ($row['is_default'] === 1) {
                    ProcessRouteModel::where('tenant_id', $tenantId)
                        ->where('model_id', $modelId)
                        ->update(['is_default' => 0]);
                }
                ProcessRouteModel::create($row);
                return $this->success('保存成功');
            } catch (\Exception $e) {
                return $this->error('保存失败');
            }
        }

        $tenantId = $this->resolveTenantId();
        $isPlatform = $this->isPlatformAdmin() && $this->getTenantId() === 0;
        View::assign('is_platform', $isPlatform ? 1 : 0);
        View::assign('tenant_id', $tenantId);
        if ($isPlatform) {
            $tenants = Db::name('tenant')->where('status', 1)->order('id', 'asc')->field('id,name')->select()->toArray();
            View::assign('tenants', $tenants);
        } else {
            View::assign('tenants', []);
        }

        $models = [];
        if ($tenantId > 0) {
            $models = ProductModelModel::with(['product'])
                ->where('tenant_id', $tenantId)
                ->where('status', 1)
                ->select();
        }
        $modelList = [];
        foreach ($models as $model) {
            $productName = '';
            if (isset($model->product) && $model->product) {
                $productName = $model->product->name ?? '';
            }
            $modelName = $model->name ?? '';
            $modelCode = $model->model_code ?? '';
            $fullName = $productName ? ($productName . ' - ' . $modelName) : $modelName;
            if ($modelCode) {
                $fullName .= ' (' . $modelCode . ')';
            }
            $modelList[$model->id] = $fullName;
        }
        View::assign('modelList', $modelList);
        $processList = [];
        if ($tenantId > 0) {
            $processList = ProcessModel::whereIn('tenant_id', [0, $tenantId])->where('status', 1)->order('sort', 'asc')->order('id', 'asc')->column('name', 'id');
        }
        View::assign('processList', $processList ?: []);
        $routeTemplates = [];
        if ($tenantId > 0) {
            $tpl = ProcessRouteModel::with(['model', 'product'])
                ->where('tenant_id', $tenantId)
                ->order('id', 'desc')
                ->limit(200)
                ->select()
                ->toArray();
            foreach ($tpl as $t) {
                $mn = $t['model']['name'] ?? '';
                $pn = $t['model']['product']['name'] ?? ($t['product']['name'] ?? '');
                $label = trim(($t['route_name'] ?? '') . ($pn || $mn ? ' - ' . trim($pn . ' ' . $mn) : ''));
                $routeTemplates[] = ['id' => (int) $t['id'], 'label' => $label !== '' ? $label : ('路线#' . (int) $t['id'])];
            }
        }
        View::assign('routeTemplates', $routeTemplates);
        View::assign('routeTypeList', ProcessRouteModel::getRouteTypeList());
        View::assign('statusList', ProcessRouteModel::getStatusList());
        View::assign('title', '添加工艺路线');
        return $this->fetchWithLayout('mes/process_route/add');
    }

    public function edit(): string|Response
    {
        $idParam = $this->request->param('ids');
        if ($idParam === null || $idParam === '') {
            $idParam = $this->request->param('id');
        }
        if ($idParam === null || $idParam === '') {
            return $this->error('参数错误');
        }
        $id = (int) $idParam;

        $tenantId = $this->getTenantId();
        $route = $tenantId > 0 ? ProcessRouteModel::where('tenant_id', $tenantId)->find($id) : ProcessRouteModel::find($id);
        if (!$route) {
            return $this->error('记录不存在');
        }

        if ($this->request->isPost()) {
            $row = $this->request->post('row/a');
            if (empty($row)) {
                return $this->error('参数不能为空');
            }

            $row['route_name'] = trim((string) ($row['route_name'] ?? ''));
            $row['route_code'] = trim((string) ($row['route_code'] ?? ''));
            $row['steps_json'] = $this->decodeStepsJson(trim((string) ($row['steps_json'] ?? '')));
            $row['update_time'] = time();

            if ($row['route_name'] === '') {
                return $this->error('路线名称不能为空');
            }

            $modelId = (int) ($row['model_id'] ?? 0);
            if ($modelId <= 0) {
                return $this->error('产品型号不能为空');
            }

            if ($row['route_code'] === '') {
                $row['route_code'] = $route->route_code;
            } else {
                $exists = ProcessRouteModel::where('tenant_id', $tenantId)
                    ->where('route_code', $row['route_code'])
                    ->where('id', '<>', $route->id)
                    ->find();
                if ($exists) {
                    return $this->error('路线编码已存在');
                }
            }

            $row['is_default'] = (int) ($row['is_default'] ?? 0) ? 1 : 0;

            try {
                if ($row['is_default'] === 1) {
                    ProcessRouteModel::where('tenant_id', $tenantId)
                        ->where('model_id', $modelId)
                        ->where('id', '<>', $route->id)
                        ->update(['is_default' => 0]);
                }
                $route->save($row);
                return $this->success('保存成功');
            } catch (\Exception $e) {
                return $this->error('保存失败');
            }
        }

        $routeTenantId = (int) $route->tenant_id;
        $route->steps_json = $this->decodeStepsJson((string) $route->steps_json);
        $isPlatform = $this->isPlatformAdmin() && $this->getTenantId() === 0;
        View::assign('is_platform', $isPlatform ? 1 : 0);
        View::assign('tenant_id', $routeTenantId);
        if ($isPlatform) {
            $tenants = Db::name('tenant')->where('status', 1)->order('id', 'asc')->field('id,name')->select()->toArray();
            View::assign('tenants', $tenants);
        } else {
            View::assign('tenants', []);
        }

        $models = [];
        if ($routeTenantId > 0) {
            $models = ProductModelModel::with(['product'])
                ->where('tenant_id', $routeTenantId)
                ->where('status', 1)
                ->select();
        }
        $modelList = [];
        foreach ($models as $model) {
            $productName = '';
            if (isset($model->product) && $model->product) {
                $productName = $model->product->name ?? '';
            }
            $modelName = $model->name ?? '';
            $modelCode = $model->model_code ?? '';
            $fullName = $productName ? ($productName . ' - ' . $modelName) : $modelName;
            if ($modelCode) {
                $fullName .= ' (' . $modelCode . ')';
            }
            $modelList[$model->id] = $fullName;
        }
        View::assign('modelList', $modelList);
        $processList = [];
        if ($routeTenantId > 0) {
            $processList = ProcessModel::whereIn('tenant_id', [0, $routeTenantId])->where('status', 1)->order('sort', 'asc')->order('id', 'asc')->column('name', 'id');
        }
        View::assign('processList', $processList ?: []);
        $routeTemplates = [];
        if ($routeTenantId > 0) {
            $tpl = ProcessRouteModel::with(['model', 'product'])
                ->where('tenant_id', $routeTenantId)
                ->order('id', 'desc')
                ->limit(200)
                ->select()
                ->toArray();
            foreach ($tpl as $t) {
                $mn = $t['model']['name'] ?? '';
                $pn = $t['model']['product']['name'] ?? ($t['product']['name'] ?? '');
                $label = trim(($t['route_name'] ?? '') . ($pn || $mn ? ' - ' . trim($pn . ' ' . $mn) : ''));
                $routeTemplates[] = ['id' => (int) $t['id'], 'label' => $label !== '' ? $label : ('路线#' . (int) $t['id'])];
            }
        }
        View::assign('routeTemplates', $routeTemplates);
        View::assign('routeTypeList', ProcessRouteModel::getRouteTypeList());
        View::assign('statusList', ProcessRouteModel::getStatusList());
        View::assign('row', $route);
        View::assign('title', '编辑工艺路线');
        return $this->fetchWithLayout('mes/process_route/edit');
    }

    public function get(): Response
    {
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) return $this->error('参数错误');
        $tenantId = $this->getTenantId();
        $route = $tenantId > 0 ? ProcessRouteModel::where('tenant_id', $tenantId)->find($id) : ProcessRouteModel::find($id);
        if (!$route) return $this->error('记录不存在');
        return $this->success('', [
            'id' => (int) $route->id,
            'tenant_id' => (int) $route->tenant_id,
            'route_name' => (string) $route->route_name,
            'steps_json' => $this->decodeStepsJson((string) $route->steps_json),
        ]);
    }

    public function del(): Response
    {
        $idsParam = $this->request->post('ids');
        if ($idsParam === null || $idsParam === '') {
            return $this->error('参数错误');
        }
        $ids = is_array($idsParam) ? $idsParam : explode(',', (string) $idsParam);

        $tenantId = $this->getTenantId();
        try {
            ProcessRouteModel::where('tenant_id', $tenantId)
                ->whereIn('id', $ids)
                ->delete();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败');
        }
    }

    protected function generateRouteCode(): string
    {
        $prefix = 'RT' . date('Ymd');
        $last = ProcessRouteModel::where('route_code', 'like', $prefix . '%')
            ->order('id', 'desc')
            ->value('route_code');
        if ($last) {
            $num = (int) substr($last, -4);
            $num++;
        } else {
            $num = 1;
        }
        return $prefix . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }

    private function resolveTenantId(): int
    {
        $tenantId = $this->getTenantId();
        if ($tenantId > 0) return $tenantId;
        $p = $this->request->param('tenant_id');
        if ($p !== null && $p !== '') return (int) $p;
        $g = $this->request->get('tenant_id');
        if ($g !== null && $g !== '') return (int) $g;
        $post = $this->request->post('tenant_id');
        if ($post !== null && $post !== '') return (int) $post;
        return 0;
    }

    private function decodeStepsJson(string $raw): string
    {
        $s = trim($raw);
        for ($i = 0; $i < 3; $i++) {
            $prev = $s;
            $s = html_entity_decode($s, ENT_QUOTES);
            if ($s === $prev) break;
        }
        return trim($s);
    }
}
