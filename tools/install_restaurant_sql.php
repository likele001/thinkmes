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
            } catch (Throwable $e) {
            }
            continue;
        }
        $conn->execute($stmt);
    }
}

function upsertConfig(string $name, string $title, string $value): void
{
    $now = time();
    $row = Db::name('config')->where('name', $name)->find();
    if ($row) {
        Db::name('config')->where('name', $name)->update(['value' => $value, 'update_time' => $now]);
        return;
    }
    Db::name('config')->insert([
        'name' => $name,
        'title' => $title,
        'value' => $value,
        'group' => 'base',
        'sort' => 0,
        'create_time' => $now,
        'update_time' => $now,
    ]);
}

$ensureColumn = function (string $table, string $column, string $ddl) use (&$ensureColumn): void {
    $prefix = (string) (Db::connect()->getConfig()['prefix'] ?? 'fa_');
    $dbName = (string) (Db::connect()->getConfig()['database'] ?? '');
    $full = $prefix . $table;
    $exists = Db::query("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1", [$dbName, $full, $column]);
    if (!$exists) {
        Db::execute("ALTER TABLE `" . str_replace('`', '``', $full) . "` ADD COLUMN " . $ddl);
    }
};

$ensureIndex = function (string $table, string $indexName, string $createSql, array $dropIfExists = []) use (&$ensureIndex): void {
    $prefix = (string) (Db::connect()->getConfig()['prefix'] ?? 'fa_');
    $dbName = (string) (Db::connect()->getConfig()['database'] ?? '');
    $full = $prefix . $table;
    foreach ($dropIfExists as $dn) {
        $hit = Db::query("SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND INDEX_NAME=? LIMIT 1", [$dbName, $full, $dn]);
        if ($hit) {
            Db::execute("ALTER TABLE `" . str_replace('`', '``', $full) . "` DROP INDEX `" . str_replace('`', '``', $dn) . "`");
        }
    }
    $exists = Db::query("SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND INDEX_NAME=? LIMIT 1", [$dbName, $full, $indexName]);
    if (!$exists) {
        Db::execute($createSql);
    }
};

$prefix = (string) (Db::connect()->getConfig()['prefix'] ?? 'fa_');
$root = dirname(__DIR__);

runSqlFile($root . '/database/restaurant/create_tables.sql', $prefix);
runSqlFile($root . '/database/restaurant/seed_restaurant_menu.sql', $prefix);

$ensureColumn('restaurant_cart', 'product_type', "`product_type` varchar(20) NOT NULL DEFAULT 'item'");
$ensureColumn('restaurant_cart', 'combo_id', "`combo_id` int unsigned NOT NULL DEFAULT 0");
$ensureColumn('restaurant_cart', 'option_key', "`option_key` varchar(64) NOT NULL DEFAULT ''");
$ensureColumn('restaurant_cart', 'option_snapshot', "`option_snapshot` text");
$ensureColumn('restaurant_cart', 'unit_price', "`unit_price` decimal(10,2) NOT NULL DEFAULT 0.00");
$ensureColumn('restaurant_cart', 'line_amount', "`line_amount` decimal(10,2) NOT NULL DEFAULT 0.00");
$ensureIndex(
    'restaurant_cart',
    'uk_table_product_option',
    "ALTER TABLE `{$prefix}restaurant_cart` ADD UNIQUE KEY `uk_table_product_option` (`tenant_id`,`table_id`,`product_type`,`item_id`,`combo_id`,`option_key`)",
    ['uk_table_item', 'uk_table_product']
);

$ensureColumn('restaurant_order_item', 'product_type', "`product_type` varchar(20) NOT NULL DEFAULT 'item'");
$ensureColumn('restaurant_order_item', 'combo_id', "`combo_id` int unsigned NOT NULL DEFAULT 0");
$ensureColumn('restaurant_order_item', 'option_key', "`option_key` varchar(64) NOT NULL DEFAULT ''");
$ensureColumn('restaurant_order_item', 'name_snapshot', "`name_snapshot` varchar(255) NOT NULL DEFAULT ''");
$ensureColumn('restaurant_order_item', 'option_snapshot', "`option_snapshot` text");
$ensureColumn('restaurant_order_item', 'unit_price', "`unit_price` decimal(10,2) NOT NULL DEFAULT 0.00");
$ensureColumn('restaurant_order_item', 'line_amount', "`line_amount` decimal(10,2) NOT NULL DEFAULT 0.00");

