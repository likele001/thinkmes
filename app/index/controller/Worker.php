<?php
declare(strict_types=1);

namespace app\index\controller;

use think\facade\View;
use think\facade\Request;
use think\Response;

class Worker
{
    private function fetchWithLayout(string $template): string
    {
        $content = View::fetch($template);
        View::assign('__CONTENT__', $content);
        return View::fetch('layout/default');
    }

    public function scan(): string|Response
    {
        $token = (string) (Request::cookie('user_token') ?? '');
        if ($token === '') {
            return redirect((string) url('user/login'));
        }
        $allocationId = (int) Request::get('allocation_id', 0);
        View::assign('title', '扫码报工');
        View::assign('allocation_id', $allocationId);
        return $this->fetchWithLayout('worker/scan');
    }
}

