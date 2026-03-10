<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\model\UserModel;
use app\common\model\UserMiniappModel;
use app\admin\model\TenantMiniappModel;
use app\api\middleware\UserAuth;
use think\Response;

/**
 * 小程序登录 / 绑定（按租户隔离）
 * 默认实现微信小程序 code2session
 */
class Miniapp extends BaseController
{
    protected function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    /**
     * 根据小程序 AppID 获取租户配置（用于小程序启动时确定 tenant_id）
     * GET: appid=xxx 或 POST appid
     * 返回 tenant_id、name（来自后台「租户小程序配置」）
     */
    public function getConfig(): Response
    {
        $appId = trim((string) ($this->request->get('appid', '') ?: $this->request->post('appid', '')));
        if ($appId === '') {
            return $this->error('appid 不能为空');
        }
        $row = TenantMiniappModel::where('app_id', $appId)
            ->where('type', 'wechat')
            ->where('status', 1)
            ->field('tenant_id,name')
            ->find();
        if (!$row) {
            return $this->error('未找到该小程序对应的租户配置，请在后台「租户小程序配置」中填写本小程序的 AppID');
        }
        return $this->success('', [
            'tenant_id' => (int) $row->tenant_id,
            'name'      => (string) ($row->name ?? ''),
        ]);
    }

    /**
     * 小程序登录（自动注册或关联用户）
     * POST: code, [tenant_id], [nickname, avatar]
     * tenant_id 可由小程序先调 getConfig 根据 appid 获得
     */
    public function login(): Response
    {
        $tenantId = (int) $this->request->post('tenant_id', 0);
        if ($tenantId <= 0) {
            $tenantId = $this->getTenantId();
        }
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }

        $code = trim((string) $this->request->post('code', ''));
        if ($code === '') {
            return $this->error('code 不能为空');
        }

        $miniapp = TenantMiniappModel::where('tenant_id', $tenantId)
            ->where('type', 'wechat')
            ->where('status', 1)
            ->find();
        if (!$miniapp) {
            return $this->error('当前租户未配置小程序信息');
        }

        $appId = (string) $miniapp['app_id'];
        $appSecret = (string) $miniapp['app_secret'];
        if ($appId === '' || $appSecret === '') {
            return $this->error('小程序 AppID 或 AppSecret 未配置');
        }

        $wx = $this->code2session($appId, $appSecret, $code);
        if (!$wx['success']) {
            return $this->error($wx['msg']);
        }
        $openid = (string) ($wx['data']['openid'] ?? '');
        $unionid = (string) ($wx['data']['unionid'] ?? '');
        $sessionKey = (string) ($wx['data']['session_key'] ?? '');
        if ($openid === '') {
            return $this->error('未获取到 openid');
        }

        $bind = UserMiniappModel::where('tenant_id', $tenantId)
            ->where('type', 'wechat')
            ->where('openid', $openid)
            ->find();

        $now = time();
        if ($bind) {
            $user = UserModel::where('id', (int) $bind['user_id'])
                ->where('tenant_id', $tenantId)
                ->where('status', 1)
                ->find();
            if (!$user) {
                return $this->error('绑定用户不存在或已禁用');
            }
            $bind->session_key = $sessionKey;
            $bind->unionid = $unionid;
            $bind->last_login_time = $now;
            $bind->update_time = $now;
            $bind->save();

            $token = UserAuth::makeToken((int) $user->id, $tenantId);
            $out = $user->toArray();
            unset($out['password']);
            $out['token'] = $token;
            return $this->success('登录成功', $out);
        }

