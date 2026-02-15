<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\model\UserModel;
use app\api\middleware\UserAuth;
use app\admin\model\ConfigModel;
use think\facade\Cache;
use think\facade\Session;
use think\Response;

/**
 * C端用户：注册、登录、找回密码、个人资料
 */
class User extends BaseController
{
    /** 找回密码验证码缓存前缀，5 分钟有效 */
    private const FORGOT_CODE_PREFIX = 'user_forgot:';
    private const FORGOT_CODE_TTL = 300;

    protected function getCaptchaMode(): string
    {
        $mode = ConfigModel::where('group', 'safe')->where('name', 'front_captcha_mode')->value('value');
        $mode = is_string($mode) ? strtolower(trim($mode)) : '';
        if (!in_array($mode, ['image', 'slider', 'off'], true)) {
            $mode = 'image';
        }
        return $mode;
    }

    /**
     * 获取当前租户ID（与 admin 一致：header 或域名解析）
     */
    protected function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    /**
     * 注册
     * POST username, password, [nickname, mobile, email]
     */
    public function register(): Response
    {
        $mode = $this->getCaptchaMode();
        if ($mode === 'image') {
            $captchaInput = strtolower(trim((string) $this->request->post('captcha', '')));
            $captchaStored = strtolower((string) Session::get('user_captcha_register', ''));
            if ($captchaStored !== '' && $captchaInput === '') {
                return $this->error('请输入验证码');
            }
            if ($captchaStored !== '' && $captchaInput !== $captchaStored) {
                return $this->error('验证码错误');
            }
        } elseif ($mode === 'slider') {
            $sliderOk = (int) $this->request->post('slider_ok', 0);
            if ($sliderOk !== 1) {
                return $this->error('请完成滑动验证');
            }
        }

        // 检查用户数限制
        $resourceCheck = $this->checkResourceLimit('user');
        if (!$resourceCheck['allowed']) {
            return $this->error($resourceCheck['msg']);
        }

        $tenantId = $this->getTenantId();
        $username = trim((string) $this->request->post('username', ''));
        $password = (string) $this->request->post('password', '');
        $nickname = trim((string) $this->request->post('nickname', ''));
        $mobile   = trim((string) $this->request->post('mobile', ''));
        $email    = trim((string) $this->request->post('email', ''));

        if (strlen($username) < 2 || strlen($username) > 50) {
            return $this->error('用户名长度为 2-50 位');
        }
        if (!preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]+$/u', $username)) {
            return $this->error('用户名仅支持字母、数字、下划线、中文');
        }
        if (strlen($password) < 6 || strlen($password) > 32) {
            return $this->error('密码长度为 6-32 位');
        }

        $exists = UserModel::where('tenant_id', $tenantId)->where('username', $username)->find();
        if ($exists) {
            return $this->error('该用户名已被注册');
        }
        if ($mobile !== '' && UserModel::where('tenant_id', $tenantId)->where('mobile', $mobile)->find()) {
            return $this->error('该手机号已被注册');
        }
        if ($email !== '' && UserModel::where('tenant_id', $tenantId)->where('email', $email)->find()) {
            return $this->error('该邮箱已被注册');
        }

        $now = time();
        $user = new UserModel();
        $user->tenant_id   = $tenantId;
        $user->username    = $username;
        $user->password    = $password;
        $user->nickname    = $nickname !== '' ? $nickname : $username;
        $user->mobile      = $mobile;
        $user->email       = $email;
        $user->status      = 1;
        $user->create_time = $now;
        $user->update_time = $now;
        $user->save();

        if (($mode ?? '') === 'image') {
            $captchaStored = Session::get('user_captcha_register');
            if ($captchaStored !== null && $captchaStored !== '') {
                Session::delete('user_captcha_register');
            }
        }

