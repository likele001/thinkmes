-- 确保超级管理员存在（账号 admin 密码 123456）
-- 若提示「账号不存在或已禁用」，在项目库中执行: mysql -u thinkmes -p thinkmes < database/seed_admin.sql

REPLACE INTO `fa_admin` (`id`, `username`, `password`, `salt`, `nickname`, `role_ids`, `status`, `create_time`, `update_time`)
VALUES (1, 'admin', '$2y$10$FgTjiHSfat5J4izn09x4u.nZ0d/aiDm0dWXN7YEZBteofm7D6M2Ma', 'fast', '超级管理员', '1', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
