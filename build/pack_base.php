#!/usr/bin/env php
<?php
/**
 * 基础框架打包脚本（底座 + 项目内「全部应带文件」）
 * - 必带：app、config、public、vendor、extend、addons、composer、runtime 空目录
 * - 中间件：随 app/ 整包复制，含 app/middleware.php、app/admin|api/middleware.php、
 *   app/admin/middleware/*.php、app/api/middleware/*.php、app/common/middleware/*.php；
 *   以及 config/middleware.php（config 目录整包复制）
 * - 一并复制：route、command、docs、database（完整 SQL 目录；init.sql 仍用 build/database/init_base.sql 覆盖，供 /install）
 * - 根目录说明/示例：nginx.conf.example、install.sh、.htaccess、INSTALLATION_GUIDE 等（若存在）
 * - 不含业务应用目录（mes/crm/ai 等）及 .env；解压后访问 /install 分步安装
 */

$root = realpath(__DIR__ . '/..');
if (!$root || !is_dir($root . '/app')) {
    die("错误：请在项目根目录执行 php build/pack_base.php\n");
}

$version = isset($argv[1]) ? $argv[1] : '1.0';
$outDir = $root . '/build/output/thinkmes-base';
$zipPath = $root . '/dist/thinkmes-base-' . $version . '.zip';

echo "项目根目录: {$root}\n";
echo "输出目录: {$outDir}\n";
echo "打包文件: {$zipPath}\n";

if (!is_dir($root . '/vendor')) {
    die("错误：未找到 vendor 目录。请先在项目根目录执行 composer install 再打包。\n");
}

// 清理并创建输出目录
if (is_dir($outDir)) {
    delTree($outDir);
}
mkdir($outDir, 0755, true);

