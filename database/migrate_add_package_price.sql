-- 为租户套餐表添加价格字段
ALTER TABLE `fa_tenant_package` 
ADD COLUMN `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '套餐价格(元)' AFTER `expire_days`;

-- 更新现有套餐价格（示例）
UPDATE `fa_tenant_package` SET `price` = 0 WHERE `price` = 0;
