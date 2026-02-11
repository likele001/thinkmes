-- 为 fa_config 表添加 title 字段（配置项中文标题）
-- 如果字段已存在会报错，可忽略或手动删除后重试

ALTER TABLE `fa_config` ADD COLUMN `title` varchar(100) NOT NULL DEFAULT '' COMMENT '配置项标题（中文）' AFTER `name`;

-- 更新现有配置项的中文标题
UPDATE `fa_config` SET `title` = '站点名称' WHERE `name` = 'site_name';
UPDATE `fa_config` SET `title` = '站点Logo' WHERE `name` = 'site_logo';
UPDATE `fa_config` SET `title` = '登录验证码' WHERE `name` = 'login_captcha';
UPDATE `fa_config` SET `title` = '登录失败次数限制' WHERE `name` = 'login_fail_limit';
UPDATE `fa_config` SET `title` = '默认语言' WHERE `name` = 'default_lang';
UPDATE `fa_config` SET `title` = '缓存驱动' WHERE `name` = 'cache_driver';
UPDATE `fa_config` SET `title` = '上传文件大小限制（字节）' WHERE `name` = 'upload_max_size';
UPDATE `fa_config` SET `title` = '上传存储方式' WHERE `name` = 'upload_storage';
