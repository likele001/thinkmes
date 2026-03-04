-- AI 套餐与购买记录表
-- 1. AI 套餐定义表
DROP TABLE IF EXISTS `fa_ai_package`;
CREATE TABLE `fa_ai_package` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '套餐ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '套餐名称',
  `price_month` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '月付价格（元）',
  `price_quarter` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '季度价格（元）',
  `price_year` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '年付价格（元）',
  `enabled` tinyint NOT NULL DEFAULT 1 COMMENT '是否启用：1启用 0禁用',
  `description` text COMMENT '套餐描述/包含功能JSON',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI 套餐表';

-- 2. 租户 AI 购买/开通记录
DROP TABLE IF EXISTS `fa_tenant_ai_purchase`;
CREATE TABLE `fa_tenant_ai_purchase` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '购买ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `package_id` int unsigned NOT NULL DEFAULT 0 COMMENT '购买的套餐ID',
  `period` varchar(20) NOT NULL DEFAULT 'month' COMMENT '周期：month/quarter/year',
  `start_time` int NOT NULL DEFAULT 0 COMMENT '生效时间',
  `end_time` int NOT NULL DEFAULT 0 COMMENT '到期时间',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1生效 0失效 2撤销',
  `order_no` varchar(100) NOT NULL DEFAULT '' COMMENT '订单号/交易号',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '实际支付金额',
  `payment_method` varchar(50) NOT NULL DEFAULT '' COMMENT '支付方式',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '购买时间',
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_package` (`package_id`),
  KEY `idx_order_no` (`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户AI购买记录';

-- 3. 全局 AI 功能开关（控制全局是否允许 AI 功能）
DROP TABLE IF EXISTS `fa_ai_global_switch`;
CREATE TABLE `fa_ai_global_switch` (
  `id` tinyint unsigned NOT NULL DEFAULT 1 COMMENT '单条配置ID，默认1',
  `enabled` tinyint NOT NULL DEFAULT 0 COMMENT '全局开关：1允许使用 AI 功能 0 禁用（默认关闭）',
  `require_purchase` tinyint NOT NULL DEFAULT 1 COMMENT '是否必须购买套餐才可使用：1是 0否',
  `notice` varchar(255) NOT NULL DEFAULT '' COMMENT '公告/提示文本',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI 全局功能开关';

-- 初始化默认记录（插入时使用 UNX_TIMESTAMP）
INSERT INTO `fa_ai_global_switch` (`id`,`enabled`,`require_purchase`,`notice`,`update_time`) VALUES (1,0,1,'',UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `enabled`=VALUES(`enabled`),`require_purchase`=VALUES(`require_purchase`),`update_time`=VALUES(`update_time`);
