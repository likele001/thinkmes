#!/bin/bash
# 目录权限（请根据运行用户修改 www）
cd "$(dirname "$0")"
chmod -R 755 runtime public/uploads 2>/dev/null || true
echo "请手动执行: chown -R www:www runtime public/uploads (若需)"
echo "并导入 database/init.sql 到 MySQL 数据库 thinkmes"
