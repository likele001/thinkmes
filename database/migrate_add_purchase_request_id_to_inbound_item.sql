-- 为采购入库明细表补充 purchase_request_id 列（若表由旧迁移创建可能缺失）
-- 表前缀非 fa_ 请替换后执行；若列已存在会报错，可忽略

ALTER TABLE `fa_mes_purchase_inbound_item`
  ADD COLUMN `purchase_request_id` int unsigned NOT NULL DEFAULT 0 COMMENT '关联采购申请ID' AFTER `inbound_id`;
