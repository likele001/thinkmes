<?php
declare(strict_types=1);

namespace app\install\controller;

use think\facade\View;
use think\facade\Session;
use think\Response;

/**
 * 安装向导（基础后台，不含 MES）
 * 步骤：同意条款 → 环境检测 → 数据库配置 → 管理员账户 → 完成
 */
class Index
{
    /** 安装锁文件路径 */
    protected string $lockFile = '';

    public function __construct()
    {
        $this->lockFile = runtime_path() . 'install.lock';
    }

    /**
     * 若已安装则跳转后台登录（除非强制重装）
     */
    protected function checkInstalled(): ?Response
    {
        if (request()->get('reinstall') === '1') {
            return null;
        }
        if (is_file($this->lockFile)) {
            $entry = $this->getAdminEntryFromEnv();
            $url = $entry !== '' ? '/' . $entry . '/index/login' : (string) url('/admin/index/login');
            return redirect($url);
        }
        return null;
    }

    /**
     * 步骤一：同意条款
     */
    public function index(): string|Response
    {
        $res = $this->checkInstalled();
        if ($res !== null) {
            return $res;
        }
        View::assign('title', '安装协议');
        View::assign('step', 1);
        return View::fetch('index/index');
    }

    /**
     * 步骤二：环境检测（页面展示 + 可 AJAX 获取检测结果）
     */
    public function step2(): string|Response
    {
        $res = $this->checkInstalled();
        if ($res !== null) {
            return $res;
        }
        if (request()->isAjax()) {
            return $this->envCheckJson();
        }
        View::assign('title', '环境检测');
        View::assign('step', 2);
        View::assign('env', $this->getEnvCheck());
        return View::fetch('index/index');
    }

    /**
     * 步骤三：数据库配置（GET 展示表单，POST 为测试连接）
     */
    public function step3(): string|Response
    {
        $res = $this->checkInstalled();
        if ($res !== null) {
            return $res;
        }
        if (request()->isPost()) {
            $act = request()->post('_action', '');
            if ($act === 'next') {
                return $this->step3Next();
            }
            return $this->testDatabase();
        }
        View::assign('title', '数据库配置');
        View::assign('step', 3);
        View::assign('install_db', Session::get('install_db', []));
        View::assign('error', request()->get('error', ''));
        View::assign('has_tables', request()->get('has_tables', ''));
        return View::fetch('index/index');
    }

    /** 保存数据库配置到 session 并跳转步骤四 */
    protected function step3Next(): Response
    {
        $data = [
            'db_host'    => trim((string) request()->post('db_host', '127.0.0.1')),
            'db_port'    => (int) request()->post('db_port', 3306),
            'db_name'    => trim((string) request()->post('db_name', '')),
            'db_user'    => trim((string) request()->post('db_user', 'root')),
            'db_pass'    => (string) request()->post('db_pass', ''),
            'db_prefix'  => trim((string) request()->post('db_prefix', 'fa_')),
            'db_charset' => trim((string) request()->post('db_charset', 'utf8mb4')),
        ];
        if ($data['db_name'] === '') {
            return redirect((string) url('/install/index/step3', ['error' => '请填写数据库名']));
        }
        $confirmOverwrite = request()->post('confirm_overwrite') === '1' || request()->post('confirm_overwrite') === 'on';
        $tableCount = $this->getDatabaseTableCount($data);
        if ($tableCount > 0 && !$confirmOverwrite) {
            return redirect((string) url('/install/index/step3', [
                'error'       => '检测到数据库已有 ' . $tableCount . ' 张表，安装将执行 DROP TABLE 并覆盖。请勾选「确认覆盖安装」后继续。',
                'has_tables'  => '1',
            ]));
        }
        $data['overwrite_confirmed'] = $tableCount > 0;
        Session::set('install_db', $data);
        return redirect((string) url('/install/index/step4'));
    }

