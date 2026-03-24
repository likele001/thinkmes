-- 扩展模块菜单与权限 - 自定义字段、工作流、插件市场
-- 表前缀 fa_

-- 自定义字段模块菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('custom_field', '自定义字段', 1, 1, 1, 0, 'fa fa-puzzle-piece', 85, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`);

SET @cf_pid = (SELECT id FROM fa_auth_rule WHERE name = 'custom_field' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('custom_field/index', '字段管理', 1, 1, 1, @cf_pid, 'fa fa-list', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('custom_field/groups', '字段组', 1, 1, 1, @cf_pid, 'fa fa-layer-group', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @cf_pid;

SET @cf_index_pid = (SELECT id FROM fa_auth_rule WHERE name = 'custom_field/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('custom_field/add', '添加', 2, 0, 1, @cf_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('custom_field/edit', '编辑', 2, 0, 1, @cf_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('custom_field/del', '删除', 2, 0, 1, @cf_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('custom_field/multi', '批量更新', 2, 0, 1, @cf_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('custom_field/render', '渲染字段', 2, 0, 1, @cf_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('custom_field/save_value', '保存字段值', 2, 0, 1, @cf_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @cf_index_pid;

SET @cf_groups_pid = (SELECT id FROM fa_auth_rule WHERE name = 'custom_field/groups' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('custom_field/addGroup', '添加分组', 2, 0, 1, @cf_groups_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('custom_field/editGroup', '编辑分组', 2, 0, 1, @cf_groups_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('custom_field/delGroup', '删除分组', 2, 0, 1, @cf_groups_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @cf_groups_pid;

-- 工作流模块菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('workflow', '工作流管理', 1, 1, 1, 0, 'fa fa-project-diagram', 86, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`);

SET @wf_pid = (SELECT id FROM fa_auth_rule WHERE name = 'workflow' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('workflow/index', '工作流定义', 1, 1, 1, @wf_pid, 'fa fa-sitemap', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('workflow/instances', '流程实例', 1, 1, 1, @wf_pid, 'fa fa-list-alt', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @wf_pid;

SET @wf_index_pid = (SELECT id FROM fa_auth_rule WHERE name = 'workflow/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('workflow/add', '创建工作流', 2, 0, 1, @wf_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('workflow/edit', '编辑工作流', 2, 0, 1, @wf_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('workflow/del', '删除工作流', 2, 0, 1, @wf_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('workflow/multi', '批量更新', 2, 0, 1, @wf_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('workflow/getStates', '获取状态', 2, 0, 1, @wf_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('workflow/getTransitions', '获取流转规则', 2, 0, 1, @wf_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @wf_index_pid;

SET @wf_instances_pid = (SELECT id FROM fa_auth_rule WHERE name = 'workflow/instances' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('workflow/instances/start', '启动流程', 2, 0, 1, @wf_instances_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('workflow/instances/approve', '审批通过', 2, 0, 1, @wf_instances_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('workflow/instances/reject', '驳回', 2, 0, 1, @wf_instances_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('workflow/instances/withdraw', '撤回', 2, 0, 1, @wf_instances_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('workflow/instances/detail', '查看详情', 2, 0, 1, @wf_instances_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @wf_instances_pid;

-- 插件市场模块菜单
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('market', '插件市场', 1, 1, 1, 0, 'fa fa-store', 87, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`);

SET @market_pid = (SELECT id FROM fa_auth_rule WHERE name = 'market' LIMIT 1);

INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('market/index', '插件市场', 1, 1, 1, @market_pid, 'fa fa-shopping-bag', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('market/my_plugins', '我的插件', 1, 1, 1, @market_pid, 'fa fa-box', 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @market_pid;

SET @market_index_pid = (SELECT id FROM fa_auth_rule WHERE name = 'market/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('market/detail', '查看详情', 2, 0, 1, @market_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('market/install', '安装插件', 2, 0, 1, @market_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('market/search', '搜索插件', 2, 0, 1, @market_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('market/getVersions', '获取版本', 2, 0, 1, @market_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('market/addReview', '添加评价', 2, 0, 1, @market_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('market/getReviews', '获取评价', 2, 0, 1, @market_index_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @market_index_pid;

SET @market_my_plugins_pid = (SELECT id FROM fa_auth_rule WHERE name = 'market/my_plugins' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('market/uninstall', '卸载插件', 2, 0, 1, @market_my_plugins_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('market/update', '更新插件', 2, 0, 1, @market_my_plugins_pid, '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `pid` = @market_my_plugins_pid;
