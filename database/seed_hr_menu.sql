-- 人事考勤菜单与权限（一级菜单 hr）
-- 表前缀 fa_

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('hr', '人事考勤', 1, 1, 1, 0, 'fa fa-user-clock', 91, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`);

SET @hr_pid = (SELECT id FROM fa_auth_rule WHERE name = 'hr' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('hr/department', '部门管理', 1, 1, 1, @hr_pid, 'fa fa-sitemap', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/position', '岗位管理', 1, 1, 1, @hr_pid, 'fa fa-briefcase', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/employee', '员工档案', 1, 1, 1, @hr_pid, 'fa fa-users', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/attendance', '考勤打卡', 1, 1, 1, @hr_pid, 'fa fa-clock', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/leave', '请假管理', 1, 1, 1, @hr_pid, 'fa fa-calendar-minus', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/overtime', '加班管理', 1, 1, 1, @hr_pid, 'fa fa-calendar-plus', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @hr_pid;

SET @dept_pid = (SELECT id FROM fa_auth_rule WHERE name = 'hr/department' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('hr/department/index', '列表', 2, 0, 1, @dept_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/department/add', '添加', 2, 0, 1, @dept_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/department/edit', '编辑', 2, 0, 1, @dept_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/department/del', '删除', 2, 0, 1, @dept_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @dept_pid;

SET @pos_pid = (SELECT id FROM fa_auth_rule WHERE name = 'hr/position' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('hr/position/index', '列表', 2, 0, 1, @pos_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/position/add', '添加', 2, 0, 1, @pos_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/position/edit', '编辑', 2, 0, 1, @pos_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/position/del', '删除', 2, 0, 1, @pos_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @pos_pid;

SET @emp_pid = (SELECT id FROM fa_auth_rule WHERE name = 'hr/employee' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('hr/employee/index', '列表', 2, 0, 1, @emp_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/employee/add', '添加', 2, 0, 1, @emp_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/employee/edit', '编辑', 2, 0, 1, @emp_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/employee/del', '删除', 2, 0, 1, @emp_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @emp_pid;

SET @att_pid = (SELECT id FROM fa_auth_rule WHERE name = 'hr/attendance' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('hr/attendance/index', '列表', 2, 0, 1, @att_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @att_pid;

SET @leave_pid = (SELECT id FROM fa_auth_rule WHERE name = 'hr/leave' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('hr/leave/index', '列表', 2, 0, 1, @leave_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/leave/add', '添加', 2, 0, 1, @leave_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/leave/edit', '编辑', 2, 0, 1, @leave_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/leave/del', '删除', 2, 0, 1, @leave_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/leave/approve', '审批', 2, 0, 1, @leave_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @leave_pid;

SET @ot_pid = (SELECT id FROM fa_auth_rule WHERE name = 'hr/overtime' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('hr/overtime/index', '列表', 2, 0, 1, @ot_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/overtime/add', '添加', 2, 0, 1, @ot_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/overtime/edit', '编辑', 2, 0, 1, @ot_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/overtime/del', '删除', 2, 0, 1, @ot_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('hr/overtime/approve', '审批', 2, 0, 1, @ot_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @ot_pid;
