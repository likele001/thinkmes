<?php
declare(strict_types=1);

namespace app\admin\controller\crm;

use app\admin\controller\Backend;
use app\admin\model\crm\FollowModel;
use app\admin\model\crm\CustomerModel;
use app\admin\model\crm\OpportunityModel;
use think\facade\Session;
use think\facade\View;
use think\Response;

/**
 * CRM 跟进记录管理
 */
class Follow extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '跟进记录');
            return $this->fetchWithLayout('crm/follow/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $customerId = (int) $this->request->get('customer_id', 0);
        $opportunityId = (int) $this->request->get('opportunity_id', 0);

        $tenantId = $this->getTenantId();
        $query = FollowModel::order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        }
        if ($customerId > 0) {
            $query->where('customer_id', $customerId);
        }
        if ($opportunityId > 0) {
            $query->where('opportunity_id', $opportunityId);
        }

        $total = $query->count();
        $rows = $query->page($page, $limit)->select();

        $list = [];
        foreach ($rows as $row) {
            $arr = $row->toArray();
            if ($row->customer_id > 0) {
                $customer = CustomerModel::find($row->customer_id);
                $arr['customer_name'] = $customer ? $customer->name : '';
            }
            if ($row->opportunity_id > 0) {
                $opportunity = OpportunityModel::find($row->opportunity_id);
                $arr['opportunity_name'] = $opportunity ? $opportunity->name : '';
            }
            $list[] = $arr;
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
            $content = trim((string) ($params['content'] ?? ''));

            if ($customerId <= 0) {
                return $this->error('请选择客户');
            }
            if ($content === '') {
                return $this->error('请输入跟进内容');
            }

            $params['tenant_id'] = $tenantId;
            $params['admin_id'] = Session::get('admin_info.id', 0);
            if (!isset($params['type']) || $params['type'] === '') {
                $params['type'] = 'visit';
            }
            if (isset($params['next_follow_time']) && $params['next_follow_time'] !== '') {
                $params['next_follow_time'] = strtotime($params['next_follow_time']);
            }

            try {
                $follow = FollowModel::create($params);
                return $this->success('添加成功', ['id' => $follow->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }

        $customerId = (int) $this->request->get('customer_id', 0);
        $opportunityId = (int) $this->request->get('opportunity_id', 0);
        $tenantId = $this->getTenantId();
        $customers = CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->select()->toArray();
        $opportunities = [];
        if ($customerId > 0) {
            $opportunities = OpportunityModel::where('tenant_id', $tenantId)
                ->where('customer_id', $customerId)
                ->select()->toArray();
        }

        View::assign('customers', $customers);
        View::assign('opportunities', $opportunities);
        View::assign('customer_id', $customerId);
        View::assign('opportunity_id', $opportunityId);
        View::assign('title', '添加跟进记录');
        return $this->fetchWithLayout('crm/follow/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids');
        if (empty($ids)) {
            $ids = $this->request->param('id');
        }
        if (empty($ids)) {
            return $this->error('参数错误');
        }

        $tenantId = $this->getTenantId();
        $row = FollowModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('跟进记录不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $content = trim((string) ($params['content'] ?? ''));
            if ($content === '') {
                return $this->error('请输入跟进内容');
            }

            if (isset($params['next_follow_time']) && $params['next_follow_time'] !== '') {
                $params['next_follow_time'] = strtotime($params['next_follow_time']);
            }

            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }

        $tenantId = $this->getTenantId();
        $customers = CustomerModel::where('tenant_id', $tenantId)->where('status', 1)->select()->toArray();
        $opportunities = [];
        if ($row->customer_id > 0) {
            $opportunities = OpportunityModel::where('tenant_id', $tenantId)
                ->where('customer_id', $row->customer_id)
                ->select()->toArray();
        }

        View::assign('customers', $customers);
        View::assign('opportunities', $opportunities);
        View::assign('row', $row);
        View::assign('title', '编辑跟进记录');
        return $this->fetchWithLayout('crm/follow/edit');
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
                $follow = FollowModel::where('tenant_id', $tenantId)->find($id);
                if ($follow) {
                    $follow->delete();
                }
            }
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
}