// 需排除的路径（相对于项目根）— 仅排除业务应用，保留租户/套餐/应用中心/用户管理
$excludeDirs = [
    'public/uploads',
    // MES 制造执行
    'app/admin/controller/mes',
    'app/admin/model/mes',
    'app/admin/view/mes',
    // CRM 客户关系
    'app/admin/controller/crm',
    'app/admin/model/crm',
    'app/admin/view/crm',
    // AI 智能
    'app/admin/controller/ai',
    'app/admin/view/ai',
    'app/admin/model/ai',
    // Payment 支付
    'app/admin/controller/payment',
    'app/admin/model/payment',
    'app/admin/view/payment',
    // Equipment 设备
    'app/admin/controller/equipment',
    'app/admin/model/equipment',
    'app/admin/view/equipment',
    // HR 人事
    'app/admin/controller/hr',
    'app/admin/model/hr',
    'app/admin/view/hr',
    // Finance 财务
    'app/admin/controller/finance',
    'app/admin/model/finance',
    'app/admin/view/finance',
    // Prompt AI提示词
    'app/admin/controller/prompt',
    'app/admin/model/prompt',
    'app/admin/view/prompt',
    // Restaurant 餐饮
    'app/admin/controller/restaurant',
    'app/admin/model/restaurant',
    'app/admin/view/restaurant',
    // Workflow 工作流（新版目录）
    'app/admin/controller/workflow',
    'app/admin/model/workflow',
    'app/admin/view/workflow',
    'app/admin/view/workflow_approval',
    'app/admin/view/workflow_statistics',
    // Wemedia 自媒体视图
    'app/admin/view/wemedia',
    // API 业务控制器目录
    'app/api/controller/prompt',
    'app/api/controller/restaurant',
    // Index 业务控制器目录
    'app/index/controller/prompt',
    'app/index/controller/wemedia',
    // Index 业务视图目录
    'app/index/view/prompt',
    'app/index/view/wemedia',
    'app/index/view/customer_service',
    'app/index/view/mes_dashboard',
    'app/index/view/worker',
    'app/index/view/customer',
    'app/index/view/developer',
    'app/index/view/purchase',
    'app/index/view/store',
    // 前端 JS 业务模块
    'public/assets/js/backend/mes',
    'public/assets/js/backend/crm',
    'public/assets/js/backend/ai',
    'public/assets/js/backend/payment',
    'public/assets/js/backend/equipment',
    'public/assets/js/backend/hr',
    'public/assets/js/backend/finance',
    'public/assets/js/backend/prompt',
    'public/assets/js/backend/restaurant',
    'public/assets/js/backend/workflow',
    'public/assets/js/wemedia',
    // 前端业务页面
    'public/restaurant',
    '.git',
    'runtime/cache',
    'runtime/log',
    'runtime/session',
    'runtime/temp',
    'build/output',
    // 业务数据库目录
    'database/restaurant',
    // common/lib 业务子目录
    'app/common/lib/restaurant',
    'app/common/lib/prompt',
    'app/common/lib/payment',
    'app/common/lib/mes',
    // common/service 业务子目录
    'app/common/service/workflow',
    // 杂项
    'public/figma_download',
];
$excludeFiles = [
    '.env',
    'install.lock',
    'public/.user.ini',
    // 后台业务路由
    'app/admin/route/crm.php',
    'app/admin/route/mes.php',
    'app/admin/route/ai.php',
    'app/admin/route/payment.php',
    'app/admin/route/equipment.php',
    'app/admin/route/hr.php',
    'app/admin/route/finance.php',
    'app/admin/route/prompt.php',
    'app/admin/route/restaurant.php',
    'app/admin/route/workflow.php',
    // API 业务路由
    'app/api/route/ai.php',
    'app/api/route/customer.php',
    'app/api/route/mes.php',
    'app/api/route/payment.php',
    'app/api/route/prompt.php',
    'app/api/route/restaurant.php',
    'app/api/route/restaurant_openclaw.php',
    'app/api/route/restaurant_wxa.php',
    // Index 业务路由
    'app/index/route/prompt.php',
    // Index 业务控制器
    'app/index/controller/Worker.php',
    'app/index/controller/Trace.php',
    'app/index/controller/Customer.php',
    'app/index/controller/CustomerService.php',
    'app/index/controller/DeveloperCenter.php',
    'app/index/controller/MesDashboard.php',
    'app/index/controller/Purchase.php',
    'app/index/controller/Store.php',
    // API 业务控制器
    'app/api/controller/Worker.php',
    'app/api/controller/Scanwork.php',
    'app/api/controller/Customer.php',
    'app/api/controller/Cockpit.php',
    'app/api/controller/Ai.php',
    'app/api/controller/Payment.php',
    'app/api/controller/Chat.php',
    'app/api/controller/Developer.php',
    'app/api/controller/Mesadmin.php',
    'app/api/controller/Mesdashboard.php',
    'app/api/controller/Mesuser.php',
    'app/api/controller/Store.php',
    'app/api/controller/TenantPurchase.php',
    'app/api/controller/Ticket.php',
    // Admin Wemedia 控制器
    'app/admin/controller/WemediaCompliance.php',
    'app/admin/controller/WemediaConfig.php',
    'app/admin/controller/WemediaCopy.php',
    'app/admin/controller/WemediaMaterial.php',
    'app/admin/controller/WemediaReport.php',
    'app/admin/controller/WemediaSchedule.php',
    'app/admin/controller/WemediaTopic.php',
    'app/admin/controller/WemediaVideo.php',
    'app/admin/model/WemediaConfigModel.php',
    // 排除旧版Workflow单文件，使用新版本目录结构
    'app/admin/controller/Workflow.php',
    'app/admin/controller/Workflow_old.php.bak',
    'app/admin/controller/WorkflowApproval.php',
    'app/admin/controller/WorkflowStatistics.php',
    'app/admin/model/Workflow.php',
    'app/admin/model/Workflow_old.php.bak',
    'app/admin/model/WorkflowState.php',
    'app/admin/model/WorkflowState_old.php.bak',
    'app/admin/model/WorkflowTransition.php',
    'app/admin/model/WorkflowTransition_old.php.bak',
    'app/admin/model/WorkflowApproval.php',
    'app/admin/model/WorkflowApprovalRecord.php',
    'app/admin/model/WorkflowDefinition.php',
    'app/admin/model/WorkflowInstance.php',
    'app/admin/model/WorkflowModule.php',
    'app/admin/model/WorkflowNode.php',
    'app/admin/model/WorkflowNodeApprover.php',
    'app/admin/model/WorkflowEdge.php',
    'app/admin/service/WorkflowService.php',
    // 排除备份文件
    'app/admin/view/workflow/add_old.html.bak',
    'app/admin/view/workflow/edit_old.html.bak',
    'app/admin/view/workflow/index_old.html.bak',
    // 前端 JS 业务模块文件
    'public/assets/js/backend/mes.js',
    'public/assets/js/backend/workflow.js',
    'public/assets/js/backend/prompt-js-only.zip',
    // Index 业务布局文件
    'app/index/view/layout/prompt.html',
    'app/index/view/layout/wemedia.html',
    'app/index/view/layout/customer.html',
    // ---- 数据库：业务模块 SQL ----
    // MES
    'database/mes_menu.sql',
    'database/seed_mes_menu.sql',
    'database/seed_mes_auth_rules.sql',
    'database/seed_mes_stock_mrp.sql',
    'database/mes_addons.sql',
    'database/MES_MENU_SUMMARY.md',
    'database/migrate_add_mes_tables.sql',
    'database/migrate_add_mes_extended_tables.sql',
    'database/migrate_add_mes_complete_supply_chain.sql',
    'database/migrate_add_mes_scheduling_tables.sql',
    'database/migrate_add_mes_allocation_time_fields.sql',
    'database/migrate_material_purchase_like_report.sql',
    'database/migrate_purchase_inbound_like_report.sql',
    'database/migrate_stock_log_split_material_product.sql',
    'database/migrate_material_from_report.sql',
    'database/migrate_add_product_model_color_spec_remark.sql',
    'database/migrate_add_bom_template.sql',
    'database/migrate_add_default_bom_id.sql',
    'database/seed_quality_standard_templates.sql',
    // CRM
    'database/seed_crm_menu.sql',
    'database/seed_crm_customer_tag_menu.sql',
    'database/migrate_add_crm_tables.sql',
    'database/migrate_add_crm_sales_order.sql',
    'database/migrate_add_crm_timestamp_columns.sql',
    'database/migrate_crm_customer_tag.sql',
    // AI
    'database/seed_ai_menu.sql',
    'database/seed_ai_cockpit_menu.sql',
    'database/migrate_add_ai_tables.sql',
    'database/migrate_add_ai_module_switch.sql',
    'database/migrate_add_tenant_ai_module_switch_only.sql',
    'database/migrate_add_ai_package.sql',
    'database/migrate_add_ai_usage.sql',
    'database/migrate_add_ai_daily_report_only.sql',
    'database/migrate_add_ai_global_switch_columns.sql',
    // Payment
    'database/seed_payment_menu.sql',
    'database/migrate_add_payment_tables.sql',
    'database/migrate_add_payment_callback_log.sql',
    // Equipment
    'database/seed_equipment_menu.sql',
    'database/migrate_equipment.sql',
    // HR
    'database/seed_hr_menu.sql',
    'database/migrate_hr.sql',
    // Finance
    'database/seed_finance_menu.sql',
    'database/migrate_finance.sql',
    // Workflow
    'database/workflow_menu.sql',
    'database/workflow_menu_clean.sql',
    'database/seed_workflow_app_menu.sql',
    'database/migrate_wf_engine_linear.sql',
    // Wemedia
    'database/seed_wemedia_menu.sql',
    'database/migrate_wemedia.sql',
    'database/migrate_wemedia_phase1_tts.sql',
    'database/migrate_wemedia_phase2_ai_video.sql',
    // Prompt
    'database/prompt_tables.sql',
    'database/seed_prompt_data.sql',
    'database/seed_prompt_menu.sql',
    'database/migrate_add_prompt_template_ext.sql',
    'database/migrate_add_prompt_ai_media_config.sql',
    'database/migrate_add_prompt_generation_media.sql',
    'database/migrate_add_video_generation.sql',
    // Customer Service
    'database/seed_customer_service_menu.sql',
    'database/seed_customer_service_menu_v2.sql',
    'database/seed_customer_service_menu_fixed.sql',
    'database/seed_cs_menu_working.sql',
    'database/seed_cs_menu_final.sql',
    'database/migrate_customer_service.sql',
    // MES 遗漏文件
    'database/seed_material_from_report.sql',
    'database/check_mes_permissions.sql',
    'database/migrate_add_purchase_request_id_to_inbound_item.sql',
    // Workflow 遗漏
    'database/migrate_drop_legacy_workflow.sql',
    // ---- common/model：业务模块模型 ----
    'app/common/model/WemediaComplianceLogModel.php',
    'app/common/model/WemediaCopyModel.php',
    'app/common/model/WemediaMaterialModel.php',
    'app/common/model/WemediaReportModel.php',
    'app/common/model/WemediaScheduleModel.php',
    'app/common/model/WemediaTopicModel.php',
    'app/common/model/WemediaVideoScriptModel.php',
    // ---- common/lib：业务模块单文件 ----
    'app/common/lib/WemediaImageService.php',
    'app/common/lib/WemediaVideoMaker.php',
    'app/common/lib/AiService.php',
    'app/common/lib/AiVideoService.php',
    'app/common/lib/DigitalHumanService.php',
    'app/common/lib/TtsService.php',
    'app/common/lib/customer/AIService.php',
    'app/common/lib/customer/ZhipuAIService.php',
    // ---- common/middleware：业务中间件 ----
    'app/common/middleware/AICheck.php',
    'app/common/middleware/AIBilling.php',
    // ---- api/middleware：业务中间件 ----
    'app/api/middleware/MesadminPermission.php',
    'app/api/middleware/ScanworkPermission.php',
    'app/api/middleware/DeveloperAuth.php',
    // ---- command：业务命令 ----
    'app/command/RestaurantAiDailyReport.php',
    // ---- index/view：业务视图 ----
    'app/index/view/trace/detail.html',
    // ---- index/lang：业务语言包 ----
    'app/index/lang/zh-cn/Trace.php',
    'app/index/lang/en-us/Trace.php',
    'app/index/lang/ko/Trace.php',
    'app/index/lang/zh-cn/Worker.php',
    'app/index/lang/en-us/Worker.php',
    'app/index/lang/ko/Worker.php',
    // ---- 杂项 ----
    'public/test_purchase.php',
    'public/test_api.html',
    'config/scanwork_permission.php',
    'config/scanwork_permission111.php',
];
// 用基础版覆盖（路由去业务应用 require；Index 用项目自带以保留租户/菜单等）
$replaceWithBase = [
    'app/admin/route/app.php' => $root . '/build/admin_route_base.php',
    'app/index/route/app.php' => $root . '/build/index_route_base.php',
    'app/api/route/app.php'   => $root . '/build/api_route_base.php',
];
function delTree($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $f) {
        $path = $dir . '/' . $f;
        is_dir($path) ? delTree($path) : @unlink($path);
    }
    rmdir($dir);
}

