<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\PrintTemplateModel;
use app\admin\model\crm\SalesOrderModel;
use app\admin\model\mes\ShipmentModel;
use app\admin\model\crm\ContractModel;
use app\admin\model\mes\OrderModel as MesOrderModel;
use think\facade\View;
use think\Response;

class PrintTemplate extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() || $limitParam === null || $limitParam === '') {
            View::assign('title', '打印模板');
            return $this->fetchWithLayout('print_template/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $page = max(1, (int) $this->request->get('page', 1));
        $tenantId = $this->getTenantId();
        $query = PrintTemplateModel::order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tp = (int) $this->request->get('tenant_id', 0);
            if ($tp > 0) {
                $query->where('tenant_id', $tp);
            }
        }
        $type = $this->request->get('type');
        if ($type !== '' && $type !== null) {
            $query->where('type', $type);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        $typeList = PrintTemplateModel::getTypeList();
        foreach ($list as &$item) {
            $item['type_text'] = $typeList[$item['type']] ?? $item['type'];
        }
        unset($item);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['name'])) {
                return $this->error('模板名称不能为空');
            }
            $params['tenant_id'] = $this->getTenantId();
            $params['create_time'] = time();
            $params['update_time'] = time();
            try {
                $row = PrintTemplateModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        View::assign('typeList', PrintTemplateModel::getTypeList());
        View::assign('title', '添加打印模板');
        return $this->fetchWithLayout('print_template/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = PrintTemplateModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }
            $params['update_time'] = time();
            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }
        View::assign('typeList', PrintTemplateModel::getTypeList());
        View::assign('row', $row);
        View::assign('title', '编辑打印模板');
        return $this->fetchWithLayout('print_template/edit');
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
            $row = PrintTemplateModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }

    /**
     * 按模板预览/打印：根据 ref_type、ref_id 取业务数据，替换模板中的 {变量名} 后输出
     * GET: template_id=可选，ref_type=order|sales_order|shipment|contract，ref_id=业务ID
     */
    public function preview(): string|Response
    {
        $refType = trim((string) $this->request->get('ref_type', ''));
        $refId = (int) $this->request->get('ref_id', 0);
        $templateId = (int) $this->request->get('template_id', 0);
        if ($refType === '' || $refId <= 0) {
            return $this->error('请指定打印类型(ref_type)与业务ID(ref_id)');
        }
        $tenantId = $this->getTenantId();
        $template = null;
        if ($templateId > 0) {
            $template = PrintTemplateModel::where('tenant_id', $tenantId)->find($templateId);
        }
        if (!$template) {
            $template = PrintTemplateModel::where('tenant_id', $tenantId)->where('type', $refType)->order('id', 'desc')->find();
        }
        if (!$template) {
            return $this->error('未找到该类型的打印模板，请先在【打印模板】中添加对应类型的模板');
        }
        $data = $this->getPrintData($refType, $refId);
        if ($data === null) {
            return $this->error('未找到对应业务数据');
        }
        $content = (string) $template->content;
        foreach ($data as $k => $v) {
            $content = str_replace('{' . $k . '}', (string) $v, $content);
        }
        View::assign('content', $content);
        View::assign('templateName', $template->name);
        return $this->fetch('print_template/preview');
    }

    /**
     * 根据业务类型与ID返回用于替换的键值对（键名即模板中的 {键名}）
     */
    protected function getPrintData(string $refType, int $refId): ?array
    {
        $tenantId = $this->getTenantId();
        switch ($refType) {
            case 'sales_order':
                $row = SalesOrderModel::with(['customer'])->where('tenant_id', $tenantId)->find($refId);
                if (!$row) {
                    return null;
                }
                $row = $row->toArray();
                return [
                    'order_no' => $row['order_no'] ?? '',
                    'customer_name' => $row['customer_name'] ?? ($row['customer']['name'] ?? ''),
                    'total_amount' => $row['total_amount'] ?? '',
                    'status' => $row['status'] ?? '',
                    'create_time' => !empty($row['create_time']) ? date('Y-m-d H:i', (int) $row['create_time']) : '',
                    'remark' => $row['remark'] ?? '',
                ];
            case 'order':
                $row = MesOrderModel::where('tenant_id', $tenantId)->find($refId);
                if (!$row) {
                    return null;
                }
                $row = $row->toArray();
                return [
                    'order_no' => $row['order_no'] ?? '',
                    'order_name' => $row['order_name'] ?? '',
                    'customer_name' => $row['customer_name'] ?? '',
                    'total_quantity' => $row['total_quantity'] ?? '',
                    'delivery_time' => !empty($row['delivery_time']) ? date('Y-m-d', (int) $row['delivery_time']) : '',
                    'create_time' => !empty($row['create_time']) ? date('Y-m-d H:i', (int) $row['create_time']) : '',
                    'remark' => $row['remark'] ?? '',
                ];
            case 'shipment':
                $row = ShipmentModel::with(['order'])->where('tenant_id', $tenantId)->find($refId);
                if (!$row) {
                    return null;
                }
                $row = $row->toArray();
                $orderNo = $row['order']['order_no'] ?? '';
                return [
                    'shipment_no' => $row['shipment_no'] ?? '',
                    'order_no' => $orderNo,
                    'customer_name' => $row['customer_name'] ?? ($row['order']['customer_name'] ?? ''),
                    'create_time' => !empty($row['create_time']) ? date('Y-m-d H:i', (int) $row['create_time']) : '',
                    'remark' => $row['remark'] ?? '',
                ];
            case 'contract':
                $row = ContractModel::where('tenant_id', $tenantId)->find($refId);
                if (!$row) {
                    return null;
                }
                $row = $row->toArray();
                $customerName = '';
                if (!empty($row['customer_id'])) {
                    $c = \app\admin\model\crm\CustomerModel::find($row['customer_id']);
                    $customerName = $c ? $c->name : '';
                }
                return [
                    'contract_no' => $row['contract_no'] ?? '',
                    'customer_name' => $customerName,
                    'amount' => $row['amount'] ?? '',
                    'sign_date' => !empty($row['sign_date']) ? date('Y-m-d', (int) $row['sign_date']) : '',
                    'create_time' => !empty($row['create_time']) ? date('Y-m-d H:i', (int) $row['create_time']) : '',
                ];
            default:
                return null;
        }
    }
}
