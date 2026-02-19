-- cloudstorage 插件安装 SQL：创建独立配置表
CREATE TABLE IF NOT EXISTS `fa_addon_cloudstorage_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` int unsigned NOT NULL DEFAULT 0 COMMENT '租户ID，0=平台级配置',
  `config` text COMMENT '配置JSON（driver及各云存储参数）',
  `create_time` int NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='cloudstorage 插件配置表';

INSERT INTO `fa_auth_rule` (`name`,`title`,`type`,`ismenu`,`status`,`pid`,`icon`,`sort`,`create_time`,`update_time`)
SELECT 'cloudstorage/index','云存储配置',1,1,1,
       IFNULL((SELECT `id` FROM `fa_auth_rule` WHERE `name`='admin/config/index' LIMIT 1),0),
       'fas fa-cloud-upload-alt',45,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name`='cloudstorage/index');

