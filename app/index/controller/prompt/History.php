<?php
declare(strict_types=1);

namespace app\index\controller\prompt;

use think\facade\View;

/**
 * AI 提示词工坊 - 生成历史
 */
class History extends BasePrompt
{
    public function index(): string
    {
        View::assign('title', '生成历史');
        return $this->fetchWithLayout('prompt/history/index');
    }
}
