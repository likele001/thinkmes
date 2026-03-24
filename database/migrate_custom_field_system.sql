-- 自定义字段系统数据表
-- 执行：mysql -u用户 -p 数据库名 < database/migrate_custom_field_system.sql

-- 自定义字段分组表（用于管理不同模块的字段集）
CREATE TABLE IF NOT EXISTS `fa_custom_field_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分组名称',
  `table_name` varchar(100) NOT NULL DEFAULT '' COMMENT '关联表名（如：mes_order）',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '分组描述',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_table_name` (`table_name`),
  KEY `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='自定义字段分组';

-- 自定义字段表
CREATE TABLE IF NOT EXISTS `fa_custom_field` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group_id` int unsigned NOT NULL DEFAULT 0 COMMENT '分组ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '字段名（英文）',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '字段标题',
  `type` varchar(30) NOT NULL DEFAULT 'text' COMMENT '字段类型：text,number,select,radio,checkbox,date,datetime,textarea,editor,image,file,switch',
  `options` text COMMENT '选项配置（JSON格式，用于select/radio/checkbox）',
  `default_value` varchar(500) NOT NULL DEFAULT '' COMMENT '默认值',
  `placeholder` varchar(200) NOT NULL DEFAULT '' COMMENT '占位符',
  `required` tinyint NOT NULL DEFAULT 0 COMMENT '是否必填：0否 1是',
  `validation_rules` varchar(500) NOT NULL DEFAULT '' COMMENT '验证规则（多个用逗号分隔）：required,email,url,phone,number,integer,min,max,length,regex',
  `regex_pattern` varchar(255) NOT NULL DEFAULT '' COMMENT '正则表达式（当validation_rules包含regex时使用）',
  `width` int NOT NULL DEFAULT 12 COMMENT '表单宽度（Bootstrap栅格：1-12）',
  `tips` varchar(500) NOT NULL DEFAULT '' COMMENT '提示信息',
  `is_list` tinyint NOT NULL DEFAULT 1 COMMENT '是否在列表中显示：0否 1是',
  `is_search` tinyint NOT NULL DEFAULT 0 COMMENT '是否可搜索：0否 1是',
  `is_sort` tinyint NOT NULL DEFAULT 0 COMMENT '是否可排序：0否 1是',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_group_name` (`group_id`, `name`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='自定义字段';

-- 自定义字段值表（存储自定义字段的实际值）
CREATE TABLE IF NOT EXISTS `fa_custom_field_value` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `field_id` int unsigned NOT NULL DEFAULT 0 COMMENT '字段ID',
  `record_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联记录ID',
  `table_name` varchar(100) NOT NULL DEFAULT '' COMMENT '关联表名',
  `value` text COMMENT '字段值',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `create_time` int unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_field_record` (`field_id`, `record_id`),
  KEY `idx_record_table` (`record_id`, `table_name`),
  KEY `idx_tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='自定义字段值';

-- 初始化示例数据
INSERT INTO `fa_custom_field_group` (`name`, `table_name`, `description`, `tenant_id`, `status`, `sort`, `create_time`, `update_time`) VALUES
('订单扩展信息', 'mes_order', '订单模块的自定义字段', 0, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('客户扩展信息', 'crm_customer', '客户模块的自定义字段', 0, 1, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT INTO `fa_custom_field` (`group_id`, `name`, `title`, `type`, `options`, `default_value`, `placeholder`, `required`, `validation_rules`, `width`, `tips`, `is_list`, `is_search`, `is_sort`, `sort`, `tenant_id`, `status`, `create_time`, `update_time`) VALUES
(1, 'priority', '优先级', 'select', '[{"label":"普通","value":"normal"},{"label":"紧急","value":"urgent"},{"label":"非常紧急","value":"critical"}]', 'normal', '请选择优先级', 1, '', 6, '订单的优先级标记', 1, 1, 1, 1, 0, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(1, 'remark_custom', '自定义备注', 'textarea', '', '', '请输入备注信息', 0, 'max:500', 12, '额外的订单备注', 1, 0, 0, 2, 0, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
