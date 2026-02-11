-- 若侧栏菜单为空，可执行本文件初始化菜单（与 init.sql 一致 + 租户/用户/文件管理）
-- 执行前请确认 fa_auth_rule 表已存在

INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(1, 'admin/index/index', '首页', 1, 1, 1, 0, 'fas fa-tachometer-alt', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, 'admin/admin/index', '管理员管理', 1, 1, 1, 0, 'fas fa-user-shield', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, 'admin/role/index', '角色管理', 1, 1, 1, 0, 'fas fa-users-cog', 20, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(4, 'admin/auth_rule/index', '权限规则', 1, 1, 1, 0, 'fas fa-sitemap', 30, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, 'admin/config/index', '系统配置', 1, 1, 1, 0, 'fas fa-cog', 40, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(6, 'admin/log/index', '操作日志', 1, 1, 1, 0, 'fas fa-history', 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`);

-- 新增：租户管理、套餐管理、订单管理、用户管理、文件管理（若已存在则更新）
INSERT INTO `fa_auth_rule` (`id`, `name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
(7, 'admin/tenant/index', '租户管理', 1, 1, 1, 0, 'fas fa-building', 15, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(10, 'admin/tenant_package/index', '套餐管理', 1, 1, 1, 0, 'fas fa-box', 16, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(11, 'admin/tenant_order/index', '订单管理', 1, 1, 1, 0, 'fas fa-shopping-cart', 17, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, 'admin/member/index', '用户管理', 1, 1, 1, 0, 'fas fa-user', 35, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(9, 'admin/attachment/index', '文件管理', 1, 1, 1, 0, 'fas fa-file-alt', 38, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`);
