<?php
declare(strict_types=1);

namespace app\api\controller\restaurant;

use app\common\controller\BaseController;
use app\admin\model\restaurant\TableModel;
use app\common\lib\WxaCodeService;
use think\facade\Db;
use think\Response;

class Wxa extends BaseController
{
    protected function tenantId(): int
    {
        $tenantId = (int) ($this->request->tenantId ?? 0);
        if ($tenantId <= 0) {
            $tenantId = (int) $this->request->param('tenant_id', 0);
        }
        return $tenantId;
    }

    public function getConfig(): Response
    {
        $appid = (string) Db::name('config')->where('name', 'restaurant_wxa_appid')->value('value');
        $page = (string) Db::name('config')->where('name', 'restaurant_wxa_page')->value('value');
        return $this->success('', [
            'appid' => $appid,
            'page' => $page ?: 'pages/menu/index',
        ]);
    }

    public function genTableCode(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $tenantId = $this->tenantId();
        if ($tenantId <= 0) {
            return $this->error('未识别租户');
        }
        $tableId = (int) $this->request->post('table_id', 0);
        $token = trim((string) $this->request->post('token', ''));
        if ($tableId <= 0 && $token === '') {
            return $this->error('参数错误');
        }
        $row = $tableId > 0
            ? TableModel::where('tenant_id', $tenantId)->find($tableId)
            : TableModel::where('tenant_id', $tenantId)->where('qr_token', $token)->find();
        if (!$row) {
            return $this->error('桌台不存在');
        }
        $token = (string) $row->qr_token;
        if ($token === '') {
            $token = TableModel::generateToken();
            $row->save(['qr_token' => $token, 'update_time' => time()]);
        }
        $appid = (string) Db::name('config')->where('name', 'restaurant_wxa_appid')->value('value');
        $secret = (string) Db::name('config')->where('name', 'restaurant_wxa_secret')->value('value');
        $page = (string) Db::name('config')->where('name', 'restaurant_wxa_page')->value('value');
        if ($appid === '' || $secret === '') {
            return $this->error('未配置 AppID/Secret');
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
        $url = WxaCodeService::pathToUrl($rel);
        return $this->success('', [
            'image_url' => $url,
            'relative_path' => $rel,
            'scene' => $scene,
            'page' => $payload['page'],
        ]);
    }
}