    /** 检测数据库表数量（连接失败返回 0） */
    protected function getDatabaseTableCount(array $db): int
    {
        try {
            $dsn = "mysql:host={$db['db_host']};port={$db['db_port']};dbname=" . str_replace('`', '``', $db['db_name']) . ";charset={$db['db_charset']}";
            $pdo = new \PDO($dsn, $db['db_user'], $db['db_pass'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = " . $pdo->quote($db['db_name']));
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * 步骤四：管理员账户（GET 展示，POST 保存并跳转步骤五）
     */
    public function step4(): string|Response
    {
        $res = $this->checkInstalled();
        if ($res !== null) {
            return $res;
        }
        if (request()->isPost()) {
            return $this->step4Next();
        }
        View::assign('title', '管理员账户');
        View::assign('step', 4);
        View::assign('install_admin', Session::get('install_admin', []));
        View::assign('error', request()->get('error', ''));
        return View::fetch('index/index');
    }

    /** 保存管理员信息到 session 并跳转步骤五 */
    protected function step4Next(): Response
    {
        $username = trim((string) request()->post('admin_username', ''));
        $password = (string) request()->post('admin_password', '');
        $nickname = trim((string) request()->post('admin_nickname', '管理员'));
        if (strlen($username) < 2 || strlen($username) > 50) {
            return redirect((string) url('/install/index/step4', ['error' => '管理员账号长度为 2-50 个字符']));
        }
        if (strlen($password) < 6 || strlen($password) > 32) {
            return redirect((string) url('/install/index/step4', ['error' => '密码长度为 6-32 位']));
        }
        Session::set('install_admin', [
            'admin_username'  => $username,
            'admin_password'  => $password,
            'admin_nickname'  => $nickname,
        ]);
        return redirect((string) url('/install/index/step5'));
    }

    /**
     * 步骤五：确认并执行安装
     */
    public function step5(): string|Response
    {
        $res = $this->checkInstalled();
        if ($res !== null) {
            return $res;
        }
        $db = Session::get('install_db', []);
        $admin = Session::get('install_admin', []);
        if (empty($db) || empty($admin)) {
            return redirect((string) url('/install/index/index'));
        }
        View::assign('title', '完成安装');
        View::assign('step', 5);
        View::assign('install_db', $db);
        View::assign('install_admin', $admin);
        return View::fetch('index/index');
    }

    /**
     * 步骤五：执行安装（写入 .env、导入 SQL、创建管理员、写锁）
     */
    public function install(): Response
    {
        $res = $this->checkInstalled();
        if ($res !== null) {
            return $res;
        }
        if (!request()->isPost()) {
            return json(['code' => 0, 'msg' => '请使用 POST 提交']);
        }

        $db = Session::get('install_db', []);
        $admin = Session::get('install_admin', []);
        $dbHost = trim((string) (request()->post('db_host') ?? $db['db_host'] ?? '127.0.0.1'));
        $dbPort = (int) (request()->post('db_port') ?? $db['db_port'] ?? 3306);
        $dbName = trim((string) (request()->post('db_name') ?? $db['db_name'] ?? ''));
        $dbUser = trim((string) (request()->post('db_user') ?? $db['db_user'] ?? 'root'));
        $dbPass = (string) (request()->post('db_pass') ?? $db['db_pass'] ?? '');
        $dbPrefix = trim((string) (request()->post('db_prefix') ?? $db['db_prefix'] ?? 'fa_'));
        $dbCharset = trim((string) (request()->post('db_charset') ?? $db['db_charset'] ?? 'utf8mb4'));
        $adminUser = trim((string) (request()->post('admin_username') ?? $admin['admin_username'] ?? ''));
        $adminPass = (string) (request()->post('admin_password') ?? $admin['admin_password'] ?? '');
        $adminNickname = trim((string) (request()->post('admin_nickname') ?? $admin['admin_nickname'] ?? '管理员'));

        if (strlen($adminUser) < 2 || strlen($adminUser) > 50) {
            return json(['code' => 0, 'msg' => '管理员账号长度为 2-50 个字符']);
        }
        if (strlen($adminPass) < 6 || strlen($adminPass) > 32) {
            return json(['code' => 0, 'msg' => '管理员密码长度为 6-32 位']);
        }
        if ($dbName === '') {
            return json(['code' => 0, 'msg' => '请填写数据库名']);
        }

        $dbConfig = [
            'db_host' => $dbHost, 'db_port' => $dbPort, 'db_name' => $dbName,
            'db_user' => $dbUser, 'db_pass' => $dbPass, 'db_charset' => $dbCharset,
        ];
        $tableCount = $this->getDatabaseTableCount($dbConfig);
        $overwriteConfirmed = (bool) (Session::get('install_db')['overwrite_confirmed'] ?? false);
        if ($tableCount > 0 && !$overwriteConfirmed) {
            return json(['code' => 0, 'msg' => '检测到数据库已有 ' . $tableCount . ' 张表，请返回步骤三勾选「确认覆盖安装」后再执行安装。']);
        }

        try {
            // 1. 生成随机后台路径并写入 .env（不创建 .php 文件，全部走 index.php）
            $adminEntry = $this->createAdminEntry();
            $this->writeEnv($dbHost, $dbPort, $dbName, $dbUser, $dbPass, $dbPrefix, $dbCharset, $adminEntry);
            // 2. 使用当前参数连接数据库并建表（不依赖已缓存的配置）
            $pdo = $this->runSql($dbHost, $dbPort, $dbName, $dbUser, $dbPass, $dbCharset, $dbPrefix);
            // 3. 创建/更新超级管理员
            $this->createAdmin($pdo, $dbPrefix, $adminUser, $adminPass, $adminNickname);
            // 4. 写锁
            file_put_contents($this->lockFile, date('Y-m-d H:i:s') . ' installed');
            Session::delete('install_db');
            Session::delete('install_admin');
        } catch (\Throwable $e) {
            return json(['code' => 0, 'msg' => '安装失败：' . $e->getMessage()]);
        }

        $adminUrl = '/' . $adminEntry . '/index/login';
        return json(['code' => 1, 'msg' => '安装成功', 'url' => $adminUrl, 'admin_entry' => $adminEntry]);
    }

    /**
     * 环境检测结果（用于页面或 AJAX）
     */
    protected function getEnvCheck(): array
    {
        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, '8.0.0', '>=');
        $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'fileinfo'];
        $extList = [];
        foreach ($extensions as $ext) {
            $loaded = extension_loaded($ext);
            $extList[] = ['name' => $ext, 'ok' => $loaded];
        }
        $allExtOk = !in_array(false, array_column($extList, 'ok'), true);

        $dirs = [
            'runtime' => runtime_path(),
            'config'  => config_path(),
        ];
        $root = root_path();
        $dirList = [];
        foreach ($dirs as $label => $dir) {
            $writable = is_dir($dir) && is_writable($dir);
            if (!$writable && !is_dir($dir)) {
                @mkdir($dir, 0755, true);
                $writable = is_dir($dir) && is_writable($dir);
            }
            $dirList[] = ['name' => $label, 'path' => $dir, 'ok' => $writable];
        }
        $envFile = $root . '.env';
        $envWritable = !is_file($envFile) || is_writable($envFile);
        $dirList[] = ['name' => '.env', 'path' => $envFile, 'ok' => $envWritable];

        $allOk = $phpOk && $allExtOk && $envWritable;
        foreach ($dirList as $d) {
            if (!$d['ok']) {
                $allOk = false;
                break;
            }
        }

        return [
            'php_version' => $phpVersion,
            'php_ok'      => $phpOk,
            'extensions'  => $extList,
            'dirs'        => $dirList,
            'all_ok'      => $allOk,
        ];
    }

    protected function envCheckJson(): Response
    {
        return json(['code' => 1, 'data' => $this->getEnvCheck()]);
    }

    /**
     * 测试数据库连接（不写文件，仅连接 + 可选建库），并返回是否已有表
     */
    protected function testDatabase(): Response
    {
        $dbHost = trim((string) request()->post('db_host', '127.0.0.1'));
        $dbPort = (int) request()->post('db_port', 3306);
        $dbName = trim((string) request()->post('db_name', ''));
        $dbUser = trim((string) request()->post('db_user', 'root'));
        $dbPass = (string) request()->post('db_pass', '');
        $dbCharset = trim((string) request()->post('db_charset', 'utf8mb4'));

        if ($dbName === '') {
            return json(['code' => 0, 'msg' => '请填写数据库名']);
        }

        try {
            $dsn = "mysql:host={$dbHost};port={$dbPort};charset={$dbCharset}";
            $pdo = new \PDO($dsn, $dbUser, $dbPass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '``', $dbName) . "` DEFAULT CHARACTER SET {$dbCharset}");
            $pdo->exec("USE `" . str_replace('`', '``', $dbName) . "`");
            $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = " . $pdo->quote($dbName));
            $tableCount = (int) $stmt->fetchColumn();
            $msg = '连接成功';
            if ($tableCount > 0) {
                $msg .= '（检测到已有 ' . $tableCount . ' 张表，安装将覆盖，请勾选「确认覆盖安装」后继续）';
            }
            return json(['code' => 1, 'msg' => $msg, 'has_tables' => $tableCount > 0, 'table_count' => $tableCount]);
        } catch (\Throwable $e) {
            return json(['code' => 0, 'msg' => '连接失败：' . $e->getMessage()]);
        }
    }

    /**
     * 从 .env 读取 ADMIN_ENTRY（路径式入口，无 .php）
     */
    protected function getAdminEntryFromEnv(): string
    {
        $envFile = root_path() . '.env';
        if (!is_file($envFile)) {
            return '';
        }
        $content = @file_get_contents($envFile);
        if ($content === false || !preg_match('/^\s*ADMIN_ENTRY\s*=\s*(\S+)/m', $content, $m)) {
            return '';
        }
        $entry = trim($m[1]);
        if (substr($entry, -4) === '.php') {
            $entry = substr($entry, 0, -4);
        }
        return $entry;
    }

    /**
     * 生成随机后台路径（如 lsj5492），不创建文件，仅写入 .env；访问 /随机路径/xxx 经 index.php 转成 /admin/xxx
     * @return string 随机路径段，如 lsj5492
     */
    protected function createAdminEntry(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $name = '';
        for ($i = 0; $i < 12; $i++) {
            $name .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $name;
    }

    /**
     * 写入 .env 数据库相关配置及后台入口（项目根目录 .env）
     */
    protected function writeEnv(string $dbHost, int $dbPort, string $dbName, string $dbUser, string $dbPass, string $dbPrefix, string $dbCharset, string $adminEntry = ''): void
    {
        $root = root_path();
        $envFile = $root . '.env';
        $lines = [
            'APP_DEBUG = false',
            'DB_TYPE = mysql',
            'DB_DRIVER = mysql',
            'DB_HOST = ' . $dbHost,
            'DB_PORT = ' . $dbPort,
            'DB_NAME = ' . $dbName,
            'DB_USER = ' . $dbUser,
            'DB_PASS = ' . $dbPass,
            'DB_CHARSET = ' . $dbCharset,
            'DB_PREFIX = ' . $dbPrefix,
        ];
        if ($adminEntry !== '') {
            $lines[] = 'ADMIN_ENTRY = ' . $adminEntry;
        }
        $content = implode("\n", $lines);
        if (is_file($envFile)) {
            $old = file_get_contents($envFile);
            foreach ($lines as $line) {
                $key = trim(explode('=', $line, 2)[0] ?? '');
                $old = preg_replace('/^' . preg_quote($key, '/') . '\s*=.*$/m', $line, $old, 1, $c);
                if ($c === 0) {
                    $old .= "\n" . $line;
                }
            }
            $content = $old;
        }
        if (file_put_contents($envFile, $content) === false) {
            throw new \RuntimeException('无法写入 .env 文件，请检查目录可写权限');
        }
    }

    /**
     * 执行 init.sql（仅基础表，不含 MES），替换表前缀，返回 PDO 供创建管理员使用
     */
    protected function runSql(string $host, int $port, string $dbname, string $user, string $pass, string $charset, string $prefix): \PDO
    {
        $sqlFile = root_path() . 'database/init.sql';
        if (!is_file($sqlFile)) {
            throw new \RuntimeException('缺少 database/init.sql');
        }
        $dsn = "mysql:host={$host};port={$port};dbname=" . str_replace('`', '``', $dbname) . ";charset={$charset}";
        $pdo = new \PDO($dsn, $user, $pass, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

        $sql = file_get_contents($sqlFile);
        $sql = str_replace('`fa_', '`' . $prefix, $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $stmt) {
            if ($stmt === '') {
                continue;
            }
            try {
                $pdo->exec($stmt);
            } catch (\Throwable $e) {
                if (stripos($stmt, 'DROP TABLE') !== false) {
                    continue;
                }
                throw $e;
            }
        }
        return $pdo;
    }

    /**
     * 更新超级管理员（init.sql 已插入 id=1），改为用户填写的账号与密码
     */
    protected function createAdmin(\PDO $pdo, string $prefix, string $username, string $password, string $nickname): void
    {
        $table = $prefix . 'admin';
        $hash = password_hash($password, PASSWORD_BCRYPT);
        if ($hash === false) {
            throw new \RuntimeException('密码加密失败，请重试');
        }
        $time = time();
        $stmt = $pdo->prepare("UPDATE `{$table}` SET `username`=?, `password`=?, `nickname`=?, `update_time`=? WHERE `id`=1");
        $stmt->execute([$username, $hash, $nickname, $time]);
        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('未找到 id=1 的管理员记录，请检查 database/init.sql 是否已正确执行');
        }
    }

}
