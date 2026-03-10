<?php
declare(strict_types=1);

namespace app\common\lib;

/**
 * 二维码生成：使用 endroid/qr-code 生成 PNG 并保存到本地
 * 保存路径：public/uploads/qrcode/{tenant_id}/allocation_{allocation_id}.png
 */
class QrCodeService
{
    /** 相对目录（相对 public 下），用于 URL 与存储 */
    public const UPLOAD_SUBDIR = 'uploads/qrcode';

    /**
     * 生成二维码 PNG 并保存到本地
     * @param string $content 二维码内容（如报工链接 URL）
     * @param int $tenantId 租户ID，用于目录隔离
     * @param int $allocationId 分工ID，用于文件名
     * @param int $size 图片边长（像素），默认 280
     * @return string 成功返回相对路径（如 uploads/qrcode/1/allocation_23.png），失败返回空字符串
     */
    public static function generateAndSave(string $content, int $tenantId, int $allocationId, int $size = 280): string
    {
        if ($content === '') {
            return '';
        }
        $root = app()->getRootPath() . 'public/';
        $subDir = self::UPLOAD_SUBDIR . '/' . max(0, $tenantId) . '/';
        $dir = $root . $subDir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $filename = 'allocation_' . $allocationId . '.png';
        $fullPath = $dir . $filename;

        try {
            // endroid/qr-code 5.x: Builder::create()->data()->size()->margin()->build()
            if (class_exists(\Endroid\QrCode\Builder\Builder::class)) {
                $builder = \Endroid\QrCode\Builder\Builder::create()
                    ->data($content)
                    ->size($size)
                    ->margin(10);
                $result = $builder->build();
                $result->saveToFile($fullPath);
                return $subDir . $filename;
            }
            // endroid/qr-code 4.x: QrCode::create + PngWriter
            if (class_exists(\Endroid\QrCode\QrCode::class) && class_exists(\Endroid\QrCode\Writer\PngWriter::class)) {
                $qrCode = \Endroid\QrCode\QrCode::create($content)->setSize($size)->setMargin(10);
                $writer = new \Endroid\QrCode\Writer\PngWriter();
                $result = $writer->write($qrCode);
                if (method_exists($result, 'saveToFile')) {
                    $result->saveToFile($fullPath);
                } else {
                    file_put_contents($fullPath, $result->getString());
                }
                return $subDir . $filename;
            }
        } catch (\Throwable $e) {
            // 记录但不抛错，返回空表示未生成
        }
        return '';
    }

    /**
     * 根据相对路径拼出完整访问 URL
     */
    public static function pathToUrl(string $relativePath): string
    {
        if ($relativePath === '') {
            return '';
        }
        $path = ltrim($relativePath, '/');
        if (strpos($path, 'uploads/') !== 0) {
            $path = 'uploads/' . ltrim($path, '/');
        }
        return rtrim((string) request()->domain(), '/') . '/' . $path;
    }
}
