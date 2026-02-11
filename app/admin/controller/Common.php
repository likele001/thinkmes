<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\lib\Hook;
use app\common\lib\Upload;
use think\facade\Session;
use think\Response;

class Common extends Backend
{
    /**
     * 文件上传
     */
    public function upload(): Response
    {
        $admin = Session::get('admin_info');
        $adminId = $admin['id'] ?? 0;
        $upload = new Upload();
        $result = $upload->handle($this->request, $adminId);
        if (is_array($result) && isset($result['url'])) {
            return $this->success('上传成功', $result);
        }
        return $this->error(is_string($result) ? $result : '上传失败');
    }

    /**
     * 分片上传：上传单个分片
     * POST identifier, index, file(chunk)
     */
    public function uploadChunk(): Response
    {
        $identifier = trim((string) $this->request->post('identifier', ''));
        $index = (int) $this->request->post('index', -1);
        if ($identifier === '' || $index < 0) {
            return $this->error('参数错误');
        }
        $upload = new Upload();
        $result = $upload->saveChunk($this->request, $identifier, $index);
        if (is_array($result)) {
            return $this->success('分片已上传', $result);
        }
        return $this->error(is_string($result) ? $result : '上传失败');
    }

    /**
     * 分片上传：合并分片并保存
     * POST identifier, total, filename
     */
    public function mergeChunks(): Response
    {
        $admin = Session::get('admin_info');
        $adminId = $admin['id'] ?? 0;
        $upload = new Upload();
        $result = $upload->mergeChunks($this->request, $adminId);
        if (is_array($result) && isset($result['url'])) {
            Hook::trigger('upload_after', [$result]);
            return $this->success('上传成功', $result);
        }
        return $this->error(is_string($result) ? $result : '合并失败');
    }
}
