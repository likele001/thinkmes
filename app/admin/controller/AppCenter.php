<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 应用中心：基础框架不内置业务应用，仅支持「上传应用包」安装与卸载
 * 各业务应用（MES/CRM/AI 等）单独打包发布，通过本地上传 zip 安装
 * 完整版：若代码中存在对应应用目录，也会在列表中展示并可卸载
 */
class AppCenter extends Backend
{
    /** 基础框架不内置应用，应用均通过上传应用包安装 */
    protected array $apps = [];

    /** 可单独打包的应用 key => [title, description]，与 build/pack_*.php 对应 */
    protected static array $knownPackagedApps = [
        'mes'       => ['title' => 'MES制造执行', 'description' => '工单、报工、工序、BOM、生产计划、质检、工资等'],
        'crm'       => ['title' => 'CRM客户关系', 'description' => '客户、商机、合同、跟进、回款、销售订单'],
        'ai'        => ['title' => '工厂AI', 'description' => 'AI配置、老板问答、语音报工、智能跟单等'],
        'payment'   => ['title' => '支付管理', 'description' => '支付配置与订单支付'],
        'equipment' => ['title' => '设备管理', 'description' => '设备、点检、保养、维修'],
        'hr'        => ['title' => '人事考勤', 'description' => '组织、员工、考勤、排班、请假、薪资'],
        'finance'   => ['title' => '财务管理', 'description' => '科目、凭证、账簿、报表'],
    ];

    /** 卸载时可选删除的数据表：key => 表名前缀（不含 fa_），用于 SHOW TABLES LIKE prefix+pattern；上传安装的以 app.json tables 为准 */
    protected static array $knownTablePrefixes = [
        'mes'       => ['mes_'],
        'crm'       => ['crm_'],
        'ai'        => ['ai_', 'tenant_ai_'],
        'payment'   => ['payment_'],
        'equipment' => ['equipment_'],
        'hr'        => ['hr_'],
        'finance'   => ['finance_'],
    ];

    protected function initialize(): void
    {
        parent::initialize();
    }

    protected function getTablePrefix(): string
    {
        return (string) (Db::connect()->getConfig()['prefix'] ?? 'fa_');
    }