function shouldExclude($relPath, $excludeDirs, $excludeFiles) {
    $relPath = str_replace('\\', '/', $relPath);
    foreach ($excludeDirs as $d) {
        if ($relPath === $d || strpos($relPath, $d . '/') === 0) return true;
    }
    if (in_array($relPath, $excludeFiles, true)) return true;
    return false;
}

function copyDir($src, $dst, $excludeDirs, $excludeFiles, $rootPath) {
    $rootLen = strlen($rootPath);
    $dir = opendir($src);
    @mkdir($dst, 0755, true);
    while (($f = readdir($dir)) !== false) {
        if ($f === '.' || $f === '..') continue;
        $srcPath = $src . '/' . $f;
        $relPath = substr($srcPath, $rootLen + 1);
        $relPath = str_replace('\\', '/', $relPath);
        if (shouldExclude($relPath, $excludeDirs, $excludeFiles)) continue;
        if (is_dir($srcPath)) {
            copyDir($srcPath, $dst . '/' . $f, $excludeDirs, $excludeFiles, $rootPath);
        } else {
            @copy($srcPath, $dst . '/' . $f);
        }
    }
    closedir($dir);
}

// 1) 复制 app, config, public, vendor、addons、extend、route、command、docs、composer 与说明类文件
$copyRootItems = ['app', 'config', 'public', 'vendor', 'composer.json', 'composer.lock', 'LICENSE', 'README.md'];
foreach (['addons', 'extend', 'route', 'command', 'docs'] as $d) {
    if (is_dir($root . '/' . $d)) {
        $copyRootItems[] = $d;
    }
}
$copyRootFiles = [
    'nginx.conf.example', 'install.sh', '.htaccess', '404.html', 'index.html',
    '.example.env', 'INSTALLATION_GUIDE.md', 'CHANGELOG.md', 'FEATURES.md',
    'LICENSE.txt', 'package-lock.json',
];
foreach ($copyRootItems as $item) {
    $src = $root . '/' . $item;
    if (!file_exists($src)) {
        continue;
    }
    $rel = $item;
    if (shouldExclude($rel, $excludeDirs, $excludeFiles)) {
        continue;
    }
    if (is_dir($src)) {
        copyDir($src, $outDir . '/' . $item, $excludeDirs, $excludeFiles, $root);
    } else {
        @copy($src, $outDir . '/' . $item);
    }
}
foreach ($copyRootFiles as $f) {
    $src = $root . '/' . $f;
    if (is_file($src)) {
        @copy($src, $outDir . '/' . $f);
    }
}

