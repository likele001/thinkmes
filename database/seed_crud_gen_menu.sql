-- 在线命令 / CRUD 一键生成 菜单（系统管理下）
-- 执行：mysql -u用户 -p 数据库名 < database/seed_crud_gen_menu.sql
-- 父级为「系统管理」admin/_sys（id=9 为 init.sql 默认）

SET @sys_pid = (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/_sys' LIMIT 1);
SET @sys_pid = IFNULL(@sys_pid, 9);

-- 一级菜单：在线命令（列表页）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/crud_gen/index', '在线命令', 1, 1, 1, @sys_pid, 'fas fa-terminal', 45, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @sys_pid, `update_time` = UNIX_TIMESTAMP();

-- 子节点（不显示在侧栏，用于权限/按钮）
SET @crud_pid = (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/crud_gen/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/crud_gen/add', '添加', 2, 0, 1, @crud_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/crud_gen/command', '生成/执行', 2, 0, 1, @crud_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/crud_gen/detail', '详情', 2, 0, 1, @crud_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/crud_gen/reExecute', '再次执行', 2, 0, 1, @crud_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/crud_gen/del', '删除', 2, 0, 1, @crud_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @crud_pid, `update_time` = UNIX_TIMESTAMP();
