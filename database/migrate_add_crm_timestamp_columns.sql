-- 为 CRM 表补充时间戳列，避免 ThinkPHP 自动时间戳写入报错
-- 表前缀与项目 database 配置一致（如 fa_）

-- 销售订单明细表：补充 create_time / update_time
ALTER TABLE `fa_crm_sales_order_item` ADD COLUMN `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间' AFTER `amount`;
ALTER TABLE `fa_crm_sales_order_item` ADD COLUMN `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间' AFTER `create_time`;

-- 跟进记录表：补充 update_time
ALTER TABLE `fa_crm_follow` ADD COLUMN `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间' AFTER `create_time`;

-- 回款表：补充 update_time
ALTER TABLE `fa_crm_payment` ADD COLUMN `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间' AFTER `create_time`;
