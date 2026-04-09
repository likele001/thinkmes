-- AI 提示词工坊 - 菜单权限种子数据
-- auth_rule: name格式为 prompt/controller/action

-- 1. 插入/更新父菜单
INSERT INTO `fa_auth_rule` (`name`,`title`,`icon`,`status`,`ismenu`,`pid`,`sort`,`create_time`,`update_time`) VALUES
('prompt', 'AI提示词工坊', 'fas fa-magic', 1, 1, 0, 90, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `status`=VALUES(`status`);

-- 2. 用变量缓存父菜单 id，避免 1093 同表子查询错误
SET @_prompt_pid = (SELECT id FROM (SELECT id FROM `fa_auth_rule` WHERE `name`='prompt' LIMIT 1) AS _t);

-- 3. 插入/更新子菜单
INSERT INTO `fa_auth_rule` (`name`,`title`,`icon`,`status`,`ismenu`,`pid`,`sort`,`create_time`,`update_time`) VALUES
('prompt/category',    '分类管理',   'fas fa-list',      1, 1, @_prompt_pid, 91, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('prompt/template',    '模板管理',   'fas fa-file-alt',  1, 1, @_prompt_pid, 92, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('prompt/generation',  '生成记录',   'fas fa-history',   1, 1, @_prompt_pid, 93, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('prompt/quota',       '额度管理',   'fas fa-coins',     1, 1, @_prompt_pid, 94, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('prompt/ai_config',   'AI服务配置', 'fas fa-robot',     1, 1, @_prompt_pid, 95, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('prompt/config',      '应用设置',   'fas fa-cog',       1, 1, @_prompt_pid, 96, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`), `status`=VALUES(`status`), `pid`=@_prompt_pid;
