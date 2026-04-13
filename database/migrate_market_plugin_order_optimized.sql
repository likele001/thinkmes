-- 插件市场：购买订单表（前端应用商店使用）
-- 优化版本：添加外键约束和性能索引

CREATE TABLE IF NOT EXISTS `fa_market_plugin_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_no` varchar(40) NOT NULL DEFAULT '' COMMENT '订单号',
  `plugin_id` int unsigned NOT NULL DEFAULT 0 COMMENT '市场插件ID',
  `plugin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '插件标识',
  `plugin_title` varchar(100) NOT NULL DEFAULT '' COMMENT '插件标题',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '订单金额',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待支付 1已支付',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'C端用户ID',
  `pay_time` int unsigned NOT NULL DEFAULT 0 COMMENT '支付时间',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_plugin_id` (`plugin_id`),
  KEY `idx_status` (`status`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_tenant_plugin` (`tenant_id`, `plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='插件市场购买订单';

-- 添加外键约束确保数据完整性
ALTER TABLE `fa_market_plugin_order` 
  ADD CONSTRAINT `fk_order_plugin` FOREIGN KEY (`plugin_id`) REFERENCES `fa_market_plugin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 添加检查约束确保金额非负
ALTER TABLE `fa_market_plugin_order`
  ADD CONSTRAINT `chk_amount_positive` CHECK (`amount` >= 0);

-- 添加检查约束确保状态有效
ALTER TABLE `fa_market_plugin_order`
  ADD CONSTRAINT `chk_status_valid` CHECK (`status` IN (0, 1));
