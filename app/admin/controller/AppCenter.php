<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use think\Response;

/**
 * 应用中心：在后台单独安装/卸载内置应用（如 MES）
 */
class AppCenter extends Backend
{
    protected array $apps = [];

    protected function initialize(): void
    {
        parent::initialize();
        $this->apps = [
            'mes' => [
                'key'         => 'mes',
                'title'       => 'MES 制造执行系统',
                'description' => '订单、产品、BOM、工序、报工、生产计划、库存、质检等制造执行功能',
                'sql_files'   => [
                    'migrate_add_mes_tables.sql',
                    'migrate_add_mes_extended_tables.sql',
                    'migrate_add_mes_complete_supply_chain.sql',
                    'seed_mes_menu.sql',
                ],
                'check_table' => 'mes_product',
                'code_path'   => dirname(__DIR__) . '/controller/mes',
            ],
            'crm' => [
                'key'         => 'crm',
                'title'       => 'CRM 客户关系管理',
                'description' => '客户、联系人、商机、合同、跟进、回款、销售订单等销售与客户全生命周期管理',
                'sql_files'   => [
                    'migrate_add_crm_tables.sql',
                    'migrate_add_crm_sales_order.sql',
                    'seed_crm_menu.sql',
                ],
                'check_table' => 'crm_customer',
                'code_path'   => dirname(__DIR__) . '/controller/crm',
            ],
            'ai' => [
                'key'         => 'ai',
                'title'       => '工厂 AI',
                'description' => '语音报工、异常检测、智能问答、自动日报、CRM 智能跟单；可单独开关子功能',
                'sql_files'   => [
                    'migrate_add_ai_tables.sql',
                    'migrate_add_ai_package.sql',
                    'migrate_add_ai_module_switch.sql',
                    'seed_ai_menu.sql',
                ],
                'check_table' => 'ai_config',
                'code_path'   => dirname(__DIR__) . '/controller/ai',
            ],
            'payment' => [
                'key'         => 'payment',
                'title'       => '支付管理',
                'description' => '单用户版：支付配置、订单管理、回调日志、统计报表',
                'sql_files'   => [
                    'migrate_add_payment_tables.sql',
                    'migrate_add_payment_callback_log.sql',
                    'seed_payment_menu.sql',
                ],
                'check_table' => 'payment_gateway',
                'code_path'   => dirname(__DIR__) . '/controller/payment',
            ],
        ];
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
        foreach ($this->apps as $key => $def) {
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
            return $this->error('应用不存在');
        }
        $def = $this->apps[$appKey];
        if (!is_dir($def['code_path'])) {
            return $this->error('当前版本不包含该应用，请使用完整版');
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
        return $this->success('安装成功');
    }

    public function uninstall(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可卸载应用');
        }
        $appKey = trim((string) $this->request->post('app', ''));
        if ($appKey === '' || !isset($this->apps[$appKey])) {
            return $this->error('应用不存在');
        }
        if ($appKey === 'mes') {
            Db::name('auth_rule')->where('name', 'mes')->update(['status' => 0]);
            Db::name('auth_rule')->where('name', 'like', 'mes/%')->update(['status' => 0]);
        }
        if ($appKey === 'crm') {
            Db::name('auth_rule')->where('name', 'crm')->update(['status' => 0]);
            Db::name('auth_rule')->where('name', 'like', 'crm/%')->update(['status' => 0]);
        }
        if ($appKey === 'ai') {
            Db::name('auth_rule')->where('name', 'ai')->update(['status' => 0]);
            Db::name('auth_rule')->where('name', 'like', 'ai/%')->update(['status' => 0]);
        }
        if ($appKey === 'payment') {
            Db::name('auth_rule')->where('name', 'payment')->update(['status' => 0]);
            Db::name('auth_rule')->where('name', 'like', 'payment/%')->update(['status' => 0]);
        }
        $this->setAppInstalledConfig($appKey, 0);
        return $this->success('已卸载（菜单已隐藏，数据表保留）');
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
        $authPrefix = $manifest['auth_prefix'] ?? $appKey;
        Db::name('auth_rule')->where('name', $authPrefix)->update(['status' => 1]);
        Db::name('auth_rule')->where('name', 'like', $authPrefix . '/%')->update(['status' => 1]);
    }
}
