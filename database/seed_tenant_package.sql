-- 租户套餐默认数据（可选，执行 init_tenant_user.sql 后可执行）
INSERT INTO `fa_tenant_package` (`id`, `name`, `max_admin`, `max_user`, `expire_days`, `sort`, `create_time`, `update_time`) VALUES
(1, '基础版', 5, 100, 365, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, '标准版', 20, 1000, 365, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, '专业版', 50, 5000, NULL, 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `max_admin` = VALUES(`max_admin`), `max_user` = VALUES(`max_user`), `expire_days` = VALUES(`expire_days`), `sort` = VALUES(`sort`);
