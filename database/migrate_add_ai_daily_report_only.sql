-- 仅创建 AI 生产日报/周报表（若未执行过 migrate_add_ai_tables.sql 可单独执行此文件）
-- 表前缀非 fa_ 时请替换后执行

CREATE TABLE IF NOT EXISTS `fa_ai_daily_report` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `report_type` varchar(20) NOT NULL DEFAULT 'daily' COMMENT '类型：daily/weekly',
  `report_date` date NOT NULL COMMENT '报告日期',
  `content` text COMMENT 'AI生成内容',
  `summary` varchar(500) NOT NULL DEFAULT '' COMMENT '摘要',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_tenant_type_date` (`tenant_id`, `report_type`, `report_date`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI生产日报周报';
