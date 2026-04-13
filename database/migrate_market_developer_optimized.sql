-- 插件市场：开发者账号（独立于 C 端 user）
-- 优化版本：增加性能索引和安全约束

CREATE TABLE IF NOT EXISTS `fa_market_developer` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `account` varchar(64) NOT NULL DEFAULT '' COMMENT '登录账号（唯一）',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '显示名称',
  `password_hash` varchar(255) NOT NULL DEFAULT '' COMMENT '密码哈希',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用 0禁用',
  `last_login_time` int unsigned NOT NULL DEFAULT 0 COMMENT '最后登录时间',
  `last_login_ip` varchar(64) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_account` (`account`),
  KEY `idx_status` (`status`),
  KEY `idx_last_login` (`last_login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='插件市场开发者';

-- 添加约束确保账号格式正确
ALTER TABLE `fa_market_developer` 
  ADD CONSTRAINT `chk_account_format` CHECK (account REGEXP '^[a-z0-9][a-z0-9._-]{2,40}$');
