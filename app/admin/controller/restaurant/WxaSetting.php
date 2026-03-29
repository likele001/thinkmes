<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use think\facade\Db;
use think\facade\View;
use think\Response;

class WxaSetting extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isPost()) {
            $appid = trim((string) $this->request->post('appid', ''));
            $secret = trim((string) $this->request->post('secret', ''));
            $page = trim((string) $this->request->post('page', 'pages/menu/index'));
            $now = time();
            $this->upsert('restaurant_wxa_appid', '餐饮小程序AppID', $appid, $now);
            $this->upsert('restaurant_wxa_secret', '餐饮小程序Secret', $secret, $now);
            $this->upsert('restaurant_wxa_page', '餐饮小程序页面路径', $page, $now);
            return $this->success('已保存');
        }
        $appid = (string) Db::name('config')->where('name', 'restaurant_wxa_appid')->value('value');
        $secret = (string) Db::name('config')->where('name', 'restaurant_wxa_secret')->value('value');
        $page = (string) Db::name('config')->where('name', 'restaurant_wxa_page')->value('value');
        View::assign('appid', $appid);
        View::assign('secret', $secret);
        View::assign('page', $page ?: 'pages/menu/index');
        View::assign('title', '餐饮小程序配置');
        return $this->fetchWithLayout('restaurant/wxa_setting/index');
    }

    private function upsert(string $name, string $title, string $value, int $now): void
    {
        $row = Db::name('config')->where('name', $name)->find();
        if ($row) {
            Db::name('config')->where('name', $name)->update(['value' => $value, 'update_time' => $now]);
            return;
        }
        Db::name('config')->insert([
            'name' => $name,
            'title' => $title,
            'value' => $value,
            'group' => 'base',
            'sort' => 0,
            'create_time' => $now,
            'update_time' => $now,
        ]);
    }
}