upsertConfig('app_restaurant_installed', 'restaurant 应用是否已安装', '1');
upsertConfig('app_restaurant_title', 'restaurant title', '餐饮SaaS');
upsertConfig('app_restaurant_description', 'restaurant description', '门店、区域、桌台、菜品、扫码点餐');
upsertConfig('app_restaurant_auth_prefix', 'restaurant auth_prefix', 'restaurant');
upsertConfig('app_restaurant_tables', 'restaurant 数据表列表（卸载时可删）', json_encode([
    'restaurant_store',
    'restaurant_area',
    'restaurant_table',
    'restaurant_category',
    'restaurant_item',
    'restaurant_item_option_group',
    'restaurant_item_option',
    'restaurant_combo',
    'restaurant_combo_item',
    'restaurant_cart',
    'restaurant_order',
    'restaurant_order_item',
    'restaurant_kds_event',
    'restaurant_ai_config',
    'restaurant_ai_log',
    'restaurant_ai_daily_report',
    'restaurant_review',
    'restaurant_review_reply_template',
    'restaurant_review_keyword',
    'restaurant_review_alert',
], JSON_UNESCAPED_UNICODE));

Db::name('auth_rule')->where('name', 'restaurant')->update(['status' => 1]);
Db::name('auth_rule')->where('name', 'like', 'restaurant/%')->update(['status' => 1]);

$ensureColumn('config', 'name', "`name` varchar(50) NOT NULL");
// 预置配置键（若不存在则插入空值）
$preset = [
    ['restaurant_wxa_appid', '餐饮小程序AppID', ''],
    ['restaurant_wxa_secret', '餐饮小程序Secret', ''],
    ['restaurant_wxa_page', '餐饮小程序页面路径', 'pages/menu/index'],
    ['restaurant_openclaw_enabled', '餐饮OpenClaw启用', '0'],
    ['restaurant_openclaw_api_base', '餐饮OpenClaw API Base', ''],
    ['restaurant_openclaw_api_key', '餐饮OpenClaw API Key', ''],
    ['restaurant_openclaw_workspace', '餐饮OpenClaw工作区', ''],
    ['restaurant_openclaw_webhook_secret', '餐饮OpenClaw Webhook Secret', ''],
    ['restaurant_review_bad_threshold', '餐饮差评阈值(<=为差评)', '3'],
    ['restaurant_review_alert_enabled', '餐饮差评告警启用', '1'],
    ['restaurant_review_alert_push_openclaw', '餐饮差评告警推送OpenClaw', '1'],
];
foreach ($preset as $p) {
    $row = Db::name('config')->where('name', $p[0])->find();
    if (!$row) {
        Db::name('config')->insert([
            'name' => $p[0],
            'title' => $p[1],
            'value' => $p[2],
            'group' => 'base',
            'sort' => 0,
            'create_time' => time(),
            'update_time' => time(),
        ]);
    }
}

$seedKw = [
    ['出餐慢', '出餐', 3],
    ['等太久', '出餐', 2],
    ['太咸', '口味', 2],
    ['太淡', '口味', 2],
    ['不好吃', '口味', 3],
    ['服务差', '服务', 3],
    ['态度差', '服务', 2],
    ['不干净', '卫生', 3],
    ['有异物', '卫生', 3],
    ['太贵', '价格', 2],
    ['分量少', '分量', 2],
    ['环境差', '环境', 2],
];
foreach ($seedKw as $k) {
    $exists = Db::name('restaurant_review_keyword')->where('tenant_id', 0)->where('keyword', $k[0])->find();
    if (!$exists) {
        Db::name('restaurant_review_keyword')->insert([
            'tenant_id' => 0,
            'keyword' => $k[0],
            'category' => $k[1],
            'weight' => (int) $k[2],
            'status' => 1,
            'create_time' => time(),
            'update_time' => time(),
        ]);
    }
}
$tables = Db::query("SHOW TABLES LIKE '" . addslashes($prefix . "restaurant_%") . "'");
$tableCount = is_array($tables) ? count($tables) : 0;

echo "ok tables=" . $tableCount . PHP_EOL;
