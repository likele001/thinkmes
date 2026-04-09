-- 租户小程序配置表（基础功能）
CREATE TABLE IF NOT EXISTS `fa_tenant_miniapp` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL COMMENT '租户ID',
  `type` varchar(20) NOT NULL DEFAULT 'wechat' COMMENT '小程序类型：wechat 等',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '小程序名称',
  `app_id` varchar(64) NOT NULL DEFAULT '' COMMENT 'AppID',
  `app_secret` varchar(100) NOT NULL DEFAULT '' COMMENT 'AppSecret',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：1启用 0禁用',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_tenant_type` (`tenant_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户小程序配置表';

