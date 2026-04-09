<?php
declare(strict_types=1);

namespace app\index\controller\prompt;

use think\facade\View;

/**
 * AI 提示词工坊 - AI 生成页（快速入口）
 */
class Generate extends BasePrompt
{
    public function index(): string
    {
        View::assign('title', 'AI 生成');
        return $this->fetchWithLayout('prompt/generate/index');
    }
}
