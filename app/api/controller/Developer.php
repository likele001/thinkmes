<?php
declare(strict_types=1);

namespace app\api\controller;

use app\api\middleware\DeveloperAuth;
use app\common\controller\BaseController;
use think\facade\Db;
use think\Response;

class Developer extends BaseController
{
    public function register(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('请求方式错误');
        }
        $account = strtolower(trim((string) $this->request->post('account', '')));
        $password = (string) $this->request->post('password', '');
        $name = trim((string) $this->request->post('name', ''));

        if ($account === '' || !preg_match('/^[a-z0-9][a-z0-9._-]{2,40}$/', $account)) {
            return $this->error('账号不合法');
        }
        
        // 增强密码策略：至少12位，包含大小写字母、数字和特殊字符
        if (strlen($password) < 12) {
            return $this->error('密码至少需要 12 位');
        }
        if (!preg_match('/[a-z]/', $password)) {
            return $this->error('密码必须包含小写字母');
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return $this->error('密码必须包含大写字母');
        }
        if (!preg_match('/[0-9]/', $password)) {
            return $this->error('密码必须包含数字');
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return $this->error('密码必须包含特殊字符');
        }
        
        if ($name === '') {
            $name = $account;
        }

        $exists = Db::name('market_developer')->where('account', $account)->find();
        if ($exists) {
            return $this->error('账号已存在');
        }

        $now = time();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $id = (int) Db::name('market_developer')->insertGetId([
            'account' => $account,
            'name' => $name,
            'password_hash' => $hash,
            'status' => 1,
            'create_time' => $now,
            'update_time' => $now,
            'last_login_time' => 0,
            'last_login_ip' => '',
        ]);

        $token = DeveloperAuth::makeToken($id);
        $resp = $this->success('注册成功', ['token' => $token, 'developer_id' => $id, 'name' => $name, 'account' => $account]);
        // 设置安全的Cookie标志
        $resp->cookie('dev_token', urlencode($token), [
            'expire' => DeveloperAuth::TTL, 
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict'
        ]);
        return $resp;
    }

    public function login(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('请求方式错误');
        }
        $account = strtolower(trim((string) $this->request->post('account', '')));
        $password = (string) $this->request->post('password', '');
        if ($account === '' || $password === '') {
            return $this->error('账号或密码不能为空');
        }

        $dev = Db::name('market_developer')->where('account', $account)->find();
        if (!$dev || (int) ($dev['status'] ?? 0) !== 1) {
            return $this->error('账号不存在或已禁用');
        }
        $hash = (string) ($dev['password_hash'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            return $this->error('账号或密码错误');
        }

        $token = DeveloperAuth::makeToken((int) $dev['id']);
        $now = time();
        Db::name('market_developer')->where('id', (int) $dev['id'])->update([
            'last_login_time' => $now,
            'last_login_ip' => (string) $this->request->ip(),
            'update_time' => $now,
        ]);

        $resp = $this->success('登录成功', ['token' => $token, 'developer_id' => (int) $dev['id'], 'name' => (string) ($dev['name'] ?? $account), 'account' => $account]);
        // 设置安全的Cookie标志
        $resp->cookie('dev_token', urlencode($token), [
            'expire' => DeveloperAuth::TTL, 
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict'
        ]);
        return $resp;
    }

    public function profile(): Response
    {
        $dev = $this->request->developerInfo ?? null;
        if (!is_array($dev)) {
            return $this->error('请先登录开发者中心', 401);
        }
        return $this->success('', [
            'developer_id' => (int) ($dev['id'] ?? 0),
            'account' => (string) ($dev['account'] ?? ''),
            'name' => (string) ($dev['name'] ?? ''),
            'create_time' => (int) ($dev['create_time'] ?? 0),
            'last_login_time' => (int) ($dev['last_login_time'] ?? 0),
        ]);
    }

    public function logout(): Response
    {
        $token = (string) ($this->request->cookie('dev_token') ?? '');
        if ($token !== '') {
            $token = urldecode($token);
            DeveloperAuth::invalidateToken($token);
        }
        $resp = $this->success('已退出');
        $resp->cookie('dev_token', '', -86400);
        return $resp;
    }
}
