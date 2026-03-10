-- 租户小程序配置：权限规则（供套餐功能分配，feature_code = admin/tenant/miniapp）
-- 执行后可在「租户套餐」→ 某套餐 →「套餐功能」→ 添加功能 中勾选「小程序（租户小程序配置）」分配给该套餐
-- 表前缀与 .env DB_PREFIX 一致，默认 fa_

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/tenant/miniapp', '租户小程序配置', 1, 1, 1, 10, 'fa fa-mobile', 59, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`), `pid` = VALUES(`pid`);
