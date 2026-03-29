-- MES 智能排产（计件）相关表
-- 说明：使用 CREATE TABLE IF NOT EXISTS，避免重复执行删除数据

CREATE TABLE IF NOT EXISTS `fa_mes_user_process_capacity` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `process_id` int unsigned NOT NULL DEFAULT 0,
  `capacity_per_day` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 1,
  `create_time` int NOT NULL DEFAULT 0,
  `update_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_user_process` (`tenant_id`,`user_id`,`process_id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_process` (`tenant_id`,`process_id`),
  KEY `idx_user` (`tenant_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='员工-工序日产能(计件)';

CREATE TABLE IF NOT EXISTS `fa_mes_schedule_task` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `batch_id` varchar(40) NOT NULL DEFAULT '',
  `plan_id` int unsigned NOT NULL DEFAULT 0,
  `order_id` int unsigned NOT NULL DEFAULT 0,
  `model_id` int unsigned NOT NULL DEFAULT 0,
  `process_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `work_date` date DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '0待下发 1已下发 2已完成 3已撤销',
  `create_time` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_batch` (`tenant_id`,`batch_id`),
  KEY `idx_date` (`tenant_id`,`work_date`),
  KEY `idx_process_user_date` (`tenant_id`,`process_id`,`user_id`,`work_date`),
  KEY `idx_plan` (`tenant_id`,`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='排产任务(计件)';

