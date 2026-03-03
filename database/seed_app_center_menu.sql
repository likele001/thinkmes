-- 添加「应用中心」菜单（若不存在则插入，若存在则启用）
-- 表前缀由实际配置决定，此处使用 fa_，若不同请替换后执行

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('admin/app_center/index', '应用中心', 1, 1, 1, 0, 'fas fa-th-large', 65, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `status` = 1,
    `ismenu` = 1,
    `icon` = VALUES(`icon`),
    `sort` = VALUES(`sort`),
    `update_time` = UNIX_TIMESTAMP();
