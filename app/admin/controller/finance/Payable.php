<?php
declare(strict_types=1);

namespace app\admin\controller\finance;

use app\admin\controller\Backend;
use app\admin\model\finance\FinancePayableModel;
use app\admin\model\mes\SupplierModel;
use think\facade\View;
use think\Response;

class Payable extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() || $limitParam === null || $limitParam === '') {
            View::assign('title', '应付账款');
            return $this->fetchWithLayout('finance/payable/index');
        }
        [$limit, $page] = $this->getPaginationParams();
        $query = FinancePayableModel::with(['supplier'])->order('id', 'desc');
        $this->applyTenantFilter($query);
        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $supplierId = $this->request->get('supplier_id');
        if ($supplierId !== '' && $supplierId !== null) {
            $query->where('supplier_id', (int) $supplierId);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['supplier_name'] = $item['supplier']['name'] ?? '-';
            $item['balance'] = round((float)$item['amount'] - (float)$item['paid'], 2);
        }
        unset($item);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['title']) || !isset($params['amount'])) {
                return $this->error('请填写摘要与金额');
            }
            $params['tenant_id'] = $this->getTenantId();
            $params['create_time'] = time();
            $params['update_time'] = time();
            $params['paid'] = $params['paid'] ?? 0;
            $params['status'] = ((float)($params['amount']) <= (float)($params['paid'])) ? 1 : 0;
            try {
                $row = FinancePayableModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        $tenantId = $this->getTenantId();
        View::assign('supplierList', SupplierModel::where('tenant_id', $tenantId)->select());
        View::assign('statusList', FinancePayableModel::getStatusList());
        View::assign('title', '添加应付');
        return $this->fetchWithLayout('finance/payable/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = FinancePayableModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            $params['update_time'] = time();
            $params['status'] = ((float)($params['amount']) <= (float)($params['paid'])) ? 1 : 0;
            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }
        View::assign('supplierList', SupplierModel::where('tenant_id', $tenantId)->select());
        View::assign('statusList', FinancePayableModel::getStatusList());
        View::assign('row', $row);
        View::assign('title', '编辑应付');
        return $this->fetchWithLayout('finance/payable/edit');
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
        $count = 0;
        foreach ($idsArr as $id) {
            $row = FinancePayableModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }
}
