<?php
declare(strict_types=1);

namespace app\index\controller\prompt;

use think\facade\View;

/**
 * AI 提示词工坊 - 首页/工作台
 */
class Index extends BasePrompt
{
    public function index(): string
    {
        View::assign('title', 'AI提示词工坊');
        return $this->fetchWithLayout('prompt/index');
    }
}
