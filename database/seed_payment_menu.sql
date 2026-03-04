-- 支付应用菜单（单用户版：支付配置、订单管理、回调日志、统计报表，不分租户）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('payment', '支付管理', 1, 1, 1, 0, 'fa fa-credit-card', 90, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `status` = VALUES(`status`);

SET @pay_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'payment' LIMIT 1), 0);

-- 1. 支付配置（网关列表，内含官方支付宝/微信、虎皮椒、易支付等）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('payment/config', '支付配置', 1, 1, 1, @pay_pid, 'fa fa-cog', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @pay_pid;

SET @cfg_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'payment/config' LIMIT 1), 0);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('payment/config/index', '网关列表', 2, 0, 1, @cfg_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('payment/config/add', '添加网关', 2, 0, 1, @cfg_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('payment/config/edit', '编辑网关', 2, 0, 1, @cfg_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('payment/config/del', '删除网关', 2, 0, 1, @cfg_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @cfg_pid;

-- 2. 订单管理
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('payment/order', '订单管理', 1, 1, 1, @pay_pid, 'fa fa-list', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @pay_pid;

SET @ord_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'payment/order' LIMIT 1), 0);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('payment/order/index', '订单列表', 2, 0, 1, @ord_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @ord_pid;

-- 3. 回调日志
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('payment/callback_log', '回调日志', 1, 1, 1, @pay_pid, 'fa fa-file-alt', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @pay_pid;

SET @log_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'payment/callback_log' LIMIT 1), 0);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('payment/callback_log/index', '日志列表', 2, 0, 1, @log_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @log_pid;

-- 4. 统计报表
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('payment/stats', '统计报表', 1, 1, 1, @pay_pid, 'fa fa-chart-bar', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @pay_pid;

SET @stats_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'payment/stats' LIMIT 1), 0);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('payment/stats/index', '报表', 2, 0, 1, @stats_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @stats_pid;
