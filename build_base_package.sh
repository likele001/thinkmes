#!/usr/bin/env bash
# 基础框架打包脚本：排除 MES/CRM/AI/设备/人事/财务/支付 等业务应用，仅保留后台底座。
# 用法：./build_base_package.sh [版本号，默认 1.0.0]

set -e
VERSION="${1:-1.0.0}"
ROOT="$(cd "$(dirname "$0")" && pwd)"
OUTPUT_NAME="thinkmes-base-${VERSION}"
TMP="$(mktemp -d)"
TMP_PROJECT="${TMP}/${OUTPUT_NAME}"

echo "[build] 复制项目到临时目录（排除 .git / runtime / vendor）..."
rsync -a --no-owner --no-group \
  --exclude '.git' \
  --exclude 'runtime' \
  --exclude 'vendor' \
  --exclude 'node_modules' \
  --exclude '.env' \
  --exclude '*.zip' \
  --exclude 'build_base_package.sh' \
  --exclude 'public/uploads' \
  --exclude 'build' \
  --exclude 'dist' \
  "$ROOT/" "$TMP_PROJECT/"

cd "$TMP_PROJECT"

echo "[build] 移除业务应用控制器及单文件..."
rm -rf \
  app/admin/controller/mes \
  app/admin/controller/crm \
  app/admin/controller/ai \
  app/admin/controller/payment \
  app/admin/controller/equipment \
  app/admin/controller/hr \
  app/admin/controller/finance
rm -f app/admin/controller/AiPackage.php

echo "[build] 移除业务应用模型..."
rm -rf \
  app/admin/model/mes \
  app/admin/model/crm \
  app/admin/model/payment \
  app/admin/model/equipment \
  app/admin/model/hr \
  app/admin/model/finance
[ -d app/admin/model/ai ] && rm -rf app/admin/model/ai

echo "[build] 移除业务应用视图..."
rm -rf \
  app/admin/view/mes \
  app/admin/view/crm \
  app/admin/view/ai \
  app/admin/view/payment \
  app/admin/view/equipment \
  app/admin/view/hr \
  app/admin/view/finance

echo "[build] 移除业务应用路由..."
rm -f \
  app/admin/route/mes.php \
  app/admin/route/crm.php \
  app/admin/route/ai.php \
  app/admin/route/payment.php \
  app/admin/route/equipment.php \
  app/admin/route/hr.php \
  app/admin/route/finance.php

echo "[build] 移除 API 中业务应用控制器..."
for f in Cockpit Scanwork Payment Ai Customer Worker; do
  [ -f "app/api/controller/${f}.php" ] && rm -f "app/api/controller/${f}.php"
done

echo "[build] 移除业务应用相关数据库迁移与种子..."
# 迁移：仅保留租户、权限、配置、打印短信、套餐订单等底座相关
rm -f database/migrate_add_mes_tables.sql
rm -f database/migrate_add_mes_extended_tables.sql
rm -f database/migrate_add_mes_complete_supply_chain.sql
rm -f database/migrate_add_crm_tables.sql
rm -f database/migrate_add_crm_sales_order.sql
rm -f database/migrate_add_crm_timestamp_columns.sql
rm -f database/migrate_crm_customer_tag.sql
rm -f database/migrate_add_customer_portal.sql
rm -f database/migrate_add_ai_tables.sql
rm -f database/migrate_add_ai_package.sql
rm -f database/migrate_add_ai_usage.sql
rm -f database/migrate_add_ai_module_switch.sql
rm -f database/migrate_add_ai_global_switch_columns.sql
rm -f database/migrate_add_payment_tables.sql
rm -f database/migrate_add_payment_callback_log.sql
rm -f database/migrate_equipment.sql
rm -f database/migrate_hr.sql
rm -f database/migrate_finance.sql
# 种子：仅保留底座菜单与权限
rm -f database/seed_mes_menu.sql
rm -f database/seed_mes_stock_mrp.sql
rm -f database/seed_mes_auth_rules.sql
rm -f database/seed_crm_menu.sql
rm -f database/seed_crm_customer_tag_menu.sql
rm -f database/seed_ai_menu.sql
rm -f database/seed_ai_cockpit_menu.sql
rm -f database/seed_payment_menu.sql
rm -f database/seed_equipment_menu.sql
rm -f database/seed_hr_menu.sql
rm -f database/seed_finance_menu.sql
# 全量菜单种子包含各应用，基础版不包含
rm -f database/seed_menu_full_saas_factory.sql

echo "[build] 确保 runtime 目录结构（安装前可写）..."
mkdir -p runtime/cache runtime/log runtime/session runtime/temp
for d in cache log session temp; do
  touch "runtime/$d/.gitkeep" 2>/dev/null || true
done

echo "[build] 写入安装说明..."
cat > 安装说明.txt << 'INSTALL_TXT'
========================================
  ThinkMES 基础框架 - 安装说明
========================================

本包为「仅底座」版本，不含 MES/CRM/AI 等业务应用；
业务应用可通过安装后的「应用中心 → 上传应用包」安装。

【安装步骤】（与 build 分步安装一致）

1. 解压本 zip 到服务器目录。

2. 将网站运行目录（Web 根目录/站点目录）指向解压后的 public 目录。
   （例如 Nginx root 填：/path/to/解压后的目录/public）

3. 确保已安装 PHP 8.0+、MySQL 5.7+，且已执行：composer install
   （在解压后的项目根目录执行，即与 app、public 同级）

4. 浏览器访问：http://你的域名/install

5. 按安装向导步骤操作：
   · 步骤一：同意安装协议
   · 步骤二：环境检测（PHP 扩展、目录可写）
   · 步骤三：填写数据库信息（主机、端口、库名、用户名、密码、表前缀）
   · 步骤四：设置超级管理员账号与密码
   · 步骤五：确认并执行安装

6. 安装成功后，请使用页面提示的后台地址登录（随机入口，增强安全）。
   若需重新安装，可访问 /install?reinstall=1。

【目录权限】
  runtime、public/uploads 需可写，建议：chmod -R 755 runtime public/uploads

========================================
INSTALL_TXT

# 若存在 .env.example 则保留（供参考）；安装向导会生成 .env
if [ -f "$ROOT/config/.env.example" ] && [ ! -f "$TMP_PROJECT/.env.example" ]; then
  cp "$ROOT/config/.env.example" "$TMP_PROJECT/.env.example"
fi

echo "[build] 生成 zip（包内一层目录 ${OUTPUT_NAME}/）..."
DIST_DIR="$ROOT/dist"
mkdir -p "$DIST_DIR"
ZIP_PATH="$DIST_DIR/${OUTPUT_NAME}.zip"
rm -f "$ZIP_PATH"
cd "$TMP"
zip -rq "$ZIP_PATH" "$OUTPUT_NAME" -x "*.git*" -x "*.zip"

rm -rf "$TMP"
echo "[build] 完成: $ZIP_PATH"
echo "[build] 用户解压后访问 /install 按步骤安装（数据库等）。"
