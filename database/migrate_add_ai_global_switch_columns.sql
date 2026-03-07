-- 为 fa_ai_global_switch 增加四个子功能开关列（仅执行一次；若报 Duplicate column 说明已存在可忽略）
-- 表前缀需与 .env 中 DB_PREFIX 一致，默认 fa_

ALTER TABLE `fa_ai_global_switch` ADD COLUMN `switch_voice_report` tinyint(1) NOT NULL DEFAULT 1 COMMENT '语音报工：1开 0关' AFTER `notice`;
ALTER TABLE `fa_ai_global_switch` ADD COLUMN `switch_anomaly` tinyint(1) NOT NULL DEFAULT 1 COMMENT '异常检测：1开 0关' AFTER `switch_voice_report`;
ALTER TABLE `fa_ai_global_switch` ADD COLUMN `switch_qa` tinyint(1) NOT NULL DEFAULT 1 COMMENT '智能问答：1开 0关' AFTER `switch_anomaly`;
ALTER TABLE `fa_ai_global_switch` ADD COLUMN `switch_crm_follow` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'CRM自动跟单：1开 0关' AFTER `switch_qa`;
