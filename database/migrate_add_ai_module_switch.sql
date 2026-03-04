-- AI 四个子功能单独开关：报工、异常检测、智能问答、CRM自动跟单
-- 平台级在 fa_ai_global_switch 四列；租户级覆盖在 fa_tenant_ai_module_switch（可选）
-- 表前缀由安装脚本替换 `fa_` -> 实际前缀；此处用 LIKE 取实际表名以兼容任意前缀

SET @dbname = DATABASE();
SET @tname = (SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME LIKE '%ai_global_switch' LIMIT 1);

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tname AND COLUMN_NAME = 'switch_voice_report') > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tname, '` ADD COLUMN `switch_voice_report` tinyint(1) NOT NULL DEFAULT 1 COMMENT ''语音报工：1开 0关'' AFTER `notice`')
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tname AND COLUMN_NAME = 'switch_anomaly') > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tname, '` ADD COLUMN `switch_anomaly` tinyint(1) NOT NULL DEFAULT 1 COMMENT ''异常检测：1开 0关''')
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tname AND COLUMN_NAME = 'switch_qa') > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tname, '` ADD COLUMN `switch_qa` tinyint(1) NOT NULL DEFAULT 1 COMMENT ''智能问答：1开 0关''')
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tname AND COLUMN_NAME = 'switch_crm_follow') > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tname, '` ADD COLUMN `switch_crm_follow` tinyint(1) NOT NULL DEFAULT 1 COMMENT ''CRM自动跟单：1开 0关''')
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 租户级模块开关覆盖表（租户或平台管理员可为某租户单独开关）
DROP TABLE IF EXISTS `fa_tenant_ai_module_switch`;
CREATE TABLE `fa_tenant_ai_module_switch` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `module` varchar(32) NOT NULL DEFAULT '' COMMENT 'voice_report/anomaly/qa/crm_follow',
  `enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1开 0关',
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_module` (`tenant_id`,`module`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户AI子功能开关覆盖';
