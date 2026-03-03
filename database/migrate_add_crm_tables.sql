-- CRM 客户关系管理表结构
-- 所有表包含 tenant_id，字符集 utf8mb4，引擎 InnoDB
-- 安装时表前缀由应用中心替换 fa_ 为实际前缀

-- 客户表
DROP TABLE IF EXISTS `fa_crm_customer`;
CREATE TABLE `fa_crm_customer` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '客户ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '客户名称',
  `short_name` varchar(50) NOT NULL DEFAULT '' COMMENT '简称',
  `level` tinyint NOT NULL DEFAULT 0 COMMENT '客户级别：0普通 1重要 2战略',
  `source` varchar(50) NOT NULL DEFAULT '' COMMENT '客户来源',
  `industry` varchar(50) NOT NULL DEFAULT '' COMMENT '所属行业',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1正常 0停用',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_status` (`status`),
  KEY `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CRM客户表';

-- 联系人表
DROP TABLE IF EXISTS `fa_crm_contact`;
CREATE TABLE `fa_crm_contact` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '联系人ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `customer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '客户ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '姓名',
  `role` varchar(50) NOT NULL DEFAULT '' COMMENT '职位/角色',
  `phone` varchar(30) NOT NULL DEFAULT '' COMMENT '电话',
  `email` varchar(80) NOT NULL DEFAULT '' COMMENT '邮箱',
  `is_main` tinyint NOT NULL DEFAULT 0 COMMENT '是否主联系人：1是 0否',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CRM联系人表';

-- 商机表
DROP TABLE IF EXISTS `fa_crm_opportunity`;
CREATE TABLE `fa_crm_opportunity` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '商机ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `customer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '客户ID',
  `contact_id` int unsigned NOT NULL DEFAULT 0 COMMENT '联系人ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '商机名称',
  `stage` varchar(30) NOT NULL DEFAULT 'lead' COMMENT '阶段：lead需求确认 quote报价 negotiate谈判 won赢单 lost输单',
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT '金额',
  `owner_id` int unsigned NOT NULL DEFAULT 0 COMMENT '负责人(admin_id)',
  `expected_date` int DEFAULT NULL COMMENT '预期成单日期',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_stage` (`stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CRM商机表';

-- 合同表
DROP TABLE IF EXISTS `fa_crm_contract`;
CREATE TABLE `fa_crm_contract` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '合同ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `customer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '客户ID',
  `opportunity_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联商机ID',
  `contract_no` varchar(50) NOT NULL DEFAULT '' COMMENT '合同编号',
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT '合同金额',
  `sign_date` int DEFAULT NULL COMMENT '签订日期',
  `status` varchar(20) NOT NULL DEFAULT 'draft' COMMENT '状态：draft草稿 performing履行中 completed已完成 terminated终止',
  `remark` text COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_contract_no` (`contract_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CRM合同表';

-- 跟进记录表
DROP TABLE IF EXISTS `fa_crm_follow`;
CREATE TABLE `fa_crm_follow` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `customer_id` int unsigned NOT NULL DEFAULT 0 COMMENT '客户ID',
  `opportunity_id` int unsigned NOT NULL DEFAULT 0 COMMENT '商机ID',
  `type` varchar(20) NOT NULL DEFAULT 'visit' COMMENT '类型：visit拜访 phone电话 email邮件',
  `content` text COMMENT '跟进内容',
  `next_follow_time` int DEFAULT NULL COMMENT '下次跟进时间',
  `admin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '跟进人',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_opportunity` (`opportunity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CRM跟进记录表';

-- 回款表
DROP TABLE IF EXISTS `fa_crm_payment`;
CREATE TABLE `fa_crm_payment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `contract_id` int unsigned NOT NULL DEFAULT 0 COMMENT '合同ID',
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT '回款金额',
  `pay_date` int NOT NULL DEFAULT 0 COMMENT '回款日期',
  `invoice_no` varchar(50) NOT NULL DEFAULT '' COMMENT '发票号',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_contract` (`contract_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CRM回款表';
