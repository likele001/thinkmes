-- 为 fa_auth_rule 表添加 ismenu 字段（是否在菜单中显示）
-- 如果字段已存在会报错，可忽略或手动删除后重试

ALTER TABLE `fa_auth_rule` ADD COLUMN `ismenu` tinyint NOT NULL DEFAULT 1 COMMENT '是否菜单：1显示 0隐藏' AFTER `type`;

-- 更新现有数据：type=1（菜单）的设为 ismenu=1，type=2/3（按钮/接口）的设为 ismenu=0
UPDATE `fa_auth_rule` SET `ismenu` = 1 WHERE `type` = 1;
UPDATE `fa_auth_rule` SET `ismenu` = 0 WHERE `type` IN (2, 3);
