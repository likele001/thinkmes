-- 套餐功能配置表（用于按套餐限制功能模块访问）
DROP TABLE IF EXISTS `fa_tenant_package_feature`;
CREATE TABLE `fa_tenant_package_feature` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `package_id` int unsigned NOT NULL COMMENT '套餐ID',
  `feature_code` varchar(50) NOT NULL COMMENT '功能代码',
  `feature_name` varchar(50) NOT NULL COMMENT '功能名称',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_package_feature` (`package_id`,`feature_code`),
  KEY `idx_package` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套餐功能配置表';

-- 默认功能模块定义（可根据实际业务调整）
-- 这些是功能代码，用于在代码中检查权限
-- 示例功能代码：
-- 'order' - 订单管理
-- 'product' - 产品管理
-- 'inventory' - 库存管理
-- 'report' - 报表统计
-- 'export' - 数据导出
-- 'api' - API接口访问
-- 'custom_field' - 自定义字段
-- 'workflow' - 工作流
-- 等等...
