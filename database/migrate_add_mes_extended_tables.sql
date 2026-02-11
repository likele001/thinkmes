-- MES 制造执行系统扩展表结构
-- 补充生产计划、分工二维码、工资、追溯等功能表
-- 所有表都包含 tenant_id 字段以实现租户隔离

-- 生产计划表
DROP TABLE IF EXISTS `fa_mes_production_plan`;
CREATE TABLE `fa_mes_production_plan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '计划ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `plan_code` varchar(50) NOT NULL DEFAULT '' COMMENT '计划编码',
  `plan_name` varchar(100) NOT NULL DEFAULT '' COMMENT '计划名称',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `model_id` int unsigned NOT NULL DEFAULT 0 COMMENT '型号ID',
  `total_quantity` int NOT NULL DEFAULT 0 COMMENT '总生产数量',
  `completed_quantity` int NOT NULL DEFAULT 0 COMMENT '已完成数量',
  `planned_start_time` int DEFAULT NULL COMMENT '计划开始时间',
  `planned_end_time` int DEFAULT NULL COMMENT '计划结束时间',
  `actual_start_time` int DEFAULT NULL COMMENT '实际开始时间',
  `actual_end_time` int DEFAULT NULL COMMENT '实际结束时间',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待开始 1进行中 2已完成 3已暂停',
  `progress` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '完成进度(%)',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_plan_code` (`plan_code`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_model` (`model_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='生产计划表';

-- 分工二维码表
DROP TABLE IF EXISTS `fa_mes_allocation_qrcode`;
CREATE TABLE `fa_mes_allocation_qrcode` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `allocation_id` int unsigned NOT NULL DEFAULT 0 COMMENT '分工ID',
  `qrcode_content` text COMMENT '二维码内容',
  `qrcode_image` mediumtext COMMENT '二维码图片(base64)',
  `qrcode_url` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码URL',
  `scan_count` int NOT NULL DEFAULT 0 COMMENT '扫码次数',
  `last_scan_time` int DEFAULT NULL COMMENT '最后扫码时间',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1有效 0失效',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_allocation` (`allocation_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分工二维码表';

-- 工资记录表（计件工资）
DROP TABLE IF EXISTS `fa_mes_wage`;
CREATE TABLE `fa_mes_wage` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '员工ID',
  `report_id` int unsigned NOT NULL DEFAULT 0 COMMENT '报工ID',
  `allocation_id` int unsigned NOT NULL DEFAULT 0 COMMENT '分工ID',
  `work_type` varchar(20) NOT NULL DEFAULT 'piece' COMMENT '工作类型：piece计件 time计时',
  `quantity` int NOT NULL DEFAULT 0 COMMENT '数量（计件）',
  `work_hours` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '工时（计时）',
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '单价',
  `total_wage` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '总工资',
  `work_date` date DEFAULT NULL COMMENT '工作日期',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_report` (`report_id`),
  KEY `idx_work_date` (`work_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工资记录表';

-- 工资统计表
DROP TABLE IF EXISTS `fa_mes_wage_statistics`;
CREATE TABLE `fa_mes_wage_statistics` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '员工ID',
  `stat_date` date NOT NULL COMMENT '统计日期',
  `piece_wage` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '计件工资',
  `time_wage` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '计时工资',
  `total_wage` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '总工资',
  `piece_count` int NOT NULL DEFAULT 0 COMMENT '计件数量',
  `time_hours` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '计时工时',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_date` (`user_id`, `stat_date`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_stat_date` (`stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工资统计表';

-- 溯源码表
DROP TABLE IF EXISTS `fa_mes_trace_code`;
CREATE TABLE `fa_mes_trace_code` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `trace_code` varchar(100) NOT NULL DEFAULT '' COMMENT '追溯码',
  `report_id` int unsigned NOT NULL DEFAULT 0 COMMENT '报工ID',
  `allocation_id` int unsigned NOT NULL DEFAULT 0 COMMENT '分工ID',
  `order_id` int unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `model_id` int unsigned NOT NULL DEFAULT 0 COMMENT '型号ID',
  `process_id` int unsigned NOT NULL DEFAULT 0 COMMENT '工序ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '员工ID',
  `item_no` varchar(100) NOT NULL DEFAULT '' COMMENT '产品编号',
  `qrcode_image` mediumtext COMMENT '二维码图片(base64)',
  `qrcode_url` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码URL',
  `scan_count` int NOT NULL DEFAULT 0 COMMENT '扫码次数',
  `last_scan_time` int DEFAULT NULL COMMENT '最后扫码时间',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1有效 0失效',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_trace_code` (`trace_code`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_report` (`report_id`),
  KEY `idx_allocation` (`allocation_id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_item_no` (`item_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='溯源码表';

-- 补充分工分配表字段（检查字段是否存在，不存在则添加）
-- 注意：需要手动检查字段是否存在，这里提供ALTER语句
-- ALTER TABLE `fa_mes_allocation` 
--   ADD COLUMN `allocation_code` varchar(50) NOT NULL DEFAULT '' COMMENT '分配编码' AFTER `id`,
--   ADD COLUMN `qr_content` text COMMENT '二维码内容' AFTER `status`,
--   ADD COLUMN `qr_image` mediumtext COMMENT '二维码图片(base64)' AFTER `qr_content`,
--   ADD COLUMN `planned_start_time` int DEFAULT NULL COMMENT '计划开始时间' AFTER `status`,
--   ADD COLUMN `planned_end_time` int DEFAULT NULL COMMENT '计划结束时间' AFTER `planned_start_time`,
--   ADD COLUMN `actual_start_time` int DEFAULT NULL COMMENT '实际开始时间' AFTER `planned_end_time`,
--   ADD COLUMN `actual_end_time` int DEFAULT NULL COMMENT '实际结束时间' AFTER `actual_start_time`,
--   ADD COLUMN `item_prefix` varchar(100) NOT NULL DEFAULT '' COMMENT '产品编号前缀' AFTER `quantity`,
--   ADD COLUMN `remark` text COMMENT '备注' AFTER `status`,
--   ADD UNIQUE KEY `idx_allocation_code` (`allocation_code`);
