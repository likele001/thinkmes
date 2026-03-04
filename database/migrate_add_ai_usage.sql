-- AI 使用统计表（统计每日调用次数与消耗 token）
DROP TABLE IF EXISTS `fa_ai_usage`;
CREATE TABLE `fa_ai_usage` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `admin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '管理员ID',
  `module` varchar(100) NOT NULL DEFAULT '' COMMENT '模块/功能',
  `action` varchar(100) NOT NULL DEFAULT '' COMMENT '动作',
  `stat_date` date NOT NULL COMMENT '日期',
  `call_count` int NOT NULL DEFAULT 0 COMMENT '调用次数',
  `tokens_used` int NOT NULL DEFAULT 0 COMMENT '消耗 tokens',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_tenant_module_action_date` (`tenant_id`,`module`,`action`,`stat_date`),
  KEY `idx_tenant_date` (`tenant_id`,`stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI 使用统计（每日）';
