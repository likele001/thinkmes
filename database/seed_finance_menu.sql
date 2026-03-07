-- 财务模块菜单与权限（一级菜单 finance）
-- 表前缀 fa_

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('finance', '财务管理', 1, 1, 1, 0, 'fa fa-yen-sign', 88, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`);

SET @finance_pid = (SELECT id FROM fa_auth_rule WHERE name = 'finance' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('finance/receivable', '应收账款', 1, 1, 1, @finance_pid, 'fa fa-money-bill-wave', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/payable', '应付账款', 1, 1, 1, @finance_pid, 'fa fa-credit-card', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/receive', '收款登记', 1, 1, 1, @finance_pid, 'fa fa-hand-holding-usd', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/pay', '付款登记', 1, 1, 1, @finance_pid, 'fa fa-file-invoice-dollar', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/profit', '利润统计', 1, 1, 1, @finance_pid, 'fa fa-chart-line', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/statement', '对账单', 1, 1, 1, @finance_pid, 'fa fa-file-invoice', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @finance_pid;

SET @rec_pid = (SELECT id FROM fa_auth_rule WHERE name = 'finance/receivable' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('finance/receivable/index', '列表', 2, 0, 1, @rec_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/receivable/add', '添加', 2, 0, 1, @rec_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/receivable/edit', '编辑', 2, 0, 1, @rec_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/receivable/del', '删除', 2, 0, 1, @rec_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @rec_pid;

SET @pay_pid = (SELECT id FROM fa_auth_rule WHERE name = 'finance/payable' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('finance/payable/index', '列表', 2, 0, 1, @pay_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/payable/add', '添加', 2, 0, 1, @pay_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/payable/edit', '编辑', 2, 0, 1, @pay_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/payable/del', '删除', 2, 0, 1, @pay_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @pay_pid;

SET @receive_pid = (SELECT id FROM fa_auth_rule WHERE name = 'finance/receive' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('finance/receive/index', '列表', 2, 0, 1, @receive_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/receive/add', '登记', 2, 0, 1, @receive_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @receive_pid;

SET @payreg_pid = (SELECT id FROM fa_auth_rule WHERE name = 'finance/pay' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('finance/pay/index', '列表', 2, 0, 1, @payreg_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('finance/pay/add', '登记', 2, 0, 1, @payreg_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @payreg_pid;

SET @profit_pid = (SELECT id FROM fa_auth_rule WHERE name = 'finance/profit' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('finance/profit/index', '查看', 2, 0, 1, @profit_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @profit_pid;

SET @stmt_pid = (SELECT id FROM fa_auth_rule WHERE name = 'finance/statement' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('finance/statement/index', '查看', 2, 0, 1, @stmt_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @stmt_pid;
