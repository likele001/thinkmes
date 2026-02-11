-- 检查权限规则数据的SQL
-- 如果查询结果为空，说明需要执行初始化SQL

-- 检查权限规则总数
SELECT COUNT(*) as total FROM `fa_auth_rule`;

-- 检查菜单规则（type=1, ismenu=1）
SELECT COUNT(*) as menu_count FROM `fa_auth_rule` WHERE `type` = 1 AND `ismenu` = 1;

-- 查看所有权限规则
SELECT `id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `sort` FROM `fa_auth_rule` ORDER BY `sort`, `id`;

-- 如果上述查询结果为空，请执行：
-- mysql -uroot -p thinkmes < database/init_auth_rules_complete.sql
