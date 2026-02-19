-- cloudstorage 插件卸载 SQL：删除独立配置表
DROP TABLE IF EXISTS `fa_addon_cloudstorage_config`;
DELETE FROM `fa_auth_rule` WHERE `name`='cloudstorage/index';
