-- 设备管理模块（多租户）DB_PREFIX=fa_
CREATE TABLE IF NOT EXISTS `fa_equipment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `code` varchar(64) NOT NULL,
  `name` varchar(128) NOT NULL,
  `category` varchar(64) DEFAULT NULL,
  `model` varchar(64) DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT 1,
  `install_date` date DEFAULT NULL,
  `remark` varchar(500) DEFAULT NULL,
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='设备档案';

CREATE TABLE IF NOT EXISTS `fa_equipment_maintenance_plan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `equipment_id` int unsigned NOT NULL,
  `plan_type` varchar(32) NOT NULL DEFAULT 'monthly',
  `cycle_days` int NOT NULL DEFAULT 30,
  `last_date` date DEFAULT NULL,
  `next_date` date DEFAULT NULL,
  `content` varchar(500) DEFAULT NULL,
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `equipment_id` (`equipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='设备保养计划';

CREATE TABLE IF NOT EXISTS `fa_equipment_check` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `equipment_id` int unsigned NOT NULL,
  `check_date` date NOT NULL,
  `checker` varchar(64) DEFAULT NULL,
  `result` tinyint NOT NULL DEFAULT 1,
  `content` text,
  `create_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `equipment_id` (`equipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='设备点检记录';

CREATE TABLE IF NOT EXISTS `fa_equipment_repair` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `equipment_id` int unsigned NOT NULL,
  `fault_desc` varchar(500) DEFAULT NULL,
  `repair_date` date DEFAULT NULL,
  `repair_result` varchar(500) DEFAULT NULL,
  `cost` decimal(12,2) DEFAULT NULL,
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `equipment_id` (`equipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='设备维修记录';

CREATE TABLE IF NOT EXISTS `fa_equipment_runtime` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `equipment_id` int unsigned NOT NULL,
  `run_date` date NOT NULL,
  `plan_hours` decimal(8,2) DEFAULT 8.00,
  `run_hours` decimal(8,2) DEFAULT 0,
  `down_hours` decimal(8,2) DEFAULT 0,
  `create_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_equipment_date` (`equipment_id`,`run_date`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='设备运行记录';
