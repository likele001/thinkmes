<?php
declare(strict_types=1);

namespace app\index\controller;

use app\admin\model\TenantRegisterModel;
use app\admin\model\TenantPackageModel;
use app\common\controller\BaseController;
use think\facade\View;
use think\Response;

/**
 * 租户注册控制器（公开访问）
 */
class TenantRegister extends BaseController
{
    /**
     * 注册页面
     */
    public function index(): string
    {
        // 获取套餐列表
        $packages = TenantPackageModel::order('sort')->order('id')->select()->toArray();
        View::assign('packages', $packages);
        return View::fetch('register');
    }

    /**
     * 提交注册申请
     */
    public function submit(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '非法请求']);
        }

        $params = $this->request->post();

        // 验证必填项
        if (empty($params['company_name'] ?? '')) {
            return json(['code' => 0, 'msg' => '企业名称不能为空']);
        }
        if (empty($params['contact_name'] ?? '')) {
            return json(['code' => 0, 'msg' => '联系人不能为空']);
        }
        if (empty($params['contact_phone'] ?? '')) {
            return json(['code' => 0, 'msg' => '联系电话不能为空']);
        }
        if (empty($params['contact_email'] ?? '')) {
            return json(['code' => 0, 'msg' => '联系邮箱不能为空']);
        }
        if (empty($params['login_account'] ?? '')) {
            return json(['code' => 0, 'msg' => '登录账号不能为空']);
        }
        if (empty($params['login_password'] ?? '')) {
            return json(['code' => 0, 'msg' => '登录密码不能为空']);
        }
        if (strlen($params['login_password'] ?? '') < 6) {
            return json(['code' => 0, 'msg' => '登录密码至少6位']);
        }
        if (empty($params['domain'] ?? '')) {
            return json(['code' => 0, 'msg' => '期望绑定域名不能为空']);
        }

        // 验证邮箱格式
        if (!filter_var($params['contact_email'], FILTER_VALIDATE_EMAIL)) {
            return json(['code' => 0, 'msg' => '邮箱格式不正确']);
        }

        // 验证登录账号格式（字母、数字、下划线）
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $params['login_account'])) {
            return json(['code' => 0, 'msg' => '登录账号只能包含字母、数字、下划线']);
        }

        // 验证域名是否已被占用
        $domain = trim($params['domain']);
        $existTenant = \think\facade\Db::name('tenant')
            ->where('domain', 'like', '%' . $domain . '%')
            ->where('status', 1)
            ->find();
        if ($existTenant) {
            return json(['code' => 0, 'msg' => '域名已被占用']);
        }

        // 检查是否有待审核的申请
        $existRegister = TenantRegisterModel::where('contact_email', $params['contact_email'])
            ->where('status', 0)
            ->find();
        if ($existRegister) {
            return json(['code' => 0, 'msg' => '该邮箱已有待审核的申请']);
        }

        // 生成注册单号
        $registerNo = 'REG' . date('YmdHis') . rand(1000, 9999);

        // 加密密码（使用bcrypt）
        $passwordHash = password_hash($params['login_password'], PASSWORD_BCRYPT);

        $data = [
            'register_no' => $registerNo,
            'company_name' => $params['company_name'],
            'contact_name' => $params['contact_name'],
            'contact_phone' => $params['contact_phone'],
            'contact_email' => $params['contact_email'],
            'login_account' => $params['login_account'], // 用户自定义的登录账号
            'login_password' => $passwordHash, // 加密后的密码
            'domain' => $domain,
            'package_id' => (int) ($params['package_id'] ?? 1),
            'remark' => $params['remark'] ?? '',
            'status' => 0, // 待审核
            'create_time' => time(),
        ];

        try {
            TenantRegisterModel::create($data);
            return json(['code' => 1, 'msg' => '注册申请已提交，请等待审核', 'data' => ['register_no' => $registerNo]]);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '提交失败：' . $e->getMessage()]);
        }
    }

    /**
     * 查询注册状态
     */
    public function query(): Response
    {
        $registerNo = trim($this->request->get('register_no'));
        if (empty($registerNo)) {
            return json(['code' => 0, 'msg' => '注册单号不能为空']);
        }

        $register = TenantRegisterModel::where('register_no', $registerNo)->find();
        if (!$register) {
            return json(['code' => 0, 'msg' => '注册单号不存在']);
        }

        $statusMap = [0 => '待审核', 1 => '已通过', 2 => '已拒绝'];
        $data = $register->toArray();
        $data['status_text'] = $statusMap[$register->status] ?? '未知';

        return json(['code' => 1, 'data' => $data]);
    }
}
