<?php
declare(strict_types=1);

namespace app\index\controller\prompt;

use think\facade\View;

/**
 * AI 提示词工坊 - 模板浏览
 */
class Template extends BasePrompt
{
    public function index(): string
    {
        View::assign('title', '模板广场');
        return $this->fetchWithLayout('prompt/template/index');
    }

    public function detail(): string
    {
        $id = (int) request()->get('id', 0);
        View::assign('title', '使用模板');
        View::assign('templateId', $id);
        return $this->fetchWithLayout('prompt/template/detail');
    }
}
