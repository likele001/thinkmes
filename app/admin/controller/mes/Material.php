<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\MaterialModel;
use think\facade\View;
use think\Response;

/**
 * 物料管理
 */
class Material extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '物料管理');
            return $this->fetchWithLayout('mes/material/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $name = trim((string) $this->request->get('name'));
        $status = $this->request->get('status');

        $tenantId = $this->getTenantId();
        $query = MaterialModel::where('tenant_id', $tenantId)->order('id', 'desc');

        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

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

            try {
                $material = MaterialModel::create($params);
                return $this->success('添加成功', ['id' => $material->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        View::assign('title', '添加物料');
        return $this->fetchWithLayout('mes/material/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = MaterialModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('物料不存在');
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

        View::assign('row', $row);
        View::assign('title', '编辑物料');
        return $this->fetchWithLayout('mes/material/edit');
    }

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
        
        try {
            foreach ($idsArr as $id) {
                $material = MaterialModel::where('tenant_id', $tenantId)->find($id);
                if ($material) {
                    $material->delete();
                }
            }
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
