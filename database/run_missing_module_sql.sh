#!/bin/bash
# 缺失模块与功能开发计划 - 所需 SQL 按顺序执行
# 使用 .env 中的数据库配置，需在项目根目录执行

cd "$(dirname "$0")/.." || exit 1
source .env 2>/dev/null || true
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_NAME:-thinkmes}"
DB_USER="${DB_USER:-thinkmes}"
DB_PASS="${DB_PASS:-123456}"

run() {
  local f="$1"
  if [ -f "database/$f" ]; then
    echo ">>> $f"
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" 2>/dev/null < "database/$f" && echo "    OK" || echo "    FAIL"
  else
    echo ">>> $f (文件不存在，跳过)"
  fi
}

echo "========== 迁移（建表）=========="
run migrate_equipment.sql
run migrate_hr.sql
run migrate_finance.sql
run migrate_print_sms.sql
run migrate_crm_customer_tag.sql

echo "========== 菜单种子 =========="
run seed_equipment_menu.sql
run seed_hr_menu.sql
run seed_finance_menu.sql
run seed_print_sms_menu.sql
run seed_crm_customer_tag_menu.sql
run seed_mes_stock_mrp.sql
run seed_ai_cockpit_menu.sql

echo "========== 完成 =========="
