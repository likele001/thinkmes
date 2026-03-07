-- 经营数据大屏菜单（挂在 工厂AI 下）
SET @ai_pid = (SELECT id FROM fa_auth_rule WHERE name = 'ai' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('ai/cockpit', '经营数据大屏', 1, 1, 1, @ai_pid, 'fa fa-tachometer-alt', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `pid` = @ai_pid;
