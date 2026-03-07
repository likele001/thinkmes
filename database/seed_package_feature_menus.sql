-- 套餐功能对应的侧栏菜单（报表统计、数据导出、API接口访问、自定义字段、工作流、消息通知、数据备份）
-- 归入「扩展功能」父级下，与 init 中的 admin/_ext(id=10) 一致；name 需与 tenant_package_feature.feature_code 一致
-- 表前缀与 .env DB_PREFIX 一致，默认 fa_

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('report', '报表统计', 1, 1, 1, 10, 'fas fa-chart-pie', 60, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('export', '数据导出', 1, 1, 1, 10, 'fas fa-file-export', 61, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('api', 'API接口访问', 1, 1, 1, 10, 'fas fa-plug', 62, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('custom_field', '自定义字段', 1, 1, 1, 10, 'fas fa-list-alt', 63, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('workflow', '工作流', 1, 1, 1, 10, 'fas fa-project-diagram', 64, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('notification', '消息通知', 1, 1, 1, 10, 'fas fa-bell', 65, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('backup', '数据备份', 1, 1, 1, 10, 'fas fa-database', 66, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`), `pid` = VALUES(`pid`);

-- 若 init 中扩展功能父级为其他 id，可执行下面一句修正（将 10 改为实际 admin/_ext 的 id）
-- UPDATE fa_auth_rule a INNER JOIN (SELECT id FROM fa_auth_rule WHERE name='admin/_ext') b ON 1=1 SET a.pid = b.id WHERE a.name IN ('report','export','api','custom_field','workflow','notification','backup');
