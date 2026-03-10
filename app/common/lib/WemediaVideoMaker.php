<?php
declare(strict_types=1);

namespace app\common\lib;

use think\facade\Log;

/**
 * 口播成片：音频 + 封面图 → MP4
 * 依赖 FFmpeg，将一张图与一段音频合成为固定画面的口播视频
 */
class WemediaVideoMaker
{
    protected string $lastError = '';

    /**
     * 合成口播视频（图 + 音频 → MP4）
     * @param string $audioPath 音频相对路径，如 uploads/wemedia_audio/20250305/xxx.mp3（相对 public）
     * @param string $coverPath 封面图相对路径，如 uploads/20250101/xxx.jpg（相对 public）
     * @return string|null 成功返回相对路径 uploads/wemedia_video/YYYYMMDD/xxx.mp4，失败返回 null
     */
    public function makeSlideVideo(string $audioPath, string $coverPath): ?string
    {
        $root = app()->getRootPath() . 'public/';
        $audioFull = $root . ltrim($audioPath, '/');
        $coverFull = $root . ltrim($coverPath, '/');
        if (!is_file($audioFull)) {
            $this->lastError = '音频文件不存在';
            return null;
        }
        if (!is_file($coverFull)) {
            $this->lastError = '封面图不存在';
            return null;
        }
        $outSubDir = 'uploads/wemedia_video/' . date('Ymd') . '/';
        $outDir = $root . $outSubDir;
        if (!is_dir($outDir)) {
            @mkdir($outDir, 0755, true);
        }
        $outFilename = date('His') . '_' . uniqid() . '.mp4';
        $outFull = $outDir . $outFilename;
        $cmd = 'ffmpeg -y -loop 1 -i ' . escapeshellarg($coverFull) . ' -i ' . escapeshellarg($audioFull)
            . ' -c:v libx264 -tune stillimage -shortest -pix_fmt yuv420p -c:a aac ' . escapeshellarg($outFull) . ' 2>&1';
        try {
            $output = [];
            @exec($cmd, $output, $code);
            $outStr = implode("\n", $output);
            if ($code !== 0 || !is_file($outFull)) {
                $this->lastError = 'FFmpeg 合成失败';
                Log::warning('WemediaVideoMaker ffmpeg: code=' . $code . ' ' . $outStr);
                return null;
            }
            return $outSubDir . $outFilename;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            Log::error('WemediaVideoMaker: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 多段视频拼接为一条（FFmpeg concat demuxer）
     * @param string[] $relativePaths 相对路径数组，如 ['uploads/wemedia_video/20250305/a.mp4', '.../b.mp4']
     * @return string|null 成功返回合并后的相对路径，失败返回 null
     */
    public function concatVideos(array $relativePaths): ?string
    {
        $root = app()->getRootPath() . 'public/';
        $fullPaths = [];
        foreach ($relativePaths as $p) {
            $full = $root . ltrim($p, '/');
            if (!is_file($full)) {
                $this->lastError = '片段不存在: ' . $p;
                return null;
            }
            $fullPaths[] = $full;
        }
        if (count($fullPaths) < 2) {
            $this->lastError = '至少需要 2 个视频才能拼接';
            return null;
        }
        $outSubDir = 'uploads/wemedia_video/' . date('Ymd') . '/';
        $outDir = $root . $outSubDir;
        if (!is_dir($outDir)) {
            @mkdir($outDir, 0755, true);
        }
        $outFilename = date('His') . '_' . uniqid() . '_concat.mp4';
        $outFull = $outDir . $outFilename;
        $listFile = $outDir . 'concat_list_' . uniqid() . '.txt';
        $listContent = implode("\n", array_map(function ($path) {
            return "file " . "'" . str_replace("'", "'\\''", $path) . "'";
        }, $fullPaths));
        if (file_put_contents($listFile, $listContent) === false) {
            $this->lastError = '创建拼接列表失败';
            return null;
        }
        try {
            $cmd = 'ffmpeg -y -f concat -safe 0 -i ' . escapeshellarg($listFile) . ' -c copy ' . escapeshellarg($outFull) . ' 2>&1';
            $output = [];
            @exec($cmd, $output, $code);
            @unlink($listFile);
            if ($code !== 0 || !is_file($outFull)) {
                $this->lastError = 'FFmpeg 拼接失败';
                Log::warning('WemediaVideoMaker concat: code=' . $code . ' ' . implode("\n", $output));
                return null;
            }
            return $outSubDir . $outFilename;
        } catch (\Throwable $e) {
            @unlink($listFile);
            $this->lastError = $e->getMessage();
            Log::error('WemediaVideoMaker concat: ' . $e->getMessage());
            return null;
        }
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }
}
