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
     * 小程序登录（自动注册或关联用户）
     * POST: code, [nickname, avatar]
     */
    public function login(): Response
    {
        $tenantId = $this->getTenantId();
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
        } else {
            $nickname = trim((string) $this->request->post('nickname', ''));
            $avatar = trim((string) $this->request->post('avatar', ''));

            $baseUsername = $nickname !== '' ? $nickname : ('wx_' . substr($openid, -6));
            $username = $baseUsername;
            $i = 1;
            while (UserModel::where('tenant_id', $tenantId)->where('username', $username)->find()) {
                $username = $baseUsername . $i;
                $i++;
            }

            $user = new UserModel();
            $user->tenant_id = $tenantId;
            $user->username = $username;
            $user->password = bin2hex(random_bytes(8));
            $user->nickname = $nickname !== '' ? $nickname : $username;
            $user->avatar = $avatar;
            $user->status = 1;
            $user->create_time = $now;
            $user->update_time = $now;
            $user->save();

            $bind = new UserMiniappModel();
            $bind->tenant_id = $tenantId;
            $bind->user_id = (int) $user->id;
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

        $token = UserAuth::makeToken((int) $user->id, $tenantId);
        $out = $user->toArray();
        unset($out['password']);
        $out['token'] = $token;
        return $this->success('登录成功', $out);
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

