<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new think\App();
$app->initialize();

use think\facade\Db;

function runSqlFile(string $path, string $prefix): void
{
    if (!is_file($path)) {
        throw new RuntimeException('sql file missing: ' . $path);
    }
    $sql = (string) file_get_contents($path);
    $sql = str_replace('`fa_', '`' . $prefix, $sql);
    $sql = str_replace('fa_auth_rule', $prefix . 'auth_rule', $sql);
    $sql = str_replace('fa_config', $prefix . 'config', $sql);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $conn = Db::connect();
    foreach ($statements as $stmt) {
        $stmt = trim((string) preg_replace('/^\s*--[^\n]*\n?/m', '', $stmt));
        if ($stmt === '') continue;
        if (stripos($stmt, 'SET @') === 0) {
            try {
                $conn->execute($stmt);
            } catch (Throwable $e) {
            }
            continue;
        }
        $conn->execute($stmt);
    }
}

$prefix = (string) (Db::connect()->getConfig()['prefix'] ?? 'fa_');
$root = dirname(__DIR__);

runSqlFile($root . '/database/migrate_add_mes_scheduling_tables.sql', $prefix);
runSqlFile($root . '/database/seed_mes_menu.sql', $prefix);

Db::name('auth_rule')->where('name', 'mes')->update(['status' => 1]);
Db::name('auth_rule')->where('name', 'like', 'mes/%')->update(['status' => 1]);

$tables = Db::query("SHOW TABLES LIKE '" . addslashes($prefix . "mes_schedule_%") . "'");
$tableCount = is_array($tables) ? count($tables) : 0;
echo "ok tables=" . $tableCount . PHP_EOL;

