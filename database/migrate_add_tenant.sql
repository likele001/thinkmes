-- 旧库补丁：为 fa_admin、fa_log 增加租户相关字段
-- 若登录报错 "fields not exists:[tenant_id]"，说明 fa_admin 缺少该字段，请执行本文件（执行前请备份）
-- 若某条语句报 "Duplicate column name" 说明该列已存在，可跳过该条继续执行下一条

-- fa_admin：增加 tenant_id、pid、data_scope
ALTER TABLE `fa_admin` ADD COLUMN `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID，0=平台超管' AFTER `id`;
ALTER TABLE `fa_admin` ADD COLUMN `pid` int unsigned NOT NULL DEFAULT 0 COMMENT '父级管理员ID' AFTER `tenant_id`;
ALTER TABLE `fa_admin` ADD COLUMN `data_scope` tinyint NOT NULL DEFAULT 1 COMMENT '数据权限：1个人 2子级 3全部' AFTER `role_ids`;

-- fa_admin：唯一键改为 (tenant_id, username)，若已有 idx_tenant_username 可跳过下面两行
ALTER TABLE `fa_admin` DROP INDEX `idx_username`;
ALTER TABLE `fa_admin` ADD UNIQUE KEY `idx_tenant_username` (`tenant_id`,`username`);

-- fa_log：增加 tenant_id
ALTER TABLE `fa_log` ADD COLUMN `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID' AFTER `id`;
ALTER TABLE `fa_log` ADD KEY `idx_tenant_time` (`tenant_id`,`create_time`);
