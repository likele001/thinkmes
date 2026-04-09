-- 租户小程序：菜单 + 套餐功能（一次性补丁）

-- 1) 菜单（挂到「租户与用户」分组下）
SET @tenant_group = COALESCE((SELECT id FROM fa_auth_rule WHERE name='admin/_tenant_user' LIMIT 1), 0);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('admin/tenant/miniapp', '租户小程序', 1, 1, 1, @tenant_group, 'fas fa-mobile-alt', 15, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = VALUES(`pid`), `ismenu` = 1, `status` = 1, `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `update_time` = UNIX_TIMESTAMP();

-- 2) 套餐功能：给所有已存在套餐补上「小程序」功能（否则租户端会提示未开通）
INSERT INTO `fa_tenant_package_feature` (`package_id`, `feature_code`, `feature_name`, `create_time`)
SELECT p.id, 'admin/tenant/miniapp', '小程序', UNIX_TIMESTAMP()
FROM `fa_tenant_package` p
ON DUPLICATE KEY UPDATE `feature_name` = VALUES(`feature_name`);

