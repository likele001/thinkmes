<?php
declare(strict_types=1);

namespace app\index\controller\wemedia;

use app\common\model\WemediaComplianceLogModel;
use think\facade\View;
use think\Response;

/**
 * 合规检测
 */
class Compliance extends BaseWemedia
{
    public function index(): string
    {
        View::assign('title', '合规检测');
        return $this->fetchWithLayout('wemedia/compliance/index');
    }

    public function list(): Response
    {
        $page = max(1, (int) request()->get('page', 1));
        $limit = min(100, max(10, (int) request()->get('limit', 20)));
        $query = WemediaComplianceLogModel::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->order('id', 'desc');
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['result_text'] = WemediaComplianceLogModel::resultText((int) ($row['result'] ?? 0));
            $row['content_preview'] = $row['content_text'] ? mb_substr(strip_tags((string) $row['content_text']), 0, 60) . '...' : '-';
        }
        unset($row);
        return $this->jsonSuccess('', ['total' => $total, 'list' => $list]);
    }

    /** 执行检测（占位：返回模拟结果，后续对接内容安全 API） */
    public function check(): Response
    {
        $content_type = trim((string) request()->post('content_type', 'text'));
        $content_text = trim((string) request()->post('content_text', ''));
        $file_path = trim((string) request()->post('file_path', ''));
        if ($content_text === '' && $file_path === '') {
            return $this->jsonError('请输入待检测文案或上传文件');
        }
        $result = 0;
        $suggestion = '当前为演示结果，接入内容安全 API 后将返回真实检测结果。';
        if (stripos($content_text, '违规') !== false || stripos($content_text, '敏感') !== false) {
            $result = 1;
            $suggestion = '建议修改或删除敏感用词后重新发布。';
        }
        $m = new WemediaComplianceLogModel();
        $m->save([
            'tenant_id'    => $this->tenantId,
            'user_id'     => $this->userId,
            'content_type' => $content_type,
            'content_text' => $content_text,
            'file_path'   => $file_path,
            'result'      => $result,
            'suggestion'  => $suggestion,
            'create_time' => time(),
        ]);
        return $this->jsonSuccess('', [
            'result'     => $result,
            'result_text'=> WemediaComplianceLogModel::resultText($result),
            'suggestion' => $suggestion,
            'id'         => (int) $m->id,
        ]);
    }

    public function del(): Response
    {
        $id = (int) (request()->post('id', request()->get('id', 0)));
        if ($id <= 0) return $this->jsonError('参数错误');
        $row = WemediaComplianceLogModel::where('id', $id)->where('tenant_id', $this->tenantId)->where('user_id', $this->userId)->find();
        if (!$row) return $this->jsonError('记录不存在');
        $row->delete();
        return $this->jsonSuccess('删除成功');
    }
}
