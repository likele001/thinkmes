<?php
declare(strict_types=1);

namespace app\admin\controller\finance;

use app\admin\controller\Backend;
use app\admin\model\finance\FinanceReceivableModel;
use app\admin\model\crm\CustomerModel;
use think\facade\View;
use think\Response;

class Receivable extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() || $limitParam === null || $limitParam === '') {
            $tenantId = $this->getTenantId();
            View::assign('customerList', CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->column('name', 'id'));
            View::assign('title', '应收账款');
            return $this->fetchWithLayout('finance/receivable/index');
        }
        [$limit, $page] = $this->getPaginationParams();
        $query = FinanceReceivableModel::with(['customer'])->order('id', 'desc');
        $this->applyTenantFilter($query);
        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        $customerId = $this->request->get('customer_id');
        if ($customerId !== '' && $customerId !== null) {
            $query->where('customer_id', (int) $customerId);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['customer_name'] = $item['customer']['name'] ?? '-';
            $item['balance'] = round((float)$item['amount'] - (float)$item['received'], 2);
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
            $params['received'] = $params['received'] ?? 0;
            $params['status'] = ((float)($params['amount']) <= (float)($params['received'])) ? 1 : 0;
            try {
                $row = FinanceReceivableModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        $tenantId = $this->getTenantId();
        View::assign('customerList', CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->select());
        View::assign('statusList', FinanceReceivableModel::getStatusList());
        View::assign('title', '添加应收');
        return $this->fetchWithLayout('finance/receivable/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = FinanceReceivableModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            $params['update_time'] = time();
            $params['status'] = ((float)($params['amount']) <= (float)($params['received'])) ? 1 : 0;
            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }
        View::assign('customerList', CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->select());
        View::assign('statusList', FinanceReceivableModel::getStatusList());
        View::assign('row', $row);
        View::assign('title', '编辑应收');
        return $this->fetchWithLayout('finance/receivable/edit');
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
            $row = FinanceReceivableModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }
}
