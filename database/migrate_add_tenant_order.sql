-- 租户订单表（用于管理租户的购买、续费、升级订单）
DROP TABLE IF EXISTS `fa_tenant_order`;
CREATE TABLE `fa_tenant_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `tenant_id` int unsigned NOT NULL COMMENT '租户ID',
  `order_no` varchar(32) NOT NULL COMMENT '订单号',
  `package_id` int unsigned NOT NULL COMMENT '套餐ID',
  `type` tinyint NOT NULL COMMENT '类型：1购买 2续费 3升级',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '金额',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0待支付 1已支付 2已取消 3已退款',
  `pay_method` varchar(20) DEFAULT '' COMMENT '支付方式：alipay/wechat/bank',
  `pay_time` int DEFAULT NULL COMMENT '支付时间',
  `expire_days` int DEFAULT NULL COMMENT '购买/续费天数',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_order_no` (`order_no`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_status` (`status`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户订单表';