// 2) database：复制仓库内全部 SQL（便于查阅/应用中心/手工执行）；init.sql 用基础版覆盖（安装向导只执行此文件）
if (is_dir($root . '/database')) {
    @mkdir($outDir . '/database', 0755, true);
    copyDir($root . '/database', $outDir . '/database', $excludeDirs, $excludeFiles, $root);
}
$initSql = $root . '/build/database/init_base.sql';
if (!is_file($initSql)) {
    $initSql = $root . '/database/init.sql';
}
copy($initSql, $outDir . '/database/init.sql');

// 3) runtime 目录结构（空目录 + .gitkeep）
mkdir($outDir . '/runtime', 0755, true);
foreach (['cache', 'log', 'session', 'temp'] as $sub) {
    mkdir($outDir . '/runtime/' . $sub, 0755, true);
    file_put_contents($outDir . '/runtime/' . $sub . '/.gitkeep', '');
}

// 4) 用基础版覆盖（仅路由；Index 用复制过去的项目自带）
foreach ($replaceWithBase as $targetRel => $baseFile) {
    if (!is_file($baseFile)) {
        echo "警告：缺少 {$baseFile}\n";
        continue;
    }
    $targetPath = $outDir . '/' . $targetRel;
    @mkdir(dirname($targetPath), 0755, true);
    copy($baseFile, $targetPath);
}

