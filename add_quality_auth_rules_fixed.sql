-- 添加质检管理权限规则（修复版）

-- 1. 添加质检管理菜单
INSERT IGNORE INTO fa_auth_rule (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('mes/quality/index', '质检管理', 1, 1, 1, 0, 'fas fa-check-circle', 50, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取质检管理菜单的ID
SET @quality_menu_id = (
    SELECT id FROM fa_auth_rule 
    WHERE name = 'mes/quality/index'
    LIMIT 1
);

-- 2. 添加质检标准管理子菜单
INSERT IGNORE INTO fa_auth_rule (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('mes/quality/standard', '质检标准管理', 1, 1, 1, @quality_menu_id, 'fas fa-list', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取质检标准管理子菜单的ID
SET @quality_standard_menu_id = (
    SELECT id FROM fa_auth_rule 
    WHERE name = 'mes/quality/standard'
    LIMIT 1
);

-- 3. 添加质检记录管理子菜单
INSERT IGNORE INTO fa_auth_rule (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('mes/quality/check', '质检记录管理', 1, 1, 1, @quality_menu_id, 'fas fa-clipboard-check', 20, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取质检记录管理子菜单的ID
SET @quality_check_menu_id = (
    SELECT id FROM fa_auth_rule 
    WHERE name = 'mes/quality/check'
    LIMIT 1
);

-- 4. 添加质检统计子菜单
INSERT IGNORE INTO fa_auth_rule (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('mes/quality/statistics', '质检统计', 1, 1, 1, @quality_menu_id, 'fas fa-chart-bar', 30, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取质检统计子菜单的ID
SET @quality_statistics_menu_id = (
    SELECT id FROM fa_auth_rule 
    WHERE name = 'mes/quality/statistics'
    LIMIT 1
);

-- 5. 添加添加质检标准权限
INSERT IGNORE INTO fa_auth_rule (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('mes/quality/addStandard', '添加质检标准', 2, 0, 1, @quality_standard_menu_id, '', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 6. 添加添加质检记录权限
INSERT IGNORE INTO fa_auth_rule (`name`, `title`, `type`, `ismenu`, `status`, `pid`, `icon`, `sort`, `create_time`, `update_time`)
VALUES ('mes/quality/addCheck', '添加质检记录', 2, 0, 1, @quality_check_menu_id, '', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 7. 清理变量
SET @quality_menu_id = NULL;
SET @quality_standard_menu_id = NULL;
SET @quality_check_menu_id = NULL;
SET @quality_statistics_menu_id = NULL;

-- 8. 输出结果
SELECT '质检管理权限规则添加完成' AS message;