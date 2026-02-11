-- 多租户 + C端用户 表（在 init.sql 之后执行；init.sql 已含 fa_admin.tenant_id/pid/data_scope、fa_log.tenant_id）
-- 若你的是旧库未含上述字段，请先执行 database/migrate_add_tenant.sql

-- 租户表
DROP TABLE IF EXISTS `fa_tenant`;
CREATE TABLE `fa_tenant` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '租户ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '租户名称',
  `domain` varchar(255) NOT NULL DEFAULT '' COMMENT '绑定域名，多个逗号分隔',
  `package_id` int unsigned NOT NULL DEFAULT 0 COMMENT '套餐ID',
  `expire_time` int DEFAULT NULL COMMENT '到期时间 NULL=永久',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_domain` (`domain`(64)),
  KEY `idx_status_expire` (`status`,`expire_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户表';

-- 租户套餐表
DROP TABLE IF EXISTS `fa_tenant_package`;
CREATE TABLE `fa_tenant_package` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '套餐ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '套餐名称',
  `max_admin` int NOT NULL DEFAULT 10 COMMENT '最大管理员数',
  `max_user` int NOT NULL DEFAULT 1000 COMMENT '最大C端用户数',
  `expire_days` int DEFAULT NULL COMMENT '默认有效天数 NULL=永久',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户套餐表';

-- C端用户表
DROP TABLE IF EXISTS `fa_user`;
CREATE TABLE `fa_user` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '登录名',
  `password` varchar(100) NOT NULL DEFAULT '' COMMENT '密码',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱',
  `level` int NOT NULL DEFAULT 0 COMMENT '等级',
  `score` int NOT NULL DEFAULT 0 COMMENT '积分',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0禁用',
  `login_time` int DEFAULT NULL COMMENT '最后登录时间',
  `login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant_username` (`tenant_id`,`username`),
  KEY `idx_mobile` (`tenant_id`,`mobile`),
  KEY `idx_email` (`tenant_id`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='C端用户表';
