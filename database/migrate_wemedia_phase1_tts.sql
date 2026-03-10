-- 阶段1：TTS + 口播成片（配音+图→视频）
-- 执行：mysql -u用户 -p 数据库名 < database/migrate_wemedia_phase1_tts.sql

-- 短视频脚本表：增加配音与成片路径
ALTER TABLE `fa_wemedia_video_script`
  ADD COLUMN `audio_path` varchar(500) NOT NULL DEFAULT '' COMMENT 'TTS 配音文件路径' AFTER `cover_path`,
  ADD COLUMN `video_path` varchar(500) NOT NULL DEFAULT '' COMMENT '口播成片视频路径' AFTER `audio_path`;
