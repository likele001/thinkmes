-- MES - 分工分配表补充计划/实际起止时间字段（旧库增量执行）
-- 若报 Duplicate column name 说明已存在，可忽略

ALTER TABLE `fa_mes_allocation`
  ADD COLUMN `planned_start_time` int DEFAULT NULL COMMENT '计划开始时间' AFTER `status`,
  ADD COLUMN `planned_end_time` int DEFAULT NULL COMMENT '计划结束时间' AFTER `planned_start_time`,
  ADD COLUMN `actual_start_time` int DEFAULT NULL COMMENT '实际开始时间' AFTER `planned_end_time`,
  ADD COLUMN `actual_end_time` int DEFAULT NULL COMMENT '实际结束时间' AFTER `actual_start_time`;

