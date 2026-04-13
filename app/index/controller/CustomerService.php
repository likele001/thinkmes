<?php
declare(strict_types=1);

namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\Lang;
use think\facade\View;
use think\facade\Request;
use think\Response;

class CustomerService extends BaseController
{
    /**
     * 确保语言设置
     */
    private function ensureLang(): void
    {
        $lang = Request::get('lang', '');
        if ($lang !== '') {
            Lang::setLangSet($lang);
        }
    }

    /**
     * 使用布局渲染视图
     */
    private function fetchWithLayout(string $template, string $layout = 'layout/default'): string
    {
        $this->ensureLang();
        View::assign('currentLang', Lang::getLangSet());
        $content = View::fetch($template);
        View::assign('__CONTENT__', $content);
        return View::fetch($layout);
    }

    /**
     * 客服中心首页
     */
    public function index(): string
    {
        View::assign('title', '客服中心');
        return $this->fetchWithLayout('customer_service/index');
    }
    
    /**
     * 智能问答页面
     */
    public function chat(): string
    {
        View::assign('title', '智能问答');
        return $this->fetchWithLayout('customer_service/index');
    }
    
    /**
     * 知识库页面
     */
    public function knowledge(): string
    {
        View::assign('title', '知识库');
        return $this->fetchWithLayout('customer_service/index');
    }
    
    /**
     * 工单系统页面
     */
    public function ticket(): string
    {
        View::assign('title', '工单系统');
        return $this->fetchWithLayout('customer_service/index');
    }
}
