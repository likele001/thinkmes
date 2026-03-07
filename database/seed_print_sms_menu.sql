-- 打印模板、短信配置菜单（系统底座下）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/print_template', '打印模板', 1, 1, 1, 0, 'fa fa-print', 25, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/sms_config', '短信配置', 1, 1, 1, 0, 'fa fa-sms', 26, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`);

SET @pt_pid = (SELECT id FROM fa_auth_rule WHERE name = 'admin/print_template' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/print_template/index', '列表', 2, 0, 1, @pt_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/print_template/add', '添加', 2, 0, 1, @pt_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/print_template/edit', '编辑', 2, 0, 1, @pt_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/print_template/del', '删除', 2, 0, 1, @pt_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @pt_pid;

SET @sms_pid = (SELECT id FROM fa_auth_rule WHERE name = 'admin/sms_config' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/sms_config/index', '查看', 2, 0, 1, @sms_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @sms_pid;
