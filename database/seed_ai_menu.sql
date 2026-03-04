-- 工厂 AI 模块菜单
-- 表前缀由应用中心执行时替换

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('ai', '工厂AI', 1, 1, 1, 0, 'fa fa-robot', 95, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`);

SET @ai_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'ai' LIMIT 1), 0);

-- AI 配置
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('ai/config', 'AI配置', 1, 1, 1, @ai_pid, 'fa fa-cog', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `type` = VALUES(`type`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`), `pid` = @ai_pid, `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `update_time` = UNIX_TIMESTAMP();

SET @config_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'ai/config' LIMIT 1), 0);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('ai/config/index', '配置列表', 2, 0, 1, @config_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('ai/config/add', '添加配置', 2, 0, 1, @config_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('ai/config/edit', '编辑配置', 2, 0, 1, @config_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('ai/config/del', '删除配置', 2, 0, 1, @config_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @config_pid;

-- 语音报工
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('ai/voice_report', '语音报工', 1, 1, 1, @ai_pid, 'fa fa-microphone', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `type` = VALUES(`type`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`), `pid` = @ai_pid, `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `update_time` = UNIX_TIMESTAMP();

SET @voice_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'ai/voice_report' LIMIT 1), 0);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('ai/voice_report/index', '语音报工', 2, 0, 1, @voice_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @voice_pid;

-- 异常检测
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('ai/anomaly', '报工异常检测', 1, 1, 1, @ai_pid, 'fa fa-exclamation-triangle', 3, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `type` = VALUES(`type`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`), `pid` = @ai_pid, `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `update_time` = UNIX_TIMESTAMP();

SET @anomaly_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'ai/anomaly' LIMIT 1), 0);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('ai/anomaly/index', '异常列表', 2, 0, 1, @anomaly_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @anomaly_pid;

-- AI 问答
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('ai/qa', 'AI老板问答', 1, 1, 1, @ai_pid, 'fa fa-comments', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `type` = VALUES(`type`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`), `pid` = @ai_pid, `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `update_time` = UNIX_TIMESTAMP();

SET @qa_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'ai/qa' LIMIT 1), 0);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('ai/qa/index', '智能问答', 2, 0, 1, @qa_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @qa_pid;

-- AI 日报
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('ai/daily_report', 'AI生产日报', 1, 1, 1, @ai_pid, 'fa fa-file-alt', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `type` = VALUES(`type`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`), `pid` = @ai_pid, `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `update_time` = UNIX_TIMESTAMP();

SET @daily_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'ai/daily_report' LIMIT 1), 0);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('ai/daily_report/index', '日报周报', 2, 0, 1, @daily_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @daily_pid;

-- AI 跟单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('ai/crm_follow', 'AI智能跟单', 1, 1, 1, @ai_pid, 'fa fa-hand-holding-heart', 6, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `type` = VALUES(`type`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`), `pid` = @ai_pid, `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `update_time` = UNIX_TIMESTAMP();

SET @follow_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'ai/crm_follow' LIMIT 1), 0);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('ai/crm_follow/index', '跟单建议', 2, 0, 1, @follow_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = @follow_pid;

-- AI 套餐管理
UPDATE `fa_auth_rule` SET `title` = 'AI套餐管理', `type` = 1, `ismenu` = 1, `status` = 1, `pid` = @ai_pid, `icon` = 'fa fa-cubes', `sort` = 7, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'ai/package';
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
SELECT 'ai/package', 'AI套餐管理', 1, 1, 1, @ai_pid, 'fa fa-cubes', 7, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'ai/package');

SET @package_pid = COALESCE((SELECT id FROM fa_auth_rule WHERE name = 'ai/package' LIMIT 1), 0);
-- 套餐列表作为可显示的子菜单
UPDATE `fa_auth_rule` SET `title` = '套餐列表', `type` = 1, `ismenu` = 1, `status` = 1, `pid` = @package_pid, `icon` = '', `sort` = 0, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'ai/package/index';
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
SELECT 'ai/package/index', '套餐列表', 1, 1, 1, @package_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'ai/package/index');

UPDATE `fa_auth_rule` SET `title` = '创建套餐', `type` = 2, `ismenu` = 0, `status` = 1, `pid` = @package_pid, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'ai/package/createPackage';
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
SELECT 'ai/package/createPackage', '创建套餐', 2, 0, 1, @package_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'ai/package/createPackage');

UPDATE `fa_auth_rule` SET `title` = '租户购买', `type` = 2, `ismenu` = 0, `status` = 1, `pid` = @package_pid, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'ai/package/purchaseForTenant';
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
SELECT 'ai/package/purchaseForTenant', '租户购买', 2, 0, 1, @package_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'ai/package/purchaseForTenant');

UPDATE `fa_auth_rule` SET `title` = '全局开关', `type` = 2, `ismenu` = 0, `status` = 1, `pid` = @package_pid, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'ai/package/globalSwitch';
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
SELECT 'ai/package/globalSwitch', '全局开关', 2, 0, 1, @package_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'ai/package/globalSwitch');

UPDATE `fa_auth_rule` SET `title` = '更新开关', `type` = 2, `ismenu` = 0, `status` = 1, `pid` = @package_pid, `update_time` = UNIX_TIMESTAMP() WHERE `name` = 'ai/package/updateGlobal';
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
SELECT 'ai/package/updateGlobal', '更新开关', 2, 0, 1, @package_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'ai/package/updateGlobal');
