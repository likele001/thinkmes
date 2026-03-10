-- 自媒体工作流：独立一级菜单（与系统管理、扩展功能、租户与用户同级）
-- 执行：mysql -u用户 -p 数据库名 < database/seed_wemedia_menu.sql

-- 一级菜单：自媒体工作流（pid=0 独立应用）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/_wemedia', '自媒体工作流', 1, 1, 1, 0, 'fas fa-video', 8, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = 0, `update_time` = UNIX_TIMESTAMP();

SET @wemedia_pid = (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/_wemedia' LIMIT 1);

-- 子菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/wemedia_config/index', '自媒体配置', 1, 1, 1, @wemedia_pid, 'fas fa-cog', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/wemedia_topic/index', '选题管理', 1, 1, 1, @wemedia_pid, 'fas fa-lightbulb', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/wemedia_copy/index', '文案管理', 1, 1, 1, @wemedia_pid, 'fas fa-file-alt', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/wemedia_material/index', '素材管理', 1, 1, 1, @wemedia_pid, 'fas fa-images', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/wemedia_video/index', '短视频管理', 1, 1, 1, @wemedia_pid, 'fas fa-film', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/wemedia_schedule/index', '排期管理', 1, 1, 1, @wemedia_pid, 'fas fa-calendar-alt', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/wemedia_report/index', '数据复盘管理', 1, 1, 1, @wemedia_pid, 'fas fa-chart-line', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/wemedia_compliance/index', '合规记录', 1, 1, 1, @wemedia_pid, 'fas fa-shield-alt', 8, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @wemedia_pid, `update_time` = UNIX_TIMESTAMP();

-- 操作权限（列表页需 index，删除需 del；配置页提交走 index POST）
SET @cfg_pid = (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/wemedia_config/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/wemedia_config/save', '保存配置', 2, 0, 1, @cfg_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @cfg_pid, `update_time` = UNIX_TIMESTAMP();

SET @topic_pid = (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/wemedia_topic/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/wemedia_topic/del', '删除', 2, 0, 1, @topic_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @topic_pid, `update_time` = UNIX_TIMESTAMP();

SET @copy_pid = (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/wemedia_copy/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/wemedia_copy/del', '删除', 2, 0, 1, @copy_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @copy_pid, `update_time` = UNIX_TIMESTAMP();

SET @mat_pid = (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/wemedia_material/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/wemedia_material/del', '删除', 2, 0, 1, @mat_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @mat_pid, `update_time` = UNIX_TIMESTAMP();

SET @video_pid = (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/wemedia_video/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/wemedia_video/del', '删除', 2, 0, 1, @video_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @video_pid, `update_time` = UNIX_TIMESTAMP();

SET @sch_pid = (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/wemedia_schedule/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/wemedia_schedule/del', '删除', 2, 0, 1, @sch_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @sch_pid, `update_time` = UNIX_TIMESTAMP();

SET @rpt_pid = (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/wemedia_report/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/wemedia_report/del', '删除', 2, 0, 1, @rpt_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @rpt_pid, `update_time` = UNIX_TIMESTAMP();

SET @comp_pid = (SELECT id FROM `fa_auth_rule` WHERE `name` = 'admin/wemedia_compliance/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/wemedia_compliance/del', '删除', 2, 0, 1, @comp_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @comp_pid, `update_time` = UNIX_TIMESTAMP();
