<?php
declare(strict_types=1);

namespace app\index\controller\wemedia;

use app\common\model\WemediaCopyModel;
use app\common\model\WemediaScheduleModel;
use app\common\model\WemediaVideoScriptModel;
use think\facade\View;
use think\Response;

/**
 * 发布排期
 */
class Schedule extends BaseWemedia
{
    public function index(): string
    {
        View::assign('title', '发布排期');
        return $this->fetchWithLayout('wemedia/schedule/index');
    }

    public function add(): string
    {
        View::assign('title', '添加排期');
        View::assign('item', null);
        View::assign('itemJson', '{}');
        View::assign('relateOptions', $this->getRelateOptions());
        return $this->fetchWithLayout('wemedia/schedule/edit');
    }

    public function edit(): string|Response
    {
        $id = (int) request()->get('id', 0);
        if ($id <= 0) return redirect('/index/wemedia/schedule/index');
        $item = WemediaScheduleModel::where('id', $id)
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->find();
        if (!$item) return redirect('/index/wemedia/schedule/index');
        View::assign('title', '编辑排期');
        View::assign('item', $item);
        View::assign('itemJson', json_encode($item->toArray()));
        View::assign('relateOptions', $this->getRelateOptions());
        return $this->fetchWithLayout('wemedia/schedule/edit');
    }

    private function getRelateOptions(): array
    {
        $opts = [['value' => '', 'label' => '请选择']];
        $copies = WemediaCopyModel::where('tenant_id', $this->tenantId)->where('user_id', $this->userId)->order('id', 'desc')->limit(50)->select();
        foreach ($copies as $c) {
            $opts[] = ['value' => 'copy_' . $c->id, 'label' => '文案：' . ($c->title ?: 'ID' . $c->id)];
        }
        $videos = WemediaVideoScriptModel::where('tenant_id', $this->tenantId)->where('user_id', $this->userId)->order('id', 'desc')->limit(50)->select();
        foreach ($videos as $v) {
            $opts[] = ['value' => 'video_' . $v->id, 'label' => '短视频：' . ($v->title ?: 'ID' . $v->id)];
        }
        return $opts;
    }

    public function list(): Response
    {
        $status = request()->get('status', '');
        $page = max(1, (int) request()->get('page', 1));
        $limit = min(100, max(10, (int) request()->get('limit', 20)));
        $query = WemediaScheduleModel::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->order('plan_time', 'desc');
        if ($status !== '') {
            $query->where('status', (int) $status);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['status_text'] = WemediaScheduleModel::statusText((int) ($row['status'] ?? 0));
            $row['relate_type_text'] = WemediaScheduleModel::relateTypeText($row['relate_type'] ?? '');
            $row['plan_time_text'] = $row['plan_time'] ? date('Y-m-d H:i', (int) $row['plan_time']) : '-';
        }
        unset($row);
        return $this->jsonSuccess('', ['total' => $total, 'list' => $list]);
    }

    public function save(): Response
    {
        $id = (int) request()->post('id', 0);
        $relate = trim((string) request()->post('relate', ''));
        $platform = trim((string) request()->post('platform', ''));
        $plan_time = trim((string) request()->post('plan_time', ''));
        $remark = trim((string) request()->post('remark', ''));
        $status = (int) request()->post('status', 0);
        $relate_type = 'copy';
        $relate_id = 0;
        if (preg_match('/^(copy|video)_(\d+)$/', $relate, $m)) {
            $relate_type = $m[1];
            $relate_id = (int) $m[2];
        }
        if ($relate_id <= 0) return $this->jsonError('请选择关联作品');
        $plan_ts = $plan_time ? strtotime($plan_time) : time();
        $now = time();
        $data = [
            'relate_type' => $relate_type,
            'relate_id'   => $relate_id,
            'platform'    => $platform,
            'plan_time'   => $plan_ts,
            'remark'      => $remark,
            'status'      => $status,
            'update_time' => $now,
        ];
        if ($id > 0) {
            $row = WemediaScheduleModel::where('id', $id)->where('tenant_id', $this->tenantId)->where('user_id', $this->userId)->find();
            if (!$row) return $this->jsonError('记录不存在');
            $row->save($data);
            return $this->jsonSuccess('保存成功', ['id' => $id]);
        }
        $data['tenant_id'] = $this->tenantId;
        $data['user_id'] = $this->userId;
        $data['create_time'] = $now;
        $m = new WemediaScheduleModel();
        $m->save($data);
        return $this->jsonSuccess('添加成功', ['id' => (int) $m->id]);
    }

    public function del(): Response
    {
        $id = (int) (request()->post('id', request()->get('id', 0)));
        if ($id <= 0) return $this->jsonError('参数错误');
        $row = WemediaScheduleModel::where('id', $id)->where('tenant_id', $this->tenantId)->where('user_id', $this->userId)->find();
        if (!$row) return $this->jsonError('记录不存在');
        $row->delete();
        return $this->jsonSuccess('删除成功');
    }
}
