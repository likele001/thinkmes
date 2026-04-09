<?php
declare(strict_types=1);
namespace app\admin\controller\prompt;

use app\admin\controller\Backend;
use app\admin\model\prompt\PromptAiConfigModel;
use app\common\lib\prompt\PromptAiService;
use think\facade\View;
use think\Response;

class AiConfig extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            [$limit, $page] = $this->getPaginationParams();
            $list  = PromptAiConfigModel::order('sort asc, id asc')->page($page, $limit)->select()->toArray();
            $total = PromptAiConfigModel::count();
            // 隐藏 api_key 中间部分
            foreach ($list as &$item) {
                $key = (string)($item['api_key'] ?? '');
                if (strlen($key) > 8) {
                    $item['api_key_masked'] = substr($key, 0, 4) . str_repeat('*', min(12, strlen($key) - 8)) . substr($key, -4);
                } else {
                    $item['api_key_masked'] = '****';
                }
            }
            return $this->success('', ['total' => $total, 'rows' => $list]);
        }
        return $this->fetchWithLayout('prompt/ai_config/index');
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['create_time'] = $data['update_time'] = time();
            // 新增时如果设为启用，将其他配置禁用（只允许一条启用）
            if ((int)($data['status'] ?? 0) === 1) {
                PromptAiConfigModel::where('status', 1)->update(['status' => 0, 'update_time' => time()]);
            }
            PromptAiConfigModel::create($data);
            return $this->success('添加成功');
        }
        return $this->fetchWithLayout('prompt/ai_config/add');
    }

    public function edit(): string|Response
    {
        $id  = (int)$this->request->param('id', 0);
        $row = PromptAiConfigModel::find($id);
        if (!$row) return $this->error('记录不存在');
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['update_time'] = time();
            unset($data['id']);
            if ((int)($data['status'] ?? 0) === 1) {
                PromptAiConfigModel::where('id', '<>', $id)->where('status', 1)
                    ->update(['status' => 0, 'update_time' => time()]);
            }
            PromptAiConfigModel::where('id', $id)->update($data);
            return $this->success('保存成功');
        }
        View::assign('row', $row->toArray());
        return $this->fetchWithLayout('prompt/ai_config/edit');
    }

    public function del(): Response
    {
        $ids = (array)$this->request->post('ids', []);
        if (empty($ids)) return $this->error('请选择要删除的记录');
        PromptAiConfigModel::whereIn('id', $ids)->delete();
        return $this->success('删除成功');
    }

    /** 测试连接 */
    public function test(): Response
    {
        $id  = (int)$this->request->post('id', 0);
        if ($id > 0) {
            $row = PromptAiConfigModel::find($id);
            if (!$row) return $this->error('配置不存在');
            $cfg = $row->toArray();
        } else {
            $cfg = $this->request->post();
        }
        $result = PromptAiService::testConnection($cfg);
        return $result['ok'] ? $this->success($result['msg']) : $this->error($result['msg']);
    }
}
