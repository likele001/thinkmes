<?php
declare(strict_types=1);

namespace app\index\controller\wemedia;

use app\common\model\WemediaReportModel;
use think\facade\View;
use think\Response;

/**
 * 数据复盘
 */
class Report extends BaseWemedia
{
    public function index(): string
    {
        View::assign('title', '数据复盘');
        return $this->fetchWithLayout('wemedia/report/index');
    }

    public function list(): Response
    {
        $platform = trim((string) request()->get('platform', ''));
        $report_date = trim((string) request()->get('report_date', ''));
        $page = max(1, (int) request()->get('page', 1));
        $limit = min(100, max(10, (int) request()->get('limit', 20)));
        $query = WemediaReportModel::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->order('report_date', 'desc')
            ->order('id', 'desc');
        if ($platform !== '') {
            $query->where('platform', $platform);
        }
        if ($report_date !== '') {
            $query->where('report_date', $report_date);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['metric_type_text'] = WemediaReportModel::metricTypeText($row['metric_type'] ?? '');
        }
        unset($row);
        return $this->jsonSuccess('', ['total' => $total, 'list' => $list]);
    }

    /** 图表数据：按日期汇总 */
    public function chart(): Response
    {
        $platform = trim((string) request()->get('platform', ''));
        $days = min(90, max(7, (int) request()->get('days', 30)));
        $query = WemediaReportModel::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->where('report_date', '>=', date('Y-m-d', strtotime("-{$days} days")));
        if ($platform !== '') {
            $query->where('platform', $platform);
        }
        $rows = $query->field('report_date, metric_type, SUM(metric_value) as total')
            ->group('report_date, metric_type')
            ->order('report_date', 'asc')
            ->select()
            ->toArray();
        $dates = [];
        $series = ['view' => [], 'like' => [], 'comment' => [], 'share' => [], 'fan' => []];
        foreach ($rows as $r) {
            $d = $r['report_date'] ?? '';
            $t = $r['metric_type'] ?? 'view';
            $v = (float) ($r['total'] ?? 0);
            if (!in_array($d, $dates)) $dates[] = $d;
            if (!isset($series[$t])) $series[$t] = [];
            $series[$t][$d] = $v;
        }
        $dates = array_unique($dates);
        sort($dates);
        $out = [];
        foreach (['view' => '播放/阅读', 'like' => '点赞', 'comment' => '评论', 'share' => '分享', 'fan' => '涨粉'] as $k => $label) {
            $data = [];
            foreach ($dates as $d) {
                $data[] = $series[$k][$d] ?? 0;
            }
            $out[] = ['name' => $label, 'data' => $data];
        }
        return $this->jsonSuccess('', ['dates' => $dates, 'series' => $out]);
    }

    public function save(): Response
    {
        $id = (int) request()->post('id', 0);
        $platform = trim((string) request()->post('platform', ''));
        $report_date = trim((string) request()->post('report_date', ''));
        $metric_type = trim((string) request()->post('metric_type', 'view'));
        $metric_value = (float) request()->post('metric_value', 0);
        $remark = trim((string) request()->post('remark', ''));
        if ($report_date === '') return $this->jsonError('请选择数据日期');
        $now = time();
        $data = [
            'platform'     => $platform,
            'report_date'  => $report_date,
            'metric_type'  => $metric_type,
            'metric_value' => $metric_value,
            'remark'       => $remark,
            'update_time'  => $now,
        ];
        if ($id > 0) {
            $row = WemediaReportModel::where('id', $id)->where('tenant_id', $this->tenantId)->where('user_id', $this->userId)->find();
            if (!$row) return $this->jsonError('记录不存在');
            $row->save($data);
            return $this->jsonSuccess('保存成功', ['id' => $id]);
        }
        $data['tenant_id'] = $this->tenantId;
        $data['user_id'] = $this->userId;
        $data['create_time'] = $now;
        $m = new WemediaReportModel();
        $m->save($data);
        return $this->jsonSuccess('添加成功', ['id' => (int) $m->id]);
    }

    public function del(): Response
    {
        $id = (int) (request()->post('id', request()->get('id', 0)));
        if ($id <= 0) return $this->jsonError('参数错误');
        $row = WemediaReportModel::where('id', $id)->where('tenant_id', $this->tenantId)->where('user_id', $this->userId)->find();
        if (!$row) return $this->jsonError('记录不存在');
        $row->delete();
        return $this->jsonSuccess('删除成功');
    }
}
