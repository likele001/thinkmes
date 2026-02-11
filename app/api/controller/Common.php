<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\lib\Upload;
use app\api\middleware\UserAuth;
use think\Response;

/**
 * C端用户：文件上传（需登录）
 */
class Common extends BaseController
{
    /**
     * 文件上传（头像、图片等）
     * POST file 或 image
     */
    public function upload(): Response
    {
        $info = $this->request->userInfo ?? [];
        if (empty($info)) {
            return $this->error('请先登录', [], 401);
        }
        $userId = (int) ($this->request->userId ?? 0);
        
        $upload = new Upload();
        $result = $upload->handle($this->request, $userId);
        if (is_array($result) && isset($result['url'])) {
            return $this->success('上传成功', $result);
        }
        return $this->error(is_string($result) ? $result : '上传失败');
    }
}
