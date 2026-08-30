-- 人事考勤模块（多租户，与计件工资联动）
-- 执行前请确认 .env 中 DB_PREFIX=fa_

-- 部门
CREATE TABLE IF NOT EXISTS `fa_hr_department` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(64) NOT NULL,
  `pid` int unsigned NOT NULL DEFAULT 0,
  `manager_id` int unsigned NOT NULL DEFAULT 0 COMMENT '部门负责人管理员ID',
  `sort` int NOT NULL DEFAULT 0,
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='部门';

-- 岗位
CREATE TABLE IF NOT EXISTS `fa_hr_position` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `name` varchar(64) NOT NULL,
  `sort` int NOT NULL DEFAULT 0,
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='岗位';

-- 员工档案（可与报工/计件工资关联）
CREATE TABLE IF NOT EXISTS `fa_hr_employee` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `admin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '绑定后台管理员ID',
  `no` varchar(32) DEFAULT NULL COMMENT '工号',
  `name` varchar(64) NOT NULL,
  `department_id` int unsigned NOT NULL DEFAULT 0,
  `position_id` int unsigned NOT NULL DEFAULT 0,
  `mobile` varchar(20) DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '1在职 2离职',
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='员工档案';

-- 考勤打卡
CREATE TABLE IF NOT EXISTS `fa_hr_attendance` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `employee_id` int unsigned NOT NULL,
  `day` date NOT NULL,
  `clock_in` int DEFAULT NULL COMMENT '上班打卡时间戳',
  `clock_out` int DEFAULT NULL COMMENT '下班打卡时间戳',
  `source` varchar(32) DEFAULT 'pc',
  `create_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_emp_day` (`employee_id`,`day`),
  KEY `tenant_id` (`tenant_id`),
  KEY `day` (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='考勤打卡';

-- 请假
CREATE TABLE IF NOT EXISTS `fa_hr_leave` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `employee_id` int unsigned NOT NULL,
  `type` tinyint NOT NULL DEFAULT 1 COMMENT '1事假 2病假 3年假',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days` decimal(4,2) NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '0待审 1通过 2拒绝',
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='请假';

-- 加班
CREATE TABLE IF NOT EXISTS `fa_hr_overtime` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `employee_id` int unsigned NOT NULL,
  `work_date` date NOT NULL,
  `hours` decimal(4,2) NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT 0,
  `create_time` int DEFAULT NULL,
  `update_time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='加班';
