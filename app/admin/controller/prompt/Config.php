<?php
declare(strict_types=1);
namespace app\admin\controller\prompt;

use app\admin\controller\Backend;
use think\facade\Db;
use think\Response;

class Config extends Backend
{
    private array $configKeys = [
        'prompt_free_quota'  => ['title' => '新用户免费次数',           'type' => 'number'],
        'prompt_price_s'     => ['title' => '体验包价格(元/10次)',        'type' => 'text'],
        'prompt_price_m'     => ['title' => '畅享包价格(元/50次)',        'type' => 'text'],
        'prompt_price_month' => ['title' => '月度套餐价格(元/月/100次)', 'type' => 'text'],
        'prompt_enable_pay'  => ['title' => '是否开启付费功能',           'type' => 'switch'],
        'prompt_output_words'=> ['title' => '默认输出字数(约)',           'type' => 'number'],
    ];

    public function index(): string|Response
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            foreach ($this->configKeys as $key => $def) {
                $val = (string)($post[$key] ?? '');
                if ($def['type'] === 'switch') {
                    $val = isset($post[$key]) ? '1' : '0';
                }
                $exists = Db::name('config')->where('name', $key)->find();
                if ($exists) {
                    Db::name('config')->where('name', $key)->update(['value' => $val, 'update_time' => time()]);
                } else {
                    Db::name('config')->insert([
                        'name' => $key, 'title' => $def['title'],
                        'value' => $val, 'group' => 'prompt',
                        'sort' => 0, 'create_time' => time(), 'update_time' => time(),
                    ]);
                }
            }
            return $this->success('保存成功');
        }
        $configs = [];
        foreach ($this->configKeys as $key => $def) {
            $val = Db::name('config')->where('name', $key)->value('value');
            $configs[$key] = ['title' => $def['title'], 'type' => $def['type'], 'value' => $val ?? ''];
        }
        \think\facade\View::assign('configs', $configs);
        return $this->fetchWithLayout('prompt/config/index');
    }
}
