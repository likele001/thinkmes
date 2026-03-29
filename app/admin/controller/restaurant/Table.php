<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\admin\model\restaurant\AreaModel;
use app\admin\model\restaurant\StoreModel;
use app\admin\model\restaurant\TableModel;
use app\common\lib\QrCodeService;
use app\common\lib\WxaCodeService;
use think\facade\View;
use think\Response;

class Table extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->getTenantId();
            View::assign('storeList', StoreModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
            View::assign('areaList', AreaModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'desc')->order('id', 'desc')->select());
            View::assign('title', '桌台管理');
            return $this->fetchWithLayout('restaurant/table/index');
        }

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));

        $tenantId = $this->getTenantId();
        $query = TableModel::with(['store', 'area'])->order('id', 'desc');
        if ($tenantId > 0) {
            $query->where('tenant_id', $tenantId);
        } else {
            $tp = (int) $this->request->get('tenant_id', 0);
            if ($tp > 0) {
                $query->where('tenant_id', $tp);
            }
        }

        $storeId = (int) $this->request->get('store_id', 0);
        if ($storeId > 0) {
            $query->where('store_id', $storeId);
        }
        $areaId = (int) $this->request->get('area_id', 0);
        if ($areaId > 0) {
            $query->where('area_id', $areaId);
        }
        $name = trim((string) $this->request->get('name', ''));
        if ($name !== '') {
            $query->where('name', 'like', '%' . $name . '%');
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$item) {
            $item['store_name'] = $item['store']['name'] ?? '-';
            $item['area_name'] = $item['area']['name'] ?? '-';
        }
        unset($item);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        $tenantId = $this->getTenantId();
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['name']) || empty($params['store_id']) || empty($params['area_id'])) {
                return $this->error('请填写桌台名称并选择门店/区域');
            }
            $now = time();
            $params['tenant_id'] = $tenantId;
            $params['store_id'] = (int) $params['store_id'];
            $params['area_id'] = (int) $params['area_id'];
            $params['code'] = trim((string) ($params['code'] ?? ''));
            $params['seats'] = isset($params['seats']) ? (int) $params['seats'] : 0;
            $params['status'] = isset($params['status']) ? (int) $params['status'] : 1;
            $params['state'] = isset($params['state']) ? (int) $params['state'] : 0;
            $params['qr_token'] = TableModel::generateToken();
            $params['create_time'] = $now;
            $params['update_time'] = $now;
            try {
                $row = TableModel::create($params);
                return $this->success('添加成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('添加失败：' . $e->getMessage());
            }
        }
        View::assign('storeList', StoreModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
        View::assign('areaList', AreaModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'desc')->order('id', 'desc')->select());
        View::assign('title', '添加桌台');
        return $this->fetchWithLayout('restaurant/table/add');
    }

    public function edit(): string|Response
    {
        $ids = $this->request->param('ids') ?: $this->request->param('id');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = TableModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if (empty($params) || empty($params['name']) || empty($params['store_id']) || empty($params['area_id'])) {
                return $this->error('请填写桌台名称并选择门店/区域');
            }
            $params['store_id'] = (int) $params['store_id'];
            $params['area_id'] = (int) $params['area_id'];
            $params['code'] = trim((string) ($params['code'] ?? ''));
            $params['seats'] = isset($params['seats']) ? (int) $params['seats'] : (int) $row->seats;
            $params['status'] = isset($params['status']) ? (int) $params['status'] : (int) $row->status;
            $params['state'] = isset($params['state']) ? (int) $params['state'] : (int) $row->state;
            $params['update_time'] = time();
            try {
                $row->save($params);
                return $this->success('编辑成功', ['id' => $row->id]);
            } catch (\Throwable $e) {
                return $this->error('编辑失败：' . $e->getMessage());
            }
        }
        View::assign('storeList', StoreModel::where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
        View::assign('areaList', AreaModel::where('tenant_id', $tenantId)->where('status', 1)->order('sort', 'desc')->order('id', 'desc')->select());
        View::assign('row', $row);
        View::assign('title', '编辑桌台');
        return $this->fetchWithLayout('restaurant/table/edit');
    }

    public function resetToken(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $id = (int) $this->request->post('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = TableModel::where('tenant_id', $tenantId)->find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        try {
            $row->save(['qr_token' => TableModel::generateToken(), 'update_time' => time()]);
            return $this->success('重置成功', ['qr_token' => $row->qr_token]);
        } catch (\Throwable $e) {
            return $this->error('重置失败：' . $e->getMessage());
        }
    }

    public function del(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $ids = $this->request->post('ids');
        if (empty($ids)) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $idsArr = is_array($ids) ? $ids : explode(',', (string) $ids);
        $count = 0;
        foreach ($idsArr as $id) {
            $row = TableModel::where('tenant_id', $tenantId)->find($id);
            if ($row) {
                $row->delete();
                $count++;
            }
        }
        return $this->success('删除成功', ['count' => $count]);
    }

    public function qrcode(): string|Response
    {
        $ids = (int) ($this->request->param('ids') ?: $this->request->param('id'));
        if ($ids <= 0) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = TableModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $token = (string) $row->qr_token;
        if ($token === '') {
            $token = TableModel::generateToken();
            $row->save(['qr_token' => $token, 'update_time' => time()]);
        }
        $url = rtrim($this->request->domain(), '/') . '/restaurant/index.html?tenant_id=' . $tenantId . '&token=' . urlencode($token);
        $rel = QrCodeService::generateWithCustomName($url, $tenantId, 'restaurant_table_' . $row->id . '.png', 360);
        $imgUrl = QrCodeService::pathToUrl($rel);
        View::assign('title', '桌台二维码');
        View::assign('row', $row);
        View::assign('qrcode_url', $imgUrl);
        View::assign('h5_url', $url);
        return $this->fetchWithLayout('restaurant/table/qrcode');
    }

    public function wxacode(): string|Response
    {
        $ids = (int) ($this->request->param('ids') ?: $this->request->param('id'));
        if ($ids <= 0) {
            return $this->error('参数错误');
        }
        $tenantId = $this->getTenantId();
        $row = TableModel::where('tenant_id', $tenantId)->find($ids);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $token = (string) $row->qr_token;
        if ($token === '') {
            $token = TableModel::generateToken();
            $row->save(['qr_token' => $token, 'update_time' => time()]);
        }
        $appid = (string) \think\facade\Db::name('config')->where('name', 'restaurant_wxa_appid')->value('value');
        $secret = (string) \think\facade\Db::name('config')->where('name', 'restaurant_wxa_secret')->value('value');
        $page = (string) \think\facade\Db::name('config')->where('name', 'restaurant_wxa_page')->value('value');
        if ($appid === '' || $secret === '') {
            return $this->error('请先在餐饮小程序配置里填写 AppID 与 Secret');
        }
        $access = WxaCodeService::genAccessToken($appid, $secret);
        if ($access === '') {
            return $this->error('获取 access_token 失败');
        }
        $scene = 't' . $tenantId . '|tk' . $token;
        $payload = [
            'scene' => $scene,
            'page' => $page ?: 'pages/menu/index',
            'width' => 360,
            'env_version' => 'release',
        ];
        $bin = WxaCodeService::genUnlimited($access, $payload);
        if ($bin === '') {
            return $this->error('生成失败');
        }
        $rel = WxaCodeService::savePng($bin, $tenantId, 'restaurant_wxacode_table_' . $row->id . '.png');
        $imgUrl = WxaCodeService::pathToUrl($rel);
        View::assign('title', '桌台小程序码');
        View::assign('row', $row);
        View::assign('wxacode_url', $imgUrl);
        View::assign('scene', $scene);
        View::assign('page', $payload['page']);
        return $this->fetchWithLayout('restaurant/table/wxacode');
    }
}
