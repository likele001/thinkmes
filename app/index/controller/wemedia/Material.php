<?php
declare(strict_types=1);

namespace app\index\controller\wemedia;

use app\common\model\WemediaMaterialModel;
use think\facade\View;
use think\Response;

/**
 * 素材管理
 */
class Material extends BaseWemedia
{
    public function index(): string
    {
        View::assign('title', '素材管理');
        return $this->fetchWithLayout('wemedia/material/index');
    }

    public function add(): string
    {
        View::assign('title', '上传素材');
        View::assign('item', null);
        View::assign('itemJson', '{}');
        return $this->fetchWithLayout('wemedia/material/edit');
    }

    public function edit(): string|Response
    {
        $id = (int) request()->get('id', 0);
        if ($id <= 0) {
            return redirect('/index/wemedia/material/index');
        }
        $item = WemediaMaterialModel::where('id', $id)
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->find();
        if (!$item) {
            return redirect('/index/wemedia/material/index');
        }
        View::assign('title', '编辑素材');
        View::assign('item', $item);
        View::assign('itemJson', json_encode($item->toArray()));
        return $this->fetchWithLayout('wemedia/material/edit');
    }

    public function list(): Response
    {
        $keyword = trim((string) request()->get('keyword', ''));
        $type = trim((string) request()->get('type', ''));
        $page = max(1, (int) request()->get('page', 1));
        $limit = min(100, max(10, (int) request()->get('limit', 20)));
        $query = WemediaMaterialModel::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->order('id', 'desc');
        if ($keyword !== '') {
            $query->whereLike('name|remark', '%' . $keyword . '%');
        }
        if ($type !== '') {
            $query->where('type', $type);
        }
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['type_text'] = WemediaMaterialModel::typeText($row['type'] ?? '');
            $row['size_text'] = $this->formatSize((int) ($row['size'] ?? 0));
        }
        unset($row);
        return $this->jsonSuccess('', ['total' => $total, 'list' => $list]);
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1024 / 1024, 1) . ' MB';
    }

    public function save(): Response
    {
        $id = (int) request()->post('id', 0);
        $name = trim((string) request()->post('name', ''));
        $type = trim((string) request()->post('type', 'image'));
        $path = trim((string) request()->post('path', ''));
        $size = (int) request()->post('size', 0);
        $mime = trim((string) request()->post('mime', ''));
        $remark = trim((string) request()->post('remark', ''));
        if ($name === '' && $path === '') {
            return $this->jsonError('请填写名称或先上传文件');
        }
        if ($path === '' && $id <= 0) {
            return $this->jsonError('请先上传文件');
        }
        $now = time();
        $data = [
            'type'        => $type,
            'name'        => $name ?: basename($path),
            'path'        => $path,
            'size'        => $size,
            'mime'        => $mime,
            'remark'      => $remark,
            'update_time' => $now,
        ];
        if ($id > 0) {
            $row = WemediaMaterialModel::where('id', $id)
                ->where('tenant_id', $this->tenantId)
                ->where('user_id', $this->userId)
                ->find();
            if (!$row) {
                return $this->jsonError('记录不存在');
            }
            $row->save($data);
            return $this->jsonSuccess('保存成功', ['id' => $id]);
        }
        $data['tenant_id']   = $this->tenantId;
        $data['user_id']    = $this->userId;
        $data['create_time'] = $now;
        $m = new WemediaMaterialModel();
        $m->save($data);
        return $this->jsonSuccess('添加成功', ['id' => (int) $m->id]);
    }

    public function del(): Response
    {
        $id = (int) (request()->post('id', request()->get('id', 0)));
        if ($id <= 0) {
            return $this->jsonError('参数错误');
        }
        $row = WemediaMaterialModel::where('id', $id)
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->find();
        if (!$row) {
            return $this->jsonError('记录不存在');
        }
        $row->delete();
        return $this->jsonSuccess('删除成功');
    }

    /** 上传文件：接收 path/size/mime，写入一条素材记录并返回 id */
    public function upload(): Response
    {
        $name = trim((string) request()->post('name', ''));
        $type = trim((string) request()->post('type', 'image'));
        $path = trim((string) request()->post('path', ''));
        $size = (int) request()->post('size', 0);
        $mime = trim((string) request()->post('mime', ''));
        if ($path === '') {
            return $this->jsonError('缺少 path');
        }
        $now = time();
        $m = new WemediaMaterialModel();
        $m->save([
            'tenant_id'   => $this->tenantId,
            'user_id'    => $this->userId,
            'type'       => $type,
            'name'       => $name ?: basename($path),
            'path'       => $path,
            'size'       => $size,
            'mime'       => $mime,
            'create_time'=> $now,
            'update_time'=> $now,
        ]);
        return $this->jsonSuccess('上传成功', ['id' => (int) $m->id, 'path' => $path]);
    }
}
