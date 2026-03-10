-- 阶段2：AI 图生视频 + 数字人
-- 执行：mysql -u用户 -p 数据库名 < database/migrate_wemedia_phase2_ai_video.sql
-- 若已执行过仅含 ai_video_path 的版本，可只执行下面第二条（数字人）：
-- ALTER TABLE `fa_wemedia_video_script` ADD COLUMN `digital_human_path` varchar(500) NOT NULL DEFAULT '' COMMENT '数字人播报视频路径' AFTER `ai_video_path`;

-- 短视频脚本表：AI 图生视频 + 数字人成片路径
ALTER TABLE `fa_wemedia_video_script`
  ADD COLUMN `ai_video_path` varchar(500) NOT NULL DEFAULT '' COMMENT 'AI 图/文生成视频路径' AFTER `video_path`,
  ADD COLUMN `digital_human_path` varchar(500) NOT NULL DEFAULT '' COMMENT '数字人播报视频路径' AFTER `ai_video_path`;