        $token = UserAuth::makeToken((int) $user->id, $tenantId);
        $out   = $user->toArray();
        unset($out['password']);
        $out['token'] = $token;
        return $this->success('注册成功', $out);
    }

    /**
     * 登录
     * POST username, password  或  mobile, password
     */
    public function login(): Response
    {
        $mode = $this->getCaptchaMode();
        if ($mode === 'image') {
            $captchaInput = strtolower(trim((string) $this->request->post('captcha', '')));
            $captchaStored = strtolower((string) Session::get('user_captcha_login', ''));
            if ($captchaStored !== '' && $captchaInput === '') {
                return $this->error('请输入验证码');
            }
            if ($captchaStored !== '' && $captchaInput !== $captchaStored) {
                return $this->error('验证码错误');
            }
        } elseif ($mode === 'slider') {
            $sliderOk = (int) $this->request->post('slider_ok', 0);
            if ($sliderOk !== 1) {
                return $this->error('请完成滑动验证');
            }
        }

        $tenantId = $this->getTenantId();
        $username = trim((string) $this->request->post('username', ''));
        $mobile   = trim((string) $this->request->post('mobile', ''));
        $password = (string) $this->request->post('password', '');

        if (strlen($password) < 6 || strlen($password) > 32) {
            return $this->error('密码长度为 6-32 位');
        }
        if ($username === '' && $mobile === '') {
            return $this->error('请填写用户名或手机号');
        }

        $query = UserModel::where('tenant_id', $tenantId)->where('status', 1);
        if ($username !== '') {
            $query->where('username', $username);
        } else {
            $query->where('mobile', $mobile);
        }
        $user = $query->find();
        if (!$user) {
            return $this->error('账号不存在或已禁用');
        }
        if (!password_verify($password, $user->password)) {
            return $this->error('密码错误');
        }

        $user->login_time = time();
        $user->login_ip   = $this->request->ip();
        $user->save();

        if (($mode ?? '') === 'image') {
            $captchaStored = Session::get('user_captcha_login');
            if ($captchaStored !== null && $captchaStored !== '') {
                Session::delete('user_captcha_login');
            }
        }

        $token = UserAuth::makeToken((int) $user->id, $tenantId);
        $out   = $user->toArray();
        unset($out['password']);
        $out['token'] = $token;
        return $this->success('登录成功', $out);
    }

    public function captcha(): Response
    {
        $scene = strtolower((string) $this->request->get('scene', 'login'));
        if ($scene !== 'register') {
            $scene = 'login';
        }
        $code = (string) mt_rand(1000, 9999);
        Session::set('user_captcha_' . $scene, $code);

        $width = 120;
        $height = 40;
        $image = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($image, 248, 250, 252);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);
        $textColor = imagecolorallocate($image, 55, 65, 81);
        $accent = imagecolorallocate($image, 129, 140, 248);
        for ($i = 0; $i < 40; $i++) {
            $x = mt_rand(0, $width);
            $y = mt_rand(0, $height);
            imagesetpixel($image, $x, $y, $accent);
        }
        imagestring($image, 5, 28, 12, $code, $textColor);

        ob_start();
        imagepng($image);
        $data = ob_get_clean();

        return response($data, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function captchaMode(): Response
    {
        $mode = $this->getCaptchaMode();
        return $this->success('', ['mode' => $mode]);
    }

    /**
     * 个人资料（需登录）
     */
    public function profile(): Response
    {
        $info = $this->request->userInfo ?? [];
        if (empty($info)) {
            return $this->error('请先登录', 0);
        }
        unset($info['password']);
        return $this->success('', $info);
    }

    /**
     * 登出（需登录，使当前 token 失效）
     */
    public function logout(): Response
    {
        $token = $this->request->header('Authorization');
        if ($token !== null && $token !== '') {
            $token = preg_replace('/^Bearer\s+/i', '', trim($token));
        }
        if (empty($token)) {
            $token = (string) $this->request->get('token', '');
        }
        if ($token !== '') {
            UserAuth::invalidateToken($token);
        }
        return $this->success('已退出登录');
    }

    /**
     * 找回密码 - 发送验证码（按手机或邮箱）
     * POST mobile 或 email
     * 实际项目可接短信/邮件服务；此处仅生成验证码存缓存，接口返回 code 仅用于演示/测试
     */
    public function forgot(): Response
    {
        $tenantId = $this->getTenantId();
        $mobile   = trim((string) $this->request->post('mobile', ''));
        $email    = trim((string) $this->request->post('email', ''));

        if ($mobile === '' && $email === '') {
            return $this->error('请填写手机号或邮箱');
        }

        $query = UserModel::where('tenant_id', $tenantId)->where('status', 1);
        if ($mobile !== '') {
            $query->where('mobile', $mobile);
        } else {
            $query->where('email', $email);
        }
        $user = $query->find();
        if (!$user) {
            return $this->error('该账号不存在或已禁用');
        }

        $code = (string) mt_rand(100000, 999999);
        $key  = self::FORGOT_CODE_PREFIX . ($mobile !== '' ? 'm_' . $mobile : 'e_' . $email) . '_' . $tenantId;
        Cache::set($key, ['code' => $code, 'user_id' => $user->id], self::FORGOT_CODE_TTL);

        // TODO: 实际发送短信/邮件。此处仅返回 code 便于测试
        $data = ['expire_seconds' => self::FORGOT_CODE_TTL];
        if (env('APP_DEBUG', false)) {
            $data['debug_code'] = $code;
        }
        return $this->success('验证码已发送', $data);
    }

    /**
     * 找回密码 - 重置
     * POST mobile 或 email, code, password
     */
    public function resetPassword(): Response
    {
        $tenantId = $this->getTenantId();
        $mobile   = trim((string) $this->request->post('mobile', ''));
        $email    = trim((string) $this->request->post('email', ''));
        $code     = trim((string) $this->request->post('code', ''));
        $password = (string) $this->request->post('password', '');

        if ($mobile === '' && $email === '') {
            return $this->error('请填写手机号或邮箱');
        }
        if (strlen($code) !== 6) {
            return $this->error('验证码格式错误');
        }
        if (strlen($password) < 6 || strlen($password) > 32) {
            return $this->error('密码长度为 6-32 位');
        }

        $key = self::FORGOT_CODE_PREFIX . ($mobile !== '' ? 'm_' . $mobile : 'e_' . $email) . '_' . $tenantId;
        $payload = Cache::get($key);
        if (!$payload || !is_array($payload) || ($payload['code'] ?? '') !== $code) {
            return $this->error('验证码错误或已过期');
        }

        $userId = (int) ($payload['user_id'] ?? 0);
        $user   = UserModel::where('id', $userId)->where('tenant_id', $tenantId)->find();
        if (!$user) {
            Cache::delete($key);
            return $this->error('用户不存在');
        }

        $user->password    = $password;
        $user->update_time = time();
        $user->save();
        Cache::delete($key);

        return $this->success('密码已重置，请使用新密码登录');
    }

    /**
     * 修改个人资料（需登录）
     * POST nickname, mobile, email, avatar
     */
    public function updateProfile(): Response
    {
        $info = $this->request->userInfo ?? [];
        if (empty($info)) {
            return $this->error('请先登录', 401);
        }
        $userId   = (int) ($this->request->userId ?? 0);
        $tenantId = (int) ($this->request->tenantId ?? 0);

        $nickname = trim((string) $this->request->post('nickname', ''));
        $mobile   = trim((string) $this->request->post('mobile', ''));
        $email    = trim((string) $this->request->post('email', ''));
        $avatar   = trim((string) $this->request->post('avatar', ''));

        $user = UserModel::where('id', $userId)->where('tenant_id', $tenantId)->find();
        if (!$user) {
            return $this->error('用户不存在');
        }

        if ($mobile !== '' && $mobile !== $user->mobile) {
            if (UserModel::where('tenant_id', $tenantId)->where('mobile', $mobile)->where('id', '<>', $userId)->find()) {
                return $this->error('该手机号已被使用');
            }
            $user->mobile = $mobile;
        }
        if ($email !== '' && $email !== $user->email) {
            if (UserModel::where('tenant_id', $tenantId)->where('email', $email)->where('id', '<>', $userId)->find()) {
                return $this->error('该邮箱已被使用');
            }
            $user->email = $email;
        }
        if ($nickname !== '') {
            $user->nickname = $nickname;
        }
        if ($avatar !== '') {
            $user->avatar = $avatar;
        }
        $user->update_time = time();
        $user->save();

        $out = $user->toArray();
        unset($out['password']);
        return $this->success('保存成功', $out);
    }

    /**
     * 修改密码（需登录）
     * POST old_password, new_password
     */
    public function changePassword(): Response
    {
        $info = $this->request->userInfo ?? [];
        if (empty($info)) {
            return $this->error('请先登录', 401);
        }
        $userId = (int) ($this->request->userId ?? 0);
        $tenantId = (int) ($this->request->tenantId ?? 0);

        $oldPwd = (string) $this->request->post('old_password', '');
        $newPwd = (string) $this->request->post('new_password', '');

        if (strlen($newPwd) < 6 || strlen($newPwd) > 32) {
            return $this->error('新密码长度为 6-32 位');
        }
        $user = UserModel::where('id', $userId)->where('tenant_id', $tenantId)->find();
        if (!$user || !password_verify($oldPwd, $user->password)) {
            return $this->error('原密码错误');
        }

        $user->password    = $newPwd;
        $user->update_time = time();
        $user->save();
        return $this->success('密码已修改，请重新登录');
    }
}
