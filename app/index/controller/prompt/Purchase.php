<?php
declare(strict_types=1);

namespace app\index\controller\prompt;

use think\facade\View;

/**
 * AI 提示词工坊 - 额度购买
 */
class Purchase extends BasePrompt
{
    public function index(): string
    {
        View::assign('title', '购买额度');
        return $this->fetchWithLayout('prompt/purchase/index');
    }
}
