<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\ConfigModel;
use think\facade\View;
use think\Response;

/**
 * 短信配置（使用 fa_config group=sms）
 */
class SmsConfig extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isPost()) {
            $list = $this->request->post('list');
            if (is_array($list)) {
                foreach ($list as $item) {
                    $name = trim((string) ($item['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $row = ConfigModel::where('name', $name)->find();
                    $value = $item['value'] ?? '';
                    if ($row) {
                        $row->value = $value;
                        $row->save();
                    } else {
                        ConfigModel::create([
                            'name'  => $name,
                            'value' => $value,
                            'group' => 'sms',
                            'sort'  => (int) ($item['sort'] ?? 0),
                        ]);
                    }
                }
            }
            return $this->success('保存成功');
        }

        $rows = ConfigModel::where('group', 'sms')->order('sort', 'asc')->select();
        $config = [];
        foreach ($rows as $r) {
            $config[$r->name] = $r->value;
        }
        $this->mergeViewConfig($config);
        View::assign('title', '短信配置');
        return $this->fetchWithLayout('sms_config/index');
    }
}
