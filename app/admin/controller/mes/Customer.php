<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\CustomerModel;
use think\facade\View;
use think\Response;

/**
 * 客户管理
 */
class Customer extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', '客户管理');
            return $this->fetchWithLayout('mes/customer/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        
        $name = trim((string) $this->request->get('customer_name'));
        $status = $this->request->get('status');

        $tenantId = $this->getTenantId();
        $query = CustomerModel::order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tenantParam = (int) $this->request->get('tenant_id', 0);
            if ($tenantParam > 0) {
                $query->where('tenant_id', $tenantParam);
            }
        }

        if ($name !== '') {
            $query->where('customer_name', 'like', '%' . $name . '%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $rows = $query->page($page, $limit)->select();

        $list = [];
        foreach ($rows as $row) {
            $arr = $row->toArray();
            unset($arr['login_password']);
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
            $loginAccount = trim((string) ($params['login_account'] ?? ''));
            $loginPassword = (string) ($params['login_password'] ?? '');
            $customerName = trim((string) ($params['customer_name'] ?? ''));

            if ($customerName === '') {
                return $this->error('请输入客户名称');
            }
            if ($loginAccount === '') {
                return $this->error('请输入登录账号');
            }
            if (strlen($loginPassword) < 6 || strlen($loginPassword) > 32) {
                return $this->error('密码长度为 6-32 位');
            }

            $exists = CustomerModel::where('tenant_id', $tenantId)
                ->where('login_account', $loginAccount)
                ->find();
            if ($exists) {
                return $this->error('登录账号已存在');
            }

            $params['tenant_id'] = $tenantId;
            $params['login_account'] = $loginAccount;
            $params['login_password'] = password_hash($loginPassword, PASSWORD_BCRYPT);
            if (!isset($params['status']) || $params['status'] === '') {
                $params['status'] = 1;
            }
            if (!isset($params['default_lang']) || $params['default_lang'] === '') {
                $params['default_lang'] = 'zh-cn';
            }

            try {
                $customer = CustomerModel::create($params);
                return $this->success('添加成功', ['id' => $customer->id]);
            } catch (\Exception $e) {
                return $this->error('添加失败');
            }
        }

        View::assign('title', '添加客户');
        return $this->fetchWithLayout('mes/customer/add');
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
        $row = CustomerModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('客户不存在');
        }

        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params)) {
                return $this->error('参数不能为空');
            }

            $loginAccount = array_key_exists('login_account', $params) ? trim((string) $params['login_account']) : $row->login_account;
            if ($loginAccount === '') {
                return $this->error('请输入登录账号');
            }
            $exists = CustomerModel::where('tenant_id', $tenantId)
                ->where('login_account', $loginAccount)
                ->where('id', '<>', $row->id)
                ->find();
            if ($exists) {
                return $this->error('登录账号已存在');
            }

            $rawPassword = $params['login_password'] ?? '';
            if ($rawPassword !== '') {
                $rawPassword = (string) $rawPassword;
                if (strlen($rawPassword) < 6 || strlen($rawPassword) > 32) {
                    return $this->error('密码长度为 6-32 位');
                }
                $params['login_password'] = password_hash($rawPassword, PASSWORD_BCRYPT);
            } else {
                unset($params['login_password']);
            }

            $params['login_account'] = $loginAccount;
            if (!isset($params['default_lang']) || $params['default_lang'] === '') {
                $params['default_lang'] = $row->default_lang ?: 'zh-cn';
            }

            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Exception $e) {
                return $this->error('编辑失败');
            }
        }

        View::assign('row', $row);
        View::assign('title', '编辑客户');
        return $this->fetchWithLayout('mes/customer/edit');
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
                $customer = CustomerModel::where('tenant_id', $tenantId)->find($id);
                if ($customer) {
                    $customer->delete();
                }
            }
            return $this->success('删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败');
        }
    }
}
