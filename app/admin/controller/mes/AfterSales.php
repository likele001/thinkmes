<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\AfterSalesModel;
use app\admin\model\mes\OrderModel;
use app\admin\model\mes\CustomerModel;
use app\admin\model\mes\TraceCodeModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 售后管理
 *
 * @icon fa fa-headphones
 */
class AfterSales extends Backend
{
    /**
     * 售后单列表
     */
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '售后管理');
            return $this->fetchWithLayout('mes/after_sales/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = AfterSalesModel::with(['order', 'customer'])
            ->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }

        $status = $this->request->get('status');
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();

        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /**
     * 添加售后单
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
            $params['after_sales_no'] = AfterSalesModel::generateAfterSalesNo();
            $params['operator_id'] = $this->auth->id ?? 0;

            try {
                $afterSales = AfterSalesModel::create($params);
                return $this->success('提交成功', ['id' => $afterSales->id]);
            } catch (\Exception $e) {
                return $this->error('提交失败');
            }
        }

        $orderId = $this->request->get('order_id');
        $traceCode = $this->request->get('trace_code');

        $tenantId = $this->getTenantId();
        $orders = OrderModel::where('tenant_id', $tenantId)->order('id', 'desc')->limit(100)->select();
        $customers = CustomerModel::where('tenant_id', $tenantId)->select();

        View::assign('orders', $orders);
        View::assign('customers', $customers);
        View::assign('order_id', $orderId);
        View::assign('trace_code', $traceCode);
        View::assign('title', '添加售后单');
        return $this->fetchWithLayout('mes/after_sales/add');
    }

    /**
     * 编辑/处理售后单
     */
    public function edit(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = AfterSalesModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('售后单不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            try {
                $row->save($params);
                return $this->success('保存成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('保存失败');
            }
        }

        $customers = CustomerModel::where('tenant_id', $tenantId)->select();
        View::assign('row', $row);
        View::assign('customers', $customers);
        View::assign('title', '处理售后单');
        return $this->fetchWithLayout('mes/after_sales/edit');
    }

    /**
     * 删除售后单
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

        try {
            AfterSalesModel::where('tenant_id', $tenantId)->whereIn('id', $idsArr)->delete();
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败');
        }
    }
}
