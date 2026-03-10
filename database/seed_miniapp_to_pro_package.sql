-- 为「专业版」套餐直接插入小程序功能（若添加功能页仍不显示时可执行本 SQL 作为补救）
-- 表前缀与 .env DB_PREFIX 一致，默认 fa_

INSERT INTO `fa_tenant_package_feature` (`package_id`, `feature_code`, `feature_name`, `create_time`)
SELECT p.id, 'admin/tenant/miniapp', '小程序（租户小程序配置）', UNIX_TIMESTAMP()
FROM `fa_tenant_package` p
WHERE p.name = '专业版'
  AND NOT EXISTS (
    SELECT 1 FROM `fa_tenant_package_feature` f
    WHERE f.package_id = p.id AND f.feature_code = 'admin/tenant/miniapp'
  );
