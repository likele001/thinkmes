-- 一键初始化所有表的SQL文件
-- 按顺序执行所有必要的表创建和初始化

-- 1. 基础表结构（如果已执行可跳过）
-- source database/init.sql;

-- 2. 租户和用户表（如果已执行可跳过）
-- source database/init_tenant_user.sql;

-- 3. 套餐功能表
source database/migrate_add_tenant_package_feature.sql;

-- 4. 订单表
source database/migrate_add_tenant_order.sql;

-- 5. 初始化权限规则
source database/init_auth_rules_complete.sql;

-- 6. 初始化套餐数据（可选）
source database/seed_tenant_package.sql;

-- 执行完成后，刷新页面即可看到数据
