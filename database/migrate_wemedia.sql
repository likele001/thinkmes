-- 自媒体工作流（用户中心独立应用）数据表
-- 执行：mysql -u用户 -p 数据库名 < database/migrate_wemedia.sql

-- 自媒体配置（后台管理：tenant_id=0 平台级，>0 租户级）
CREATE TABLE IF NOT EXISTS `fa_wemedia_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '0=平台',
  `config_key` varchar(64) NOT NULL DEFAULT '',
  `config_value` text,
  `remark` varchar(200) NOT NULL DEFAULT '',
  `create_time` int unsigned NOT NULL DEFAULT 0,
  `update_time` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_key` (`tenant_id`,`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='自媒体配置';

-- 选题策划
CREATE TABLE IF NOT EXISTS `fa_wemedia_topic` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '创建人ID',
  `platform` varchar(30) NOT NULL DEFAULT '' COMMENT '平台',
  `field_keyword` varchar(100) NOT NULL DEFAULT '' COMMENT '领域关键词',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '选题标题',
  `highlight` text COMMENT '核心亮点',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '0待创作 1已完成',
  `is_shared` tinyint NOT NULL DEFAULT 0 COMMENT '0私有 1共享',
  `create_time` int unsigned NOT NULL DEFAULT 0,
  `update_time` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='自媒体选题';

-- 文案创作
CREATE TABLE IF NOT EXISTS `fa_wemedia_copy` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `topic_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联选题ID',
  `platform` varchar(30) NOT NULL DEFAULT '' COMMENT '平台',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `content` longtext COMMENT '正文',
  `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '标签逗号分隔',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '0草稿 1已发布',
  `is_shared` tinyint NOT NULL DEFAULT 0,
  `create_time` int unsigned NOT NULL DEFAULT 0,
  `update_time` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_topic_id` (`topic_id`),
  KEY `idx_platform` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='自媒体文案';

-- 素材管理
CREATE TABLE IF NOT EXISTS `fa_wemedia_material` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `type` varchar(20) NOT NULL DEFAULT 'image' COMMENT 'image/video/audio/text',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '文件名/标题',
  `path` varchar(500) NOT NULL DEFAULT '' COMMENT '存储路径',
  `size` int unsigned NOT NULL DEFAULT 0 COMMENT '字节',
  `mime` varchar(100) NOT NULL DEFAULT '',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `is_shared` tinyint NOT NULL DEFAULT 0,
  `create_time` int unsigned NOT NULL DEFAULT 0,
  `update_time` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='自媒体素材';

-- 短视频脚本
CREATE TABLE IF NOT EXISTS `fa_wemedia_video_script` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `topic_id` int unsigned NOT NULL DEFAULT 0,
  `platform` varchar(30) NOT NULL DEFAULT '',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '视频标题',
  `duration` int unsigned NOT NULL DEFAULT 0 COMMENT '预计时长秒',
  `script_content` longtext COMMENT '分镜/脚本内容',
  `cover_path` varchar(500) NOT NULL DEFAULT '' COMMENT '封面图路径',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '0草稿 1已完成',
  `is_shared` tinyint NOT NULL DEFAULT 0,
  `create_time` int unsigned NOT NULL DEFAULT 0,
  `update_time` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_topic_id` (`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='短视频脚本';

-- 发布排期
CREATE TABLE IF NOT EXISTS `fa_wemedia_schedule` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `relate_type` varchar(20) NOT NULL DEFAULT 'copy' COMMENT 'copy/video',
  `relate_id` int unsigned NOT NULL DEFAULT 0 COMMENT '文案或视频ID',
  `platform` varchar(30) NOT NULL DEFAULT '',
  `plan_time` int unsigned NOT NULL DEFAULT 0 COMMENT '计划发布时间',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '0待发布 1已发布 2已下架',
  `create_time` int unsigned NOT NULL DEFAULT 0,
  `update_time` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_plan_time` (`plan_time`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='发布排期';

-- 数据复盘（单条数据录入）
CREATE TABLE IF NOT EXISTS `fa_wemedia_report` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `platform` varchar(30) NOT NULL DEFAULT '',
  `report_date` date NOT NULL COMMENT '数据日期',
  `metric_type` varchar(30) NOT NULL DEFAULT 'view' COMMENT 'view/like/comment/share/fan',
  `metric_value` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '数值',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `create_time` int unsigned NOT NULL DEFAULT 0,
  `update_time` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_report_date` (`report_date`),
  KEY `idx_platform` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据复盘';

-- 合规检测记录
CREATE TABLE IF NOT EXISTS `fa_wemedia_compliance_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0,
  `user_id` int unsigned NOT NULL DEFAULT 0,
  `content_type` varchar(20) NOT NULL DEFAULT 'text' COMMENT 'text/image/video',
  `content_text` text COMMENT '待检测文案',
  `file_path` varchar(500) NOT NULL DEFAULT '' COMMENT '图片/视频路径',
  `result` tinyint NOT NULL DEFAULT 0 COMMENT '0合规 1违规',
  `suggestion` text COMMENT '修改建议',
  `create_time` int unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='合规检测记录';
