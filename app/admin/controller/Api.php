<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\View;
use think\Response;

class Api extends Backend
{
    public function index(): string|Response
    {
        View::assign('title', 'API接口访问');
        $siteBase = rtrim((string) $this->request->domain(), '/');
        View::assign('api_base', $siteBase . '/api');
        View::assign('doc_url', $siteBase . '/api/doc');
        return $this->fetchWithLayout('api/index');
    }

    public function doc(): Response
    {
        $siteBase = rtrim((string) $this->request->domain(), '/');
        return redirect($siteBase . '/api/doc');
    }
}
