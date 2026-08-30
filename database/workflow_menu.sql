-- 工作流审批引擎菜单配置
-- 执行方式：mysql -u thinkmes -p thinkmes < workflow_menu.sql

-- 查询工作流菜单是否存在
SELECT @pid := id FROM fa_auth_rule WHERE name = 'workflow' LIMIT 1;

-- 如果不存在顶级菜单，则创建
INSERT IGNORE INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
VALUES (0, 'workflow', '工作流', 'fa fa-sitemap', '', '工作流审批引擎', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 100, 'normal');

-- 重新获取顶级菜单ID
SELECT @pid := id FROM fa_auth_rule WHERE name = 'workflow' LIMIT 1;

-- 删除旧的工作流菜单（如果存在）
DELETE FROM fa_auth_rule WHERE name LIKE 'workflow/%' AND pid = @pid;

-- 插入新的工作流菜单
-- 审批流定义
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
VALUES (@pid, 'workflow/definition/index', '审批流定义', 'fa fa-sitemap', '', '审批流定义管理', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 100, 'normal');

-- 审批流定义 - 查看列表
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/definition/index', '查看', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 100, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/definition/index';

-- 审批流定义 - 新建
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/definition/add', '新建', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 99, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/definition/index';

-- 审批流定义 - 编辑
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/definition/edit', '编辑', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 98, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/definition/index';

-- 审批流定义 - 删除
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/definition/del', '删除', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 97, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/definition/index';

-- 审批流定义 - 节点配置
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/definition/designer', '节点配置', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 96, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/definition/index';

-- 审批流定义 - 保存节点
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/definition/saveNodes', '保存节点', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 95, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/definition/index';

-- 审批流定义 - 切换状态
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/definition/toggle', '切换状态', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 94, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/definition/index';

-- 业务模块接入
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
VALUES (@pid, 'workflow/module/index', '业务模块接入', 'fa fa-cog', '', '业务模块接入配置', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 99, 'normal');

-- 业务模块接入 - 查看列表
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/module/index', '查看', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 100, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/module/index';

-- 业务模块接入 - 获取选项
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/module/options', '获取选项', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 99, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/module/index';

-- 业务模块接入 - 保存
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/module/save', '保存', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 98, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/module/index';

-- 审批中心
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
VALUES (@pid, 'workflow/approval/index', '审批中心', 'fa fa-tasks', '', '审批中心', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 98, 'normal');

-- 审批中心 - 查看列表
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/approval/index', '查看', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 100, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/approval/index';

-- 审批中心 - 待我审批
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/approval/pending', '待我审批', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 99, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/approval/index';

-- 审批中心 - 我已审批
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/approval/done', '我已审批', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 98, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/approval/index';

-- 审批中心 - 我发起的
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/approval/mine', '我发起的', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 97, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/approval/index';

-- 审批中心 - 审批详情
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/approval/detail', '审批详情', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 96, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/approval/index';

-- 审批中心 - 通过
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/approval/doApprove', '通过', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 95, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/approval/index';

-- 审批中心 - 拒绝
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/approval/doReject', '拒绝', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 94, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/approval/index';

-- 审批中心 - 转交
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/approval/doTransfer', '转交', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 93, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/approval/index';

-- 审批中心 - 撤回
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/approval/doWithdraw', '撤回', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 92, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/approval/index';

-- 审批中心 - 统计
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/approval/stats', '统计', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 91, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/approval/index';

-- 审批中心 - 管理员选项
INSERT INTO fa_auth_rule (`pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `createtime`, `updatetime`, `weigh`, `status`)
SELECT id, 'workflow/approval/adminOptions', '管理员选项', '', '', '', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 90, 'normal'
FROM fa_auth_rule WHERE name = 'workflow/approval/index';

-- 输出结果
SELECT '工作流菜单配置完成！' AS result;
SELECT COUNT(*) AS menu_count FROM fa_auth_rule WHERE name LIKE 'workflow%';
