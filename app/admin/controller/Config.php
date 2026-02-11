<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\ConfigModel;
use think\facade\Db;
use think\facade\View;
use think\Response;

class Config extends Backend
{
    /** 配置分组（多语言/缓存/安全等） */
    private const GROUPS = [
        'base'   => '基本',
        'upload' => '上传',
        'safe'   => '安全',
        'lang'   => '多语言',
        'cache'  => '缓存',
    ];

    public function index(): string|Response
    {
        $group = trim((string) $this->request->get('group', ''));
        // 带 group 参数或 Ajax 请求时返回 JSON 配置列表，便于 config.js 拉取
        if ($group !== '' || $this->request->isAjax()) {
            $g = $group !== '' ? $group : 'base';
            $list = ConfigModel::where('group', $g)->order('sort', 'asc')->order('id', 'asc')->select()->toArray();
            return $this->success('', $list);
        }
        View::assign('title', '系统配置');
        return $this->fetchWithLayout('config/index');
    }

    /**
     * 获取分组列表（供前端切换 tab）
     */
    public function group(): Response
    {
        return $this->success('', array_keys(self::GROUPS));
    }

    /**
     * 保存配置 POST name=>value 或 list=>[{name,value},...]
     */
    public function save(): Response
    {
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
                    ConfigModel::create(['name' => $name, 'value' => $value, 'group' => (string) ($item['group'] ?? 'base'), 'sort' => (int) ($item['sort'] ?? 0)]);
                }
            }
        } else {
            $name = trim((string) $this->request->post('name', ''));
            $value = $this->request->post('value', '');
            if ($name === '') {
                return $this->error('name 不能为空');
            }
            $row = ConfigModel::where('name', $name)->find();
            if ($row) {
                $row->value = $value;
                $row->save();
            } else {
                ConfigModel::create(['name' => $name, 'value' => $value, 'group' => trim((string) $this->request->post('group', 'base')), 'sort' => 0]);
            }
        }
        return $this->success('保存成功');
    }
}
