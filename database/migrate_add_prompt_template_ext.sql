-- 提示词模板：补充模板级别的输出要求与扩展变量（旧库增量执行）
-- 若报 Duplicate column name 说明已存在，可忽略

ALTER TABLE `fa_prompt_template` ADD COLUMN `output_words` int NOT NULL DEFAULT 0 COMMENT '最大字数(约，0不限制)' AFTER `system_prompt`;
ALTER TABLE `fa_prompt_template` ADD COLUMN `ext_prompt` text COMMENT '附加要求（会自动拼到最终提示词，可用{变量}）' AFTER `output_words`;
ALTER TABLE `fa_prompt_template` ADD COLUMN `ext_variables` text COMMENT '扩展变量定义JSON数组（会追加到 variables）' AFTER `ext_prompt`;