    public function index(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可访问应用中心');
        }
        if ($this->request->isAjax()) {
            return $this->appList();
        }
        View::assign('title', '应用中心');
        return $this->fetchWithLayout('app_center/index');
    }

    protected function appList(): Response
    {
        $prefix = $this->getTablePrefix();
        $list = [];
        // 1) 已安装的应用（从 config app_*_installed=1 读取，含上传安装的）
        $configs = Db::name('config')->where('name', 'like', 'app_%_installed')->where('value', '1')->column('name');
        foreach ($configs as $name) {
            if (preg_match('/^app_(.+)_installed$/', $name, $m)) {
                $key = $m[1];
                $title = Db::name('config')->where('name', 'app_' . $key . '_title')->value('value') ?: $key;
                $description = Db::name('config')->where('name', 'app_' . $key . '_description')->value('value') ?: '';
                $list[] = [
                    'key'         => $key,
                    'title'       => $title,
                    'description' => $description,
                    'available'   => true,
                    'installed'   => true,
                ];
            }
        }
        // 2) 完整版：代码中存在的可打包应用（mes/crm/ai/payment/equipment/hr/finance）也列入，与「上传安装」一致展示
        $installedKeys = array_column($list, 'key');
        foreach (static::$knownPackagedApps as $key => $def) {
            if (in_array($key, $installedKeys, true)) {
                continue;
            }
            if (!$this->appCodeExists($key)) {
                continue;
            }
            $configInstalled = Db::name('config')->where('name', 'app_' . $key . '_installed')->value('value');
            $installed = ($configInstalled === '1' || $configInstalled === null); // 未写过 config 视为已安装（代码在即算）
            $title = Db::name('config')->where('name', 'app_' . $key . '_title')->value('value') ?: ($def['title'] ?? $key);
            $description = Db::name('config')->where('name', 'app_' . $key . '_description')->value('value') ?: ($def['description'] ?? '');
            $list[] = [
                'key'         => $key,
                'title'       => $title,
                'description' => $description,
                'available'   => true,
                'installed'   => $installed,
            ];
        }
        // 3) 若有内置应用且未安装，也展示（兼容带内置应用的发行版）
        $installedKeys = array_column($list, 'key');
        foreach ($this->apps as $key => $def) {
            if (in_array($key, $installedKeys, true)) {
                continue;
            }
            $available = is_dir($def['code_path'] ?? '');
            $installed = $this->isAppInstalled($key, $def, $prefix);
            $list[] = [
                'key'         => $key,
                'title'       => $def['title'],
                'description' => $def['description'],
                'available'   => $available,
                'installed'   => $installed,
            ];
        }
        return $this->success('', ['total' => count($list), 'list' => $list]);
    }

    /** 判断可打包应用是否存在于代码中（有对应 controller 目录或路由） */
    protected function appCodeExists(string $key): bool
    {
        $base = root_path() . 'app/admin/controller/';
        if (is_dir($base . $key)) {
            return true;
        }
        $routeFile = root_path() . 'app/admin/route/' . $key . '.php';
        return is_file($routeFile);
    }

    protected function isAppInstalled(string $key, array $def, string $prefix): bool
    {
        $configVal = Db::name('config')->where('name', 'app_' . $key . '_installed')->value('value');
        if ($configVal === '1') {
            return true;
        }
        if ($configVal === '0' || $configVal === '') {
            return false;
        }
        $checkTable = $def['check_table'] ?? '';
        if ($checkTable !== '') {
            try {
                $full = $prefix . $checkTable;
                $r = Db::query("SHOW TABLES LIKE '" . addslashes($full) . "'");
                if (!empty($r)) {
                    return true;
                }
            } catch (\Throwable $e) {}
        }
        return false;
    }

    public function install(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可安装应用');
        }
        $appKey = trim((string) $this->request->post('app', ''));
        if ($appKey === '' || !isset($this->apps[$appKey])) {
            return $this->error('基础版不内置应用，请通过「上传应用包」安装');
        }
        $def = $this->apps[$appKey];
        if (!is_dir($def['code_path'] ?? '')) {
            return $this->error('当前版本不包含该应用，请上传应用包安装');
        }
        $prefix = $this->getTablePrefix();
        if ($this->isAppInstalled($appKey, $def, $prefix)) {
            return $this->error('该应用已安装');
        }
        $dbDir = root_path() . 'database/';
        foreach ($def['sql_files'] as $file) {
            $path = $dbDir . $file;
            if (!is_file($path)) {
                return $this->error('缺少数据库文件：' . $file);
            }
            try {
                $this->runSqlFile($path, $prefix);
            } catch (\Throwable $e) {
                return $this->error('执行 ' . $file . ' 失败：' . $e->getMessage());
            }
        }
        $this->setAppInstalledConfig($appKey, 1);
        if ($appKey === 'mes') {
            Db::name('auth_rule')->where('name', 'mes')->update(['status' => 1]);
            Db::name('auth_rule')->where('name', 'like', 'mes/%')->update(['status' => 1]);
        }
        if ($appKey === 'crm') {
            Db::name('auth_rule')->where('name', 'crm')->update(['status' => 1]);
            Db::name('auth_rule')->where('name', 'like', 'crm/%')->update(['status' => 1]);
        }
        if ($appKey === 'ai') {
            Db::name('auth_rule')->where('name', 'ai')->update(['status' => 1]);
            Db::name('auth_rule')->where('name', 'like', 'ai/%')->update(['status' => 1]);
        }
        if ($appKey === 'payment') {
            Db::name('auth_rule')->where('name', 'payment')->update(['status' => 1]);
            Db::name('auth_rule')->where('name', 'like', 'payment/%')->update(['status' => 1]);
        }
        if ($appKey === 'equipment') {
            Db::name('auth_rule')->where('name', 'equipment')->update(['status' => 1]);
            Db::name('auth_rule')->where('name', 'like', 'equipment/%')->update(['status' => 1]);
        }
        if ($appKey === 'hr') {
            Db::name('auth_rule')->where('name', 'hr')->update(['status' => 1]);
            Db::name('auth_rule')->where('name', 'like', 'hr/%')->update(['status' => 1]);
        }
        if ($appKey === 'finance') {
            Db::name('auth_rule')->where('name', 'finance')->update(['status' => 1]);
            Db::name('auth_rule')->where('name', 'like', 'finance/%')->update(['status' => 1]);
        }
        return $this->success('安装成功');
    }

    public function uninstall(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可卸载应用');
        }
        $appKey = trim((string) $this->request->post('app', ''));
        if ($appKey === '') {
            return $this->error('请指定应用');
        }
        $configVal = Db::name('config')->where('name', 'app_' . $appKey . '_installed')->value('value');
        $allowUninstall = ($configVal === '1') || (isset(static::$knownPackagedApps[$appKey]) && $this->appCodeExists($appKey));
        if (!$allowUninstall) {
            return $this->error('该应用未安装');
        }
        $authPrefix = Db::name('config')->where('name', 'app_' . $appKey . '_auth_prefix')->value('value') ?: $appKey;
        // 删除该应用在权限规则表中的菜单/权限记录，避免权限规则列表里残留大量「禁用」项；重新安装时会执行 seed_*_menu.sql 重新生成
        Db::name('auth_rule')->where('name', $authPrefix)->delete();
        Db::name('auth_rule')->where('name', 'like', $authPrefix . '/%')->delete();
        $deleteTables = (int) $this->request->post('delete_tables', 0);
        $dropped = [];
        if ($deleteTables === 1) {
            $tables = $this->getAppTablesForUninstall($appKey, $prefix);
            foreach ($tables as $fullName) {
                try {
                    Db::execute("DROP TABLE IF EXISTS `" . str_replace('`', '``', $fullName) . "`");
                    $dropped[] = $fullName;
                } catch (\Throwable $e) {
                    // 单表失败不阻断，继续删其余表
                }
            }
        }
        $this->setAppInstalledConfig($appKey, 0);
        $msg = '已卸载（菜单与权限规则已移除）';
        if (!empty($dropped)) {
            $msg .= '，已删除 ' . count($dropped) . ' 个数据表：' . implode('、', array_slice($dropped, 0, 5)) . (count($dropped) > 5 ? ' 等' : '');
        } else {
            $msg .= '，数据表已保留';
        }
        return $this->success($msg);
    }

    /** 获取某应用在卸载时可删除的表名（带表前缀） */
    protected function getAppTablesForUninstall(string $appKey, string $prefix): array
    {
        $tables = [];
        $configJson = Db::name('config')->where('name', 'app_' . $appKey . '_tables')->value('value');
        if ($configJson !== null && $configJson !== '') {
            $arr = json_decode($configJson, true);
            if (is_array($arr)) {
                foreach ($arr as $t) {
                    $t = str_replace('`', '', (string) $t);
                    if ($t === '') continue;
                    if (str_starts_with($t, 'fa_')) {
                        $t = $prefix . substr($t, 3);
                    } else {
                        $t = $prefix . ltrim($t, '_');
                    }
                    $tables[] = $t;
                }
                return array_unique($tables);
            }
        }
        if (!isset(static::$knownTablePrefixes[$appKey])) {
            return $tables;
        }
        $conn = Db::connect();
        foreach (static::$knownTablePrefixes[$appKey] as $suffix) {
            $pattern = $prefix . $suffix . '%';
            $rows = Db::query("SHOW TABLES LIKE '" . addslashes($pattern) . "'");
            foreach ($rows as $row) {
                $name = (string) reset($row);
                if ($name !== '') {
                    $tables[] = $name;
                }
            }
        }
        return array_unique($tables);
    }

    protected function setAppInstalledConfig(string $appKey, int $value): void
    {
        $name = 'app_' . $appKey . '_installed';
        if (Db::name('config')->where('name', $name)->find()) {
            Db::name('config')->where('name', $name)->update(['value' => (string) $value, 'update_time' => time()]);
        } else {
            Db::name('config')->insert([
                'name' => $name, 'title' => $appKey . ' 应用是否已安装', 'value' => (string) $value,
                'group' => 'base', 'sort' => 0, 'create_time' => time(), 'update_time' => time(),
            ]);
        }
    }

    protected function runSqlFile(string $path, string $prefix): void
    {
        $sql = file_get_contents($path);
        $sql = str_replace('`fa_', '`' . $prefix, $sql);
        $sql = str_replace('fa_auth_rule', $prefix . 'auth_rule', $sql);
        $sql = str_replace('fa_config', $prefix . 'config', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        $conn = Db::connect();
        foreach ($statements as $stmt) {
            $stmt = trim(preg_replace('/^\s*--[^\n]*\n?/m', '', $stmt));
            if ($stmt === '') {
                continue;
            }
            if (stripos($stmt, 'SET @') === 0) {
                try {
                    $conn->execute($stmt);
                } catch (\Throwable $e) {}
                continue;
            }
            try {
                $conn->execute($stmt);
            } catch (\Throwable $e) {
                if (stripos($stmt, 'DROP TABLE') !== false) {
                    continue;
                }
                throw $e;
            }
        }
    }

    /**
     * 上传应用包：接收 zip 文件，解压合并到项目并执行安装
     */
    public function upload(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可上传应用');
        }
        $file = $this->request->file('file') ?? $this->request->file('zip');
        if (!$file || !$file->isValid()) {
            return $this->error('请选择有效的 zip 文件');
        }
        $ext = strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            return $this->error('仅支持 .zip 格式');
        }
        $tmpDir = runtime_path() . 'app_upload_' . uniqid();
        @mkdir($tmpDir, 0755, true);
        try {
            $zipPath = $tmpDir . '/pkg.zip';
            $file->move($tmpDir, 'pkg.zip');
            if (!is_file($zipPath)) {
                throw new \RuntimeException('上传失败');
            }
            $manifest = $this->extractAndMerge($zipPath, $tmpDir);
            $this->installFromManifest($manifest);
            return $this->success('上传并安装成功');
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        } finally {
            $this->delTree($tmpDir);
        }
    }

    protected function extractAndMerge(string $zipPath, string $tmpDir): array
    {
        $zip = new \ZipArchive();
        if (!$zip->open($zipPath, \ZipArchive::RDONLY)) {
            throw new \RuntimeException('无法打开 zip 文件');
        }
        $extractDir = $tmpDir . '/ext';
        @mkdir($extractDir, 0755, true);
        $zip->extractTo($extractDir);
        $zip->close();
        $entries = scandir($extractDir);
        $rootFolder = null;
        foreach ($entries as $e) {
            if ($e === '.' || $e === '..') continue;
            $p = $extractDir . '/' . $e;
            if (is_dir($p)) {
                $rootFolder = $e;
                break;
            }
        }
        if (!$rootFolder) {
            throw new \RuntimeException('zip 包格式错误：缺少根目录');
        }
        $manifestPath = $extractDir . '/' . $rootFolder . '/app.json';
        if (!is_file($manifestPath)) {
            throw new \RuntimeException('zip 包格式错误：缺少 app.json 清单文件');
        }
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (!is_array($manifest) || empty($manifest['key']) || empty($manifest['sql_files'])) {
            throw new \RuntimeException('app.json 格式错误：需包含 key、sql_files');
        }
        $srcBase = $extractDir . '/' . $rootFolder;
        $root = root_path();
        $allowedDirs = ['app', 'public', 'database'];
        $this->mergeDir($srcBase, $root, $allowedDirs);
        return $manifest;
    }

    protected function mergeDir(string $src, string $dst, array $allowedDirs): void
    {
        if (!is_dir($src)) return;
        $items = scandir($src);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            if (!in_array($item, $allowedDirs, true)) continue;
            $srcPath = $src . '/' . $item;
            $dstPath = $dst . $item;
            if (is_dir($srcPath)) {
                $this->copyDirRecursive($srcPath, $dstPath);
            } else {
                @mkdir(dirname($dstPath), 0755, true);
                @copy($srcPath, $dstPath);
            }
        }
    }

    protected function copyDirRecursive(string $src, string $dst): void
    {
        if (!is_dir($src)) return;
        @mkdir($dst, 0755, true);
        $dir = opendir($src);
        while (($f = readdir($dir)) !== false) {
            if ($f === '.' || $f === '..') continue;
            $srcPath = $src . '/' . $f;
            $dstPath = $dst . '/' . $f;
            if (is_dir($srcPath)) {
                $this->copyDirRecursive($srcPath, $dstPath);
            } else {
                @mkdir(dirname($dstPath), 0755, true);
                @copy($srcPath, $dstPath);
            }
        }
        closedir($dir);
    }

    protected function delTree(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $f) {
            $path = $dir . '/' . $f;
            is_dir($path) ? $this->delTree($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    protected function installFromManifest(array $manifest): void
    {
        $appKey = $manifest['key'];
        $prefix = $this->getTablePrefix();
        $def = [
            'check_table' => $manifest['check_table'] ?? '',
            'sql_files'   => $manifest['sql_files'],
        ];
        $alreadyInstalled = $this->isAppInstalled($appKey, $def, $prefix);
        $dbDir = root_path() . 'database/';
        foreach ($manifest['sql_files'] as $file) {
            $path = $dbDir . $file;
            if (!is_file($path)) {
                throw new \RuntimeException('缺少数据库文件：' . $file);
            }
            if (!$alreadyInstalled) {
                $this->runSqlFile($path, $prefix);
            } elseif (preg_match('/^seed_.*_menu\.sql$/', $file)) {
                $this->runSqlFile($path, $prefix);
            }
        }
        $this->setAppInstalledConfig($appKey, 1);
        if (!empty($manifest['tables']) && is_array($manifest['tables'])) {
            $this->setAppTablesConfig($appKey, $manifest['tables']);
        }
        $authPrefix = $manifest['auth_prefix'] ?? $appKey;
        Db::name('auth_rule')->where('name', $authPrefix)->update(['status' => 1]);
        Db::name('auth_rule')->where('name', 'like', $authPrefix . '/%')->update(['status' => 1]);
        $this->setAppMetaConfig($appKey, [
            'title'       => $manifest['title'] ?? $appKey,
            'description' => $manifest['description'] ?? '',
            'auth_prefix' => $authPrefix,
        ]);
    }

    protected function setAppMetaConfig(string $appKey, array $meta): void
    {
        $now = time();
        foreach ($meta as $k => $v) {
            $name = 'app_' . $appKey . '_' . $k;
            if (Db::name('config')->where('name', $name)->find()) {
                Db::name('config')->where('name', $name)->update(['value' => (string) $v, 'update_time' => $now]);
            } else {
                Db::name('config')->insert([
                    'name' => $name, 'title' => $appKey . ' ' . $k, 'value' => (string) $v,
                    'group' => 'base', 'sort' => 0, 'create_time' => $now, 'update_time' => $now,
                ]);
            }
        }
    }

    protected function setAppTablesConfig(string $appKey, array $tables): void
    {
        $name = 'app_' . $appKey . '_tables';
        $value = json_encode(array_values($tables));
        if (Db::name('config')->where('name', $name)->find()) {
            Db::name('config')->where('name', $name)->update(['value' => $value, 'update_time' => time()]);
        } else {
            Db::name('config')->insert([
                'name' => $name, 'title' => $appKey . ' 数据表列表（卸载时可删）', 'value' => $value,
                'group' => 'base', 'sort' => 0, 'create_time' => time(), 'update_time' => time(),
            ]);
        }
    }
}
