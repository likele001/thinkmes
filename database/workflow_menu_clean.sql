-- 清理旧的工作流菜单并重新创建
-- 执行方式：mysql -u thinkmes -p123456 thinkmes < workflow_menu_clean.sql

-- 1. 删除所有旧的工作流菜单
DELETE FROM fa_auth_rule WHERE name LIKE 'workflow%' OR name LIKE 'workflow\_%';

-- 2. 创建顶级菜单
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `type`, `ismenu`, `status`, `sort`, `create_time`, `update_time`)
VALUES (0, 'workflow', '工作流', 'fa fa-sitemap', 1, 1, 1, 100, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取顶级菜单ID
SET @workflow_id = LAST_INSERT_ID();

-- 3. 审批流定义
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `type`, `ismenu`, `status`, `sort`, `create_time`, `update_time`)
VALUES (@workflow_id, 'workflow/definition/index', '审批流定义', 'fa fa-sitemap', 1, 1, 1, 100, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

SET @def_id = LAST_INSERT_ID();

-- 子权限
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `type`, `ismenu`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@def_id, 'workflow/definition/add', '新建', '', 0, 0, 1, 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@def_id, 'workflow/definition/edit', '编辑', '', 0, 0, 1, 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@def_id, 'workflow/definition/del', '删除', '', 0, 0, 1, 97, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@def_id, 'workflow/definition/designer', '节点配置', '', 0, 0, 1, 96, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@def_id, 'workflow/definition/saveNodes', '保存节点', '', 0, 0, 1, 95, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@def_id, 'workflow/definition/toggle', '切换状态', '', 0, 0, 1, 94, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 4. 业务模块接入
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `type`, `ismenu`, `status`, `sort`, `create_time`, `update_time`)
VALUES (@workflow_id, 'workflow/module/index', '业务模块接入', 'fa fa-cog', 1, 1, 1, 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

SET @module_id = LAST_INSERT_ID();

-- 子权限
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `type`, `ismenu`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@module_id, 'workflow/module/options', '获取选项', '', 0, 0, 1, 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@module_id, 'workflow/module/save', '保存', '', 0, 0, 1, 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 5. 审批中心
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `type`, `ismenu`, `status`, `sort`, `create_time`, `update_time`)
VALUES (@workflow_id, 'workflow/approval/index', '审批中心', 'fa fa-tasks', 1, 1, 1, 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

SET @approval_id = LAST_INSERT_ID();

-- 子权限
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `type`, `ismenu`, `status`, `sort`, `create_time`, `update_time`) VALUES
(@approval_id, 'workflow/approval/pending', '待我审批', '', 0, 0, 1, 99, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@approval_id, 'workflow/approval/done', '我已审批', '', 0, 0, 1, 98, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@approval_id, 'workflow/approval/mine', '我发起的', '', 0, 0, 1, 97, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@approval_id, 'workflow/approval/detail', '审批详情', '', 0, 0, 1, 96, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@approval_id, 'workflow/approval/doApprove', '通过', '', 0, 0, 1, 95, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@approval_id, 'workflow/approval/doReject', '拒绝', '', 0, 0, 1, 94, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@approval_id, 'workflow/approval/doTransfer', '转交', '', 0, 0, 1, 93, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@approval_id, 'workflow/approval/doWithdraw', '撤回', '', 0, 0, 1, 92, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@approval_id, 'workflow/approval/stats', '统计', '', 0, 0, 1, 91, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(@approval_id, 'workflow/approval/adminOptions', '管理员选项', '', 0, 0, 1, 90, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 输出结果
SELECT '工作流菜单重新配置完成！' AS result;
SELECT COUNT(*) AS menu_count FROM fa_auth_rule WHERE name LIKE 'workflow/%';
