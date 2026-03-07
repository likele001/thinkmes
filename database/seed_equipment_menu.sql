-- 设备管理菜单与权限（一级菜单 equipment，与 route 的 equipment/* 对应）
-- 表前缀 fa_，执行前请确认与 .env DB_PREFIX 一致

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('equipment', '设备管理', 1, 1, 1, 0, 'fa fa-tools', 92, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`);

SET @equipment_pid = (SELECT id FROM fa_auth_rule WHERE name = 'equipment' LIMIT 1);

-- 设备档案
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('equipment/equipment', '设备档案', 1, 1, 1, @equipment_pid, 'fa fa-cog', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @equipment_pid;

SET @equipment_archive_pid = (SELECT id FROM fa_auth_rule WHERE name = 'equipment/equipment' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('equipment/equipment/index', '列表', 2, 0, 1, @equipment_archive_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/equipment/add', '添加', 2, 0, 1, @equipment_archive_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/equipment/edit', '编辑', 2, 0, 1, @equipment_archive_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/equipment/del', '删除', 2, 0, 1, @equipment_archive_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/equipment/stat', '利用率统计', 1, 1, 1, @equipment_archive_pid, 'fa fa-chart-pie', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @equipment_archive_pid;

-- 保养计划
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('equipment/maintenance', '保养计划', 1, 1, 1, @equipment_pid, 'fa fa-calendar-check', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @equipment_pid;

SET @maint_pid = (SELECT id FROM fa_auth_rule WHERE name = 'equipment/maintenance' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('equipment/maintenance/index', '列表', 2, 0, 1, @maint_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/maintenance/add', '添加', 2, 0, 1, @maint_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/maintenance/edit', '编辑', 2, 0, 1, @maint_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/maintenance/del', '删除', 2, 0, 1, @maint_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @maint_pid;

-- 点检记录
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('equipment/check', '点检记录', 1, 1, 1, @equipment_pid, 'fa fa-clipboard-check', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @equipment_pid;

SET @check_pid = (SELECT id FROM fa_auth_rule WHERE name = 'equipment/check' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('equipment/check/index', '列表', 2, 0, 1, @check_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/check/add', '添加', 2, 0, 1, @check_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/check/edit', '编辑', 2, 0, 1, @check_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/check/del', '删除', 2, 0, 1, @check_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @check_pid;

-- 维修记录
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('equipment/repair', '维修记录', 1, 1, 1, @equipment_pid, 'fa fa-wrench', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @equipment_pid;

SET @repair_pid = (SELECT id FROM fa_auth_rule WHERE name = 'equipment/repair' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('equipment/repair/index', '列表', 2, 0, 1, @repair_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/repair/add', '添加', 2, 0, 1, @repair_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/repair/edit', '编辑', 2, 0, 1, @repair_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/repair/del', '删除', 2, 0, 1, @repair_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @repair_pid;

-- 运行记录
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('equipment/runtime', '运行记录', 1, 1, 1, @equipment_pid, 'fa fa-tachometer-alt', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @equipment_pid;

SET @runtime_pid = (SELECT id FROM fa_auth_rule WHERE name = 'equipment/runtime' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('equipment/runtime/index', '列表', 2, 0, 1, @runtime_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/runtime/add', '添加', 2, 0, 1, @runtime_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/runtime/edit', '编辑', 2, 0, 1, @runtime_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('equipment/runtime/del', '删除', 2, 0, 1, @runtime_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @runtime_pid;
