-- 客户标签菜单（挂在 CRM 下）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
SELECT 'crm/customer_tag', '客户标签', 1, 1, 1, id, 'fa fa-tags', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP() FROM `fa_auth_rule` WHERE name = 'crm' LIMIT 1
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`);