// 5) 删除基础版不需要的单个文件（AI 包管理；api 业务控制器）
$removeFiles = [
    'app/admin/controller/AiPackage.php',
    'app/api/controller/Cockpit.php',
    'app/api/controller/Scanwork.php',
    'app/api/controller/Payment.php',
    'app/api/controller/Ai.php',
    'app/api/controller/Customer.php',
    'app/api/controller/Worker.php',
];
foreach ($removeFiles as $f) {
    $path = $outDir . '/' . $f;
    if (is_file($path)) {
        unlink($path);
        echo "已移除: {$f}\n";
    }
}

// 6) .env.example（根与 config 二选一）
if (is_file($root . '/config/.env.example')) {
    copy($root . '/config/.env.example', $outDir . '/.env.example');
} elseif (is_file($root . '/.env.example')) {
    copy($root . '/.env.example', $outDir . '/.env.example');
}

// 6b) public/uploads 占位（复制阶段排除了 uploads 内容）
$uploadsDst = $outDir . '/public/uploads';
if (!is_dir($uploadsDst)) {
    @mkdir($uploadsDst, 0755, true);
}
$gk = $root . '/public/uploads/.gitkeep';
if (is_file($gk)) {
    @copy($gk, $uploadsDst . '/.gitkeep');
}

// 6c) 打包说明放入包内（便于离线查阅）
$packDoc = $root . '/docs/基础框架打包说明.md';
if (is_file($packDoc)) {
    @mkdir($outDir . '/docs', 0755, true);
    @copy($packDoc, $outDir . '/docs/基础框架打包说明.md');
}

// 7) 安装说明.txt
$installTxt = <<<'TXT'
========================================
  ThinkMES 基础框架 - 安装说明
========================================

本包为「仅底座」版本，不含 MES/CRM/AI 等业务应用；
业务应用可通过安装后的「应用中心 → 上传应用包」安装。

【安装步骤】（与 build 一致，分步安装）

1. 解压本 zip 到服务器目录。

2. 将网站运行目录（Web 根目录）指向解压后的 public 目录。

3. 在项目根目录（与 app、public 同级）执行：composer install

4. 浏览器访问：http://你的域名/install

5. 按安装向导步骤：
   · 步骤一：同意安装协议
   · 步骤二：环境检测
   · 步骤三：填写数据库（主机、端口、库名、用户名、密码、表前缀）
   · 步骤四：设置超级管理员账号与密码
   · 步骤五：确认并执行安装

6. 安装成功后使用页面提示的后台地址登录（随机入口）。

【目录权限】runtime、public/uploads 需可写。

【附】包内含完整 database/ 目录（除 init.sql 外其它 SQL 为迁移/种子参考，按需执行；首次安装不必全部导入）。

========================================
TXT;
file_put_contents($outDir . '/安装说明.txt', $installTxt);

// 8) 创建 ZIP（输出到 dist/ 发布目录）
@mkdir(dirname($zipPath), 0755, true);
if (file_exists($zipPath)) unlink($zipPath);
$zip = new ZipArchive();
if (!$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    die("无法创建 ZIP: {$zipPath}\n");
}
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($outDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);
$baseName = 'thinkmes-base-' . $version;
foreach ($files as $file) {
    if (!$file->isDir()) {
        $path = $file->getRealPath();
        $entry = $baseName . '/' . substr($path, strlen($outDir) + 1);
        $zip->addFile($path, $entry);
    }
}
$zip->close();

echo "打包完成: {$zipPath}\n";
echo "已包含：app、config、public、vendor、extend、addons、route、command、docs、database（全量 SQL + init 为基础版）、runtime 空目录、多语言等。\n";
echo "zip 已输出到 dist/。解压后访问 /install 安装；后台仅通过随机入口访问。\n";
