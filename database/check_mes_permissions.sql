-- 检查MES权限规则完整性
-- 用于验证所有路由都有对应的权限规则

-- 1. 检查主菜单
SELECT '主菜单' as type, COUNT(*) as count FROM fa_auth_rule WHERE name LIKE 'mes/%' AND ismenu=1;

-- 2. 检查权限规则
SELECT '权限规则' as type, COUNT(*) as count FROM fa_auth_rule WHERE name LIKE 'mes/%' AND ismenu=0;

-- 3. 列出所有MES菜单（按排序）
SELECT name, title, sort, pid FROM fa_auth_rule WHERE name LIKE 'mes/%' AND ismenu=1 ORDER BY sort;

-- 4. 检查是否有遗漏的路由权限（需要手动对比路由文件）
-- 主要路由：
-- mes/index - MES首页
-- mes/order/* - 订单管理
-- mes/product/* - 产品管理
-- mes/product_model/* - 产品型号
-- mes/bom/* - BOM管理
-- mes/process/* - 工序管理
-- mes/process_price/* - 工序工价
-- mes/production_plan/* - 生产计划
-- mes/allocation/* - 分工分配
-- mes/report/* - 报工管理
-- mes/wage/* - 工资管理
-- mes/trace_code/* - 追溯码管理
-- mes/customer/* - 客户管理
-- mes/material/* - 物料管理
-- mes/supplier/* - 供应商管理
