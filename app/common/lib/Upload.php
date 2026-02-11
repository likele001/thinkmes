<?php
declare(strict_types=1);

namespace app\common\lib;

use think\facade\Db;
use think\facade\Filesystem;
use think\File;

class Upload
{
    /** @var int 单文件最大字节 */
    protected int $maxSize;
    /** @var array 允许扩展名 */
    protected array $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'];

    public function __construct()
    {
        $conf = config('upload', []);
        $this->maxSize = (int) ($conf['max_size'] ?? 2097152);
    }

    /**
     * 当前存储是否为 OSS（占位：未接 SDK 时仍走本地）
     */
    public function isOss(): bool
    {
        return (config('upload.storage') ?? 'local') === 'oss';
    }

    public function handle($request, int $adminId = 0)
    {
        $file = $request->file('file') ?? $request->file('image');
        if (!$file || !$file->isValid()) {
            return '请选择文件';
        }
        $size = $file->getSize();
        if ($size > $this->maxSize) {
            return '文件大小不能超过 ' . ($this->maxSize / 1048576) . 'MB';
        }
        $ext = strtolower($file->extension());
        if (!in_array($ext, $this->allowedExt, true)) {
            return '不允许的文件类型';
        }
        $dir = 'uploads/' . date('Ymd') . '/';
        $saveName = date('His') . '_' . uniqid() . '.' . $ext;
        $root = app()->getRootPath() . 'public/uploads/';
        $subDir = date('Ymd') . '/';
        if (!is_dir($root . $subDir)) {
            @mkdir($root . $subDir, 0755, true);
        }
        try {
            $file->move($root . $subDir, $saveName);
        } catch (\Throwable $e) {
            return '上传失败:' . $e->getMessage();
        }
        $path = $subDir . $saveName;
        $url = $request->domain() . '/uploads/' . date('Ymd') . '/' . $saveName;
        $storage = $this->isOss() ? 'oss' : 'local';
        Db::name('upload')->insert([
            'admin_id' => $adminId,
            'url' => $url,
            'size' => $size,
            'mime_type' => $file->getOriginalMime(),
            'storage' => $storage,
            'create_time' => time(),
        ]);
        return ['url' => $url, 'path' => $path];
    }

    /**
     * 分片上传：保存单个分片到 runtime/chunks/{identifier}/{index}
     */
    public function saveChunk($request, string $identifier, int $index): array|string
    {
        $file = $request->file('file') ?? $request->file('chunk');
        if (!$file || !$file->isValid()) {
            return '请选择分片文件';
        }
        $chunkSize = (int) (config('upload.chunk_size') ?? 2097152);
        if ($file->getSize() > $chunkSize * 2) {
            return '分片过大';
        }
        $dir = app()->getRuntimePath() . 'chunks' . DIRECTORY_SEPARATOR . preg_replace('/[^a-zA-Z0-9_-]/', '', $identifier) . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $path = $dir . (string) $index;
        try {
            $file->move($dir, (string) $index);
        } catch (\Throwable $e) {
            return '分片保存失败:' . $e->getMessage();
        }
        return ['index' => $index];
    }

    /**
     * 合并分片并保存为正式文件，写入 fa_upload
     */
    public function mergeChunks($request, int $adminId = 0): array|string
    {
        $identifier = trim((string) $request->post('identifier', ''));
        $total = (int) $request->post('total', 0);
        $filename = trim((string) $request->post('filename', ''));
        if ($identifier === '' || $total < 1 || $filename === '') {
            return '参数错误';
        }
        $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $identifier);
        $dir = app()->getRuntimePath() . 'chunks' . DIRECTORY_SEPARATOR . $safeId . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            return '分片不存在';
        }
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExt, true)) {
            return '不允许的文件类型';
        }
        $subDir = date('Ymd') . '/';
        $saveName = date('His') . '_' . uniqid() . '.' . $ext;
        $root = app()->getRootPath() . 'public/uploads/';
        if (!is_dir($root . $subDir)) {
            @mkdir($root . $subDir, 0755, true);
        }
        $fullPath = $root . $subDir . $saveName;
        $fp = fopen($fullPath, 'wb');
        if (!$fp) {
            return '合并写入失败';
        }
        $size = 0;
        for ($i = 0; $i < $total; $i++) {
            $chunkPath = $dir . (string) $i;
            if (!is_file($chunkPath)) {
                fclose($fp);
                @unlink($fullPath);
                return '缺少分片:' . $i;
            }
            $size += filesize($chunkPath);
            fwrite($fp, file_get_contents($chunkPath));
            @unlink($chunkPath);
        }
        fclose($fp);
        if (is_dir($dir)) {
            @rmdir($dir);
        }
        $url = $request->domain() . '/uploads/' . $subDir . $saveName;
        Db::name('upload')->insert([
            'admin_id' => $adminId,
            'url' => $url,
            'size' => $size,
            'mime_type' => '',
            'storage' => $this->isOss() ? 'oss' : 'local',
            'create_time' => time(),
        ]);
        return ['url' => $url, 'path' => $subDir . $saveName];
    }
}
