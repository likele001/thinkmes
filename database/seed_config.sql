-- 系统配置种子（多语言/缓存/安全等），在 init.sql 之后执行
-- 若 fa_config 已有数据可跳过或按需修改
-- 注意：需要先执行 migrate_add_config_title.sql 添加 title 字段

INSERT INTO `fa_config` (`name`, `title`, `value`, `group`, `sort`) VALUES
('site_name', '站点名称', 'ThinkMes', 'base', 0),
('site_logo', '站点Logo', '', 'base', 1),
('login_captcha', '登录验证码', '0', 'safe', 0),
('login_fail_limit', '登录失败次数限制', '5', 'safe', 1),
('default_lang', '默认语言', 'zh-cn', 'lang', 0),
('cache_driver', '缓存驱动', 'file', 'cache', 0),
('upload_max_size', '上传文件大小限制（字节）', '10485760', 'upload', 0),
('upload_storage', '上传存储方式', 'local', 'upload', 1),
('front_captcha_mode', '前端验证码方式 (Captcha Mode)', 'image', 'safe', 2)
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `title` = VALUES(`title`);
