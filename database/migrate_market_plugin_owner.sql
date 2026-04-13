-- 插件市场：发布者归属表（关联开发者账号，不与 C 端 user 绑定）

DROP TABLE IF EXISTS `fa_market_plugin_owner`;
CREATE TABLE `fa_market_plugin_owner` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `plugin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '市场插件ID',
  `developer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '开发者ID',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_plugin_id` (`plugin_id`),
  KEY `idx_developer` (`developer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='插件发布者归属';
