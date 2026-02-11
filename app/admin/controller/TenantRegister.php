<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\controller\Backend;
use app\admin\model\TenantRegisterModel;
use app\admin\model\TenantPackageModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 租户注册申请（公开访问，无需登录）
 */
class TenantRegister extends Backend
{
    /**
     * 提交注册申请
     */
    public function submit(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }

        $params = $this->request->post();

        // 验证必填项
        if (empty($params['company_name'] ?? '')) {
            return $this->error('企业名称不能为空');
        }
        if (empty($params['contact_name'] ?? '')) {
            return $this->error('联系人不能为空');
        }
        if (empty($params['contact_phone'] ?? '')) {
            return $this->error('联系电话不能为空');
        }
        if (empty($params['contact_email'] ?? '')) {
            return $this->error('联系邮箱不能为空');
        }
        if (empty($params['domain'] ?? '')) {
            return $this->error('期望绑定域名不能为空');
        }

        // 验证邮箱格式
        if (!filter_var($params['contact_email'], FILTER_VALIDATE_EMAIL)) {
            return $this->error('邮箱格式不正确');
        }

        // 验证域名是否已被占用
        $domain = trim($params['domain']);
        $existTenant = Db::name('tenant')->where('domain', 'like', '%' . $domain . '%')->find();
        if ($existTenant) {
            return $this->error('域名已被占用');
        }

        // 检查是否有待审核的申请
        $existRegister = Db::name('tenant_register')
            ->where('contact_email', $params['contact_email'])
            ->where('status', 0)
            ->find();
        if ($existRegister) {
            return $this->error('该邮箱已有待审核的申请');
        }

        // 生成注册单号
        $registerNo = 'REG' . date('YmdHis') . rand(1000, 9999);

        $data = [
            'register_no' => $registerNo,
            'company_name' => $params['company_name'],
            'contact_name' => $params['contact_name'],
            'contact_phone' => $params['contact_phone'],
            'contact_email' => $params['contact_email'],
            'domain' => $domain,
            'package_id' => (int) ($params['package_id'] ?? 1),
            'remark' => $params['remark'] ?? '',
            'status' => 0, // 待审核
            'create_time' => time(),
        ];

        try {
            TenantRegisterModel::create($data);
            return $this->success('注册申请已提交，请等待审核', ['register_no' => $registerNo]);
        } catch (\Exception $e) {
            return $this->error('提交失败：' . $e->getMessage());
        }
    }

    /**
     * 查询注册状态
     */
    public function query(): Response
    {
        $registerNo = trim($this->request->get('register_no'));
        if (empty($registerNo)) {
            return $this->error('注册单号不能为空');
        }

        $register = TenantRegisterModel::where('register_no', $registerNo)->find();
        if (!$register) {
            return $this->error('注册单号不存在');
        }

        $statusMap = [0 => '待审核', 1 => '已通过', 2 => '已拒绝'];
        $data = $register->toArray();
        $data['status_text'] = $statusMap[$register->status] ?? '未知';

        return $this->success('', $data);
    }
}
