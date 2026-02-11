<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class AuthRuleModel extends Model
{
    protected $name = 'auth_rule';

    protected $type = [
        'create_time' => 'integer',
        'update_time' => 'integer',
    ];

    /**
     * 获取树形列表
     */
    public function getTree(int $pid = 0, bool $menuOnly = false): array
    {
        $query = $this->order('sort', 'asc')->order('id', 'asc');
        if ($menuOnly) {
            // 菜单模式：只显示状态为1的菜单类型规则
            $query->where('status', 1)->where('type', 1)->where('ismenu', 1);
        } else {
            // 列表模式：显示所有规则（包括禁用的），用于管理页面
            // 不限制status，这样可以管理禁用的规则
        }
        $list = $query->select()->toArray();
        return $this->buildTree($list, $pid);
    }

    protected function buildTree(array $list, int $pid): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ((int) $item['pid'] === $pid) {
                $item['children'] = $this->buildTree($list, (int) $item['id']);
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
