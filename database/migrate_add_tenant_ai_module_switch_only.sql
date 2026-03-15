-- 仅创建租户 AI 子功能开关表（解决 updateGlobal 报错 Table 'fa_tenant_ai_module_switch' doesn't exist）
-- 若表前缀不是 fa_，请将 fa_ 替换为你的实际前缀后执行

CREATE TABLE IF NOT EXISTS `fa_tenant_ai_module_switch` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `module` varchar(32) NOT NULL DEFAULT '' COMMENT 'voice_report/anomaly/qa/crm_follow',
  `enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1开 0关',
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_module` (`tenant_id`,`module`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户AI子功能开关覆盖';
