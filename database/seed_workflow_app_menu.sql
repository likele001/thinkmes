-- 工作流应用包：菜单与权限（与 CheckAuth 中 admin/workflow/... 路由一致）
-- 表前缀由应用中心安装时自动替换 fa_
-- 父级分组为「扩展功能」admin/_ext，pid=10 在 init_base 中与之一致

-- 1. 顶级菜单（挂在扩展功能下）
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/workflow/definition/index', '工作流定义', 1, 1, 1, 10, 'fas fa-project-diagram', 85, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/module/index', '业务模块', 1, 1, 1, 10, 'fas fa-cubes', 87, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/approval/index', '审批中心', 1, 1, 1, 10, 'fas fa-user-check', 84, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/instance/index', '工作流实例', 1, 1, 1, 10, 'fas fa-tasks', 86, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`), `ismenu` = VALUES(`ismenu`), `status` = VALUES(`status`), `pid` = VALUES(`pid`), `icon` = VALUES(`icon`), `sort` = VALUES(`sort`), `update_time` = VALUES(`update_time`);

-- 2. 流程定义子权限
SET @wfdef_pid = (SELECT id FROM fa_auth_rule WHERE name = 'admin/workflow/definition/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/workflow/definition/add', '新建', 2, 0, 1, @wfdef_pid, '', 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/definition/edit', '编辑', 2, 0, 1, @wfdef_pid, '', 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/definition/del', '删除', 2, 0, 1, @wfdef_pid, '', 97, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/definition/toggle', '启停', 2, 0, 1, @wfdef_pid, '', 96, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/definition/designer', '节点设计', 2, 0, 1, @wfdef_pid, '', 95, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/definition/saveNodes', '保存节点', 2, 0, 1, @wfdef_pid, '', 94, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = VALUES(`pid`), `status` = VALUES(`status`), `update_time` = VALUES(`update_time`);

-- 3. 业务模块子权限
SET @wfmod_pid = (SELECT id FROM fa_auth_rule WHERE name = 'admin/workflow/module/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/workflow/module/options', '模块选项', 2, 0, 1, @wfmod_pid, '', 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/module/save', '保存配置', 2, 0, 1, @wfmod_pid, '', 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = VALUES(`pid`), `status` = VALUES(`status`), `update_time` = VALUES(`update_time`);

-- 4. 审批中心子权限
SET @wfa_pid = (SELECT id FROM fa_auth_rule WHERE name = 'admin/workflow/approval/index' LIMIT 1);
INSERT INTO `fa_auth_rule` (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`) VALUES
('admin/workflow/approval/pending', '待我审批', 2, 0, 1, @wfa_pid, '', 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/approval/done', '我已审批', 2, 0, 1, @wfa_pid, '', 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/approval/mine', '我发起的', 2, 0, 1, @wfa_pid, '', 97, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/approval/detail', '审批详情', 2, 0, 1, @wfa_pid, '', 96, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/approval/doApprove', '通过', 2, 0, 1, @wfa_pid, '', 95, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/approval/doReject', '拒绝', 2, 0, 1, @wfa_pid, '', 94, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/approval/doTransfer', '转交', 2, 0, 1, @wfa_pid, '', 93, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/approval/doWithdraw', '撤回', 2, 0, 1, @wfa_pid, '', 92, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/approval/adminOptions', '审批人列表', 2, 0, 1, @wfa_pid, '', 91, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('admin/workflow/approval/stats', '统计', 2, 0, 1, @wfa_pid, '', 90, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `pid` = VALUES(`pid`), `status` = VALUES(`status`), `update_time` = VALUES(`update_time`);
