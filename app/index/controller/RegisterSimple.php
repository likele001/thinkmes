<?php
declare(strict_types=1);

namespace app\index\controller;

use app\admin\model\TenantRegisterModel;
use app\admin\model\TenantPackageModel;
use app\common\controller\BaseController;
use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 租户注册控制器（简化版 - 不依赖 think\Controller）
 */
class TenantRegisterSimple extends BaseController
{
    /**
     * 注册页面
     */
    public function index(): string
    {
        // 获取套餐列表
        $packages = \app\admin\model\TenantPackageModel::order('sort')->order('id')->select()->toArray();
        \think\facade\View::assign('packages', $packages);

        return \think\facade\View::fetch('index/register');
    }

    /**
     * 提交注册申请
     */
    public function submit(): \think\Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '非法请求']);
        }

        $params = $this->request->post();

        // 基础验证
        if (empty($params['company_name'] ?? '')) {
            return json(['code' => 0, 'msg' => '企业名称不能为空']);
        }
        if (empty($params['contact_name'] ?? '')) {
            return json(['code' => 0, 'msg' => '联系人不能为空']);
        }

        // 验证邮箱
        if (empty($params['contact_email'] ?? '')) {
            return json(['code' => 0, 'msg' => '联系邮箱不能为空']);
        }
        if (!filter_var($params['contact_email'], FILTER_VALIDATE_EMAIL)) {
            return json(['code' => 0, 'msg' => '邮箱格式不正确']);
        }

        // 验证域名
        $domain = trim($params['domain'] ?? '');
        if (empty($domain)) {
            return json(['code' => 0, 'msg' => '期望绑定域名不能为空']);
        }

        // 检查域名是否已占用
        $existTenant = \think\facade\Db::name('tenant')
            ->where('domain', 'like', '%' . $domain . '%')
            ->where('status', 1)
            ->find();
        if ($existTenant) {
            return json(['code' => 0, 'msg' => '域名已被占用']);
        }

        // 检查是否有待审核的申请
        $existRegister = \app\admin\model\TenantRegisterModel::where('contact_email', $params['contact_email'])
            ->where('status', 0)
            ->find();
        if ($existRegister) {
            return json(['code' => 0, 'msg' => '该邮箱已有待审核的申请']);
        }

        // 生成注册单号
        $registerNo = 'REG' . date('YmdHis') . rand(1000, 9999);

        $data = [
            'register_no' => $registerNo,
            'company_name' => $params['company_name'],
            'contact_name' => $params['contact_name'],
            'contact_phone' => $params['contact_phone'] ?? '',
            'contact_email' => $params['contact_email'],
            'domain' => $domain,
            'package_id' => (int)($params['package_id'] ?? 1),
            'remark' => $params['remark'] ?? '',
            'status' => 0, // 待审核
            'create_time' => time(),
        ];

        try {
            \app\admin\model\TenantRegisterModel::create($data);
            return json(['code' => 1, 'msg' => '注册申请已提交，请等待审核', 'data' => ['register_no' => $registerNo]]);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '提交失败：' . $e->getMessage()]);
        }
    }

    /**
     * 查询注册状态
     */
    public function query(): \think\Response
    {
        $registerNo = trim($this->request->get('register_no'));
        if (empty($registerNo)) {
            return json(['code' => 0, 'msg' => '注册单号不能为空']);
        }

        $register = \app\admin\model\TenantRegisterModel::where('register_no', $registerNo)->find();
        if (!$register) {
            return json(['code' => 0, 'msg' => '注册单号不存在']);
        }

        $statusMap = [0 => '待审核', 1 => '已通过', 2 => '已拒绝'];
        $data = $register->toArray();
        $data['status_text'] = $statusMap[$register->status] ?? '未知';

        return json(['code' => 1, 'data' => $data]);
    }
}