        // 未绑定：要求绑定用户中心已有员工，不自动建新用户
        return $this->success('请绑定员工账号', ['need_bind' => true]);
    }

    /**
     * 小程序绑定已有员工（用户中心账号+密码）
     * POST: code, tenant_id, username, password
     * 校验员工后建立 openid -> user_id 绑定并返回 token
     */
    public function bindWithEmployee(): Response
    {
        $tenantId = (int) $this->request->post('tenant_id', 0);
        if ($tenantId <= 0) {
            $tenantId = $this->getTenantId();
        }
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }

        $code = trim((string) $this->request->post('code', ''));
        $username = trim((string) $this->request->post('username', ''));
        $password = trim((string) $this->request->post('password', ''));
        if ($code === '' || $username === '' || $password === '') {
            return $this->error('请提供 code、用户名和密码');
        }

        $miniapp = TenantMiniappModel::where('tenant_id', $tenantId)
            ->where('type', 'wechat')
            ->where('status', 1)
            ->find();
        if (!$miniapp) {
            return $this->error('当前租户未配置小程序信息');
        }

        $appId = (string) $miniapp['app_id'];
        $appSecret = (string) $miniapp['app_secret'];
        if ($appId === '' || $appSecret === '') {
            return $this->error('小程序 AppID 或 AppSecret 未配置');
        }

        $wx = $this->code2session($appId, $appSecret, $code);
        if (!$wx['success']) {
            return $this->error($wx['msg']);
        }
        $openid = (string) ($wx['data']['openid'] ?? '');
        $unionid = (string) ($wx['data']['unionid'] ?? '');
        $sessionKey = (string) ($wx['data']['session_key'] ?? '');
        if ($openid === '') {
            return $this->error('未获取到 openid');
        }

        $user = UserModel::where('tenant_id', $tenantId)
            ->where('username', $username)
            ->where('status', 1)
            ->find();
        if (!$user) {
            return $this->error('用户不存在或已禁用');
        }
        $passHash = (string) $user->getData('password');
        if ($passHash === '' || !password_verify($password, $passHash)) {
            return $this->error('密码错误');
        }

        $userId = (int) $user->id;
        $now = time();
        $bind = UserMiniappModel::where('tenant_id', $tenantId)
            ->where('type', 'wechat')
            ->where('openid', $openid)
            ->find();
        if ($bind && (int) $bind['user_id'] !== $userId) {
            return $this->error('该微信已绑定其他员工，请更换微信或联系管理员');
        }

        if ($bind) {
            $bind->session_key = $sessionKey;
            $bind->unionid = $unionid;
            $bind->last_login_time = $now;
            $bind->update_time = $now;
            $bind->save();
        } else {
            $bind = new UserMiniappModel();
            $bind->tenant_id = $tenantId;
            $bind->user_id = $userId;
            $bind->type = 'wechat';
            $bind->app_id = $appId;
            $bind->openid = $openid;
            $bind->unionid = $unionid;
            $bind->session_key = $sessionKey;
            $bind->last_login_time = $now;
            $bind->create_time = $now;
            $bind->update_time = $now;
            $bind->save();
        }

        $token = UserAuth::makeToken($userId, $tenantId);
        $out = $user->toArray();
        unset($out['password']);
        $out['token'] = $token;
        return $this->success('绑定成功', $out);
    }

    /**
     * 已登录用户绑定/更新小程序
     * 需要在路由或中间件中引入 UserAuth
     */
    public function bind(): Response
    {
        $tenantId = $this->getTenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $userInfo = $this->request->userInfo ?? [];
        if (empty($userInfo) || empty($userInfo['id'])) {
            return $this->error('请先登录');
        }
        $userId = (int) $userInfo['id'];

        $code = trim((string) $this->request->post('code', ''));
        if ($code === '') {
            return $this->error('code 不能为空');
        }

        $miniapp = TenantMiniappModel::where('tenant_id', $tenantId)
            ->where('type', 'wechat')
            ->where('status', 1)
            ->find();
        if (!$miniapp) {
            return $this->error('当前租户未配置小程序信息');
        }

        $appId = (string) $miniapp['app_id'];
        $appSecret = (string) $miniapp['app_secret'];
        if ($appId === '' || $appSecret === '') {
            return $this->error('小程序 AppID 或 AppSecret 未配置');
        }

        $wx = $this->code2session($appId, $appSecret, $code);
        if (!$wx['success']) {
            return $this->error($wx['msg']);
        }
        $openid = (string) ($wx['data']['openid'] ?? '');
        $unionid = (string) ($wx['data']['unionid'] ?? '');
        $sessionKey = (string) ($wx['data']['session_key'] ?? '');
        if ($openid === '') {
            return $this->error('未获取到 openid');
        }

        $now = time();
        $bind = UserMiniappModel::where('tenant_id', $tenantId)
            ->where('type', 'wechat')
            ->where('openid', $openid)
            ->find();
        if ($bind && (int) $bind['user_id'] !== $userId) {
            return $this->error('该小程序账号已绑定其他用户');
        }

        if ($bind) {
            $bind->session_key = $sessionKey;
            $bind->unionid = $unionid;
            $bind->last_login_time = $now;
            $bind->update_time = $now;
            $bind->save();
        } else {
            $bind = new UserMiniappModel();
            $bind->tenant_id = $tenantId;
            $bind->user_id = $userId;
            $bind->type = 'wechat';
            $bind->app_id = $appId;
            $bind->openid = $openid;
            $bind->unionid = $unionid;
            $bind->session_key = $sessionKey;
            $bind->last_login_time = $now;
            $bind->create_time = $now;
            $bind->update_time = $now;
            $bind->save();
        }

        return $this->success('绑定成功');
    }

    /**
     * 调用微信 code2session
     */
    protected function code2session(string $appId, string $appSecret, string $code): array
    {
        $url = 'https://api.weixin.qq.com/sns/jscode2session'
            . '?appid=' . urlencode($appId)
            . '&secret=' . urlencode($appSecret)
            . '&js_code=' . urlencode($code)
            . '&grant_type=authorization_code';

        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
            ],
        ]);
        $resp = @file_get_contents($url, false, $context);
        if ($resp === false) {
            return ['success' => false, 'msg' => '请求微信接口失败', 'data' => []];
        }
        $data = json_decode($resp, true);
        if (!is_array($data)) {
            return ['success' => false, 'msg' => '微信返回格式错误', 'data' => []];
        }
        if (isset($data['errcode']) && (int) $data['errcode'] !== 0) {
            $msg = isset($data['errmsg']) ? (string) $data['errmsg'] : '微信接口错误';
            return ['success' => false, 'msg' => $msg, 'data' => $data];
        }
        return ['success' => true, 'msg' => '', 'data' => $data];
    }
}

