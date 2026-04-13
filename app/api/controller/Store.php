<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\lib\payment\PaymentService;
use app\common\lib\Upload;
use think\facade\Db;
use think\Response;

class Store extends BaseController
{
    private function normalizePluginRow(array $row): array
    {
        $desc = (string) ($row['description'] ?? '');
        $trim = ltrim($desc);
        if ($trim !== '' && ($trim[0] ?? '') === '{') {
            $decoded = json_decode($desc, true);
            if (is_array($decoded)) {
                $summary = (string) ($decoded['summary'] ?? '');
                $detail = (string) ($decoded['detail'] ?? '');
                $shots = $decoded['screenshots'] ?? [];
                if (!is_array($shots)) {
                    $shots = [];
                }
                $shots = array_values(array_filter(array_map(function ($v) {
                    $s = trim((string) $v);
                    return $s !== '' ? $s : null;
                }, $shots)));

                $summaryOut = trim($summary);
                if ($summaryOut === '' && trim($detail) !== '') {
                    $summaryOut = trim(preg_replace('/\s+/', ' ', $detail));
                }
                if ($summaryOut !== '' && strlen($summaryOut) > 160) {
                    $summaryOut = substr($summaryOut, 0, 160) . '...';
                }
                $row['description'] = $summaryOut;
                $row['detail'] = $detail;
                $row['screenshots'] = $shots;
                return $row;
            }
        }

        $row['detail'] = '';
        $row['screenshots'] = [];
        return $row;
    }

    private function normalizePluginList(array $result): array
    {
        if (!isset($result['data']) || !is_array($result['data'])) {
            return $result;
        }
        $result['data'] = array_map(function ($row) {
            return is_array($row) ? $this->normalizePluginRow($row) : $row;
        }, $result['data']);
        return $result;
    }

    private function getTenantId(): int
    {
        return (int) ($this->request->tenantId ?? 0);
    }

    private function getUserId(): int
    {
        return (int) ($this->request->userId ?? 0);
    }

    private function getDeveloperId(): int
    {
        return (int) ($this->request->developerId ?? 0);
    }

    private function getDeveloperName(): string
    {
        $info = $this->request->developerInfo ?? [];
        if (is_array($info)) {
            $name = trim((string) ($info['name'] ?? $info['account'] ?? ''));
            if ($name !== '') {
                return $name;
            }
        }
        $id = $this->getDeveloperId();
        return $id > 0 ? ('dev' . $id) : 'dev';
    }

    public function plugins(): Response
    {
        $keyword = trim((string) $this->request->get('keyword', ''));
        $category = trim((string) $this->request->get('category', ''));
        $sort = trim((string) $this->request->get('sort', 'default'));
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(60, (int) $this->request->get('limit', 20)));

        $query = Db::name('market_plugin')->where('status', 'active');
        
        // 使用参数化查询防止SQL注入
        if ($keyword !== '') {
            $searchKeyword = '%' . $keyword . '%';
            $query->where(function($q) use ($searchKeyword) {
                $q->where('title', 'like', $searchKeyword)
                  ->whereOr('name', 'like', $searchKeyword)
                  ->whereOr('keywords', 'like', $searchKeyword)
                  ->whereOr('description', 'like', $searchKeyword);
            });
        }
        
        if ($category !== '' && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($sort === 'downloads') {
            $query->order('downloads', 'desc')->order('rating', 'desc');
        } elseif ($sort === 'rating') {
            $query->order('rating', 'desc')->order('downloads', 'desc');
        } elseif ($sort === 'new') {
            $query->order('released_at', 'desc')->order('updated_at', 'desc');
        } else {
            $query->order('is_featured', 'desc')->order('rating', 'desc')->order('downloads', 'desc');
        }

        $result = $query->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        return $this->success('', $this->normalizePluginList($result));
    }

    public function detail(): Response
    {
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }

        $plugin = Db::name('market_plugin')->where('id', $id)->where('status', 'active')->find();
        if (!$plugin) {
            return $this->error('应用不存在');
        }
        if (is_array($plugin)) {
            $plugin = $this->normalizePluginRow($plugin);
        }

        $latest = Db::name('market_plugin_version')
            ->where('plugin_id', $id)
            ->order('released_at', 'desc')
            ->find();

        $versions = Db::name('market_plugin_version')
            ->where('plugin_id', $id)
            ->order('released_at', 'desc')
            ->limit(30)
            ->select()
            ->toArray();

        return $this->success('', [
            'plugin' => $plugin,
            'latest_version' => $latest ?: null,
            'versions' => $versions,
        ]);
    }

    public function paymentMethods(): Response
    {
        $tenantId = $this->getTenantId();
        $list = Db::name('payment_gateway')
            ->where('enabled', 1);
        if ($tenantId > 0) {
            $list->whereIn('tenant_id', [0, $tenantId])->order('tenant_id', 'desc');
        } else {
            $list->where('tenant_id', 0);
        }
        $list = $list
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->field('id,name,code')
            ->select()
            ->toArray();
        return $this->success('', ['list' => $list]);
    }

    public function publish(): Response
    {
        $developerId = $this->getDeveloperId();
        if ($developerId <= 0) {
            return $this->error('请先登录开发者中心');
        }
        if (!$this->request->isPost()) {
            return $this->error('请求方式错误');
        }

        $name = strtolower(trim((string) $this->request->post('name', '')));
        $title = trim((string) $this->request->post('title', ''));
        $description = trim((string) $this->request->post('description', ''));
        $detail = trim((string) $this->request->post('detail', ''));
        $screenshotsRaw = $this->request->post('screenshots', '');
        $category = trim((string) $this->request->post('category', 'other'));
        $version = trim((string) $this->request->post('version', '1.0.0'));
        $price = (float) $this->request->post('price', 0);
        $downloadUrl = trim((string) $this->request->post('download_url', ''));
        $screenshot = trim((string) $this->request->post('screenshot', ''));
        $homepage = trim((string) $this->request->post('homepage', ''));
        $keywords = trim((string) $this->request->post('keywords', ''));

        if ($name === '' || !preg_match('/^[a-z][a-z0-9_]{1,49}$/', $name)) {
            return $this->error('插件标识不合法（需小写字母开头，仅含字母数字下划线）');
        }
        if ($title === '') {
            return $this->error('标题不能为空');
        }
        if ($downloadUrl === '') {
            return $this->error('请先上传应用包（zip）');
        }
        if ($price < 0) {
            return $this->error('价格不合法');
        }
        if ($category === '') {
            $category = 'other';
        }

        $screenshots = [];
        if (is_array($screenshotsRaw)) {
            $screenshots = $screenshotsRaw;
        } elseif (is_string($screenshotsRaw) && trim($screenshotsRaw) !== '') {
            $decoded = json_decode($screenshotsRaw, true);
            if (is_array($decoded)) {
                $screenshots = $decoded;
            }
        }
        if (!is_array($screenshots)) {
            $screenshots = [];
        }
        $screenshots = array_values(array_filter(array_map(function ($v) {
            $s = trim((string) $v);
            return $s !== '' ? $s : null;
        }, $screenshots)));
        if (count($screenshots) > 12) {
            $screenshots = array_slice($screenshots, 0, 12);
        }

        $cover = $screenshot;
        if ($cover === '' && $screenshots) {
            $cover = (string) $screenshots[0];
        }
        if ($cover !== '' && strlen($cover) > 255) {
            $cover = substr($cover, 0, 255);
        }

        $descPayload = json_encode([
            'summary' => $description,
            'detail' => $detail,
            'screenshots' => $screenshots,
        ], JSON_UNESCAPED_UNICODE);
        if (!is_string($descPayload) || $descPayload === '') {
            $descPayload = $description;
        }

        $now = time();
        $fileSize = 0;
        $urlPath = parse_url($downloadUrl, PHP_URL_PATH);
        if (is_string($urlPath) && $urlPath !== '' && str_starts_with($urlPath, '/uploads/')) {
            $full = root_path() . 'public' . $urlPath;
            if (is_file($full)) {
                $fileSize = (int) filesize($full);
            }
        }

        $plugin = Db::name('market_plugin')->where('name', $name)->find();
        if ($plugin) {
            $owner = Db::name('market_plugin_owner')->where('plugin_id', (int) $plugin['id'])->find();
            if (!$owner || (int) ($owner['developer_id'] ?? 0) !== $developerId) {
                return $this->error('该插件标识已被占用');
            }
            Db::name('market_plugin')->where('id', (int) $plugin['id'])->update([
                'title' => $title,
                'description' => $descPayload,
                'author' => $this->getDeveloperName(),
                'version' => $version,
                'category' => $category,
                'homepage' => $homepage,
                'screenshot' => $cover,
                'download_url' => $downloadUrl,
                'file_size' => $fileSize,
                'price' => $price,
                'keywords' => $keywords,
                'status' => 'active',
                'released_at' => $now,
                'updated_at' => $now,
                'update_time' => $now,
            ]);
            $pluginId = (int) $plugin['id'];
        } else {
            $pluginId = (int) Db::name('market_plugin')->insertGetId([
                'name' => $name,
                'title' => $title,
                'description' => $descPayload,
                'author' => $this->getDeveloperName(),
                'version' => $version,
                'category' => $category,
                'homepage' => $homepage,
                'screenshot' => $cover,
                'download_url' => $downloadUrl,
                'file_size' => $fileSize,
                'price' => $price,
                'min_version' => '',
                'max_version' => '',
                'require_php' => '7.4',
                'dependencies' => '[]',
                'keywords' => $keywords,
                'downloads' => 0,
                'rating' => 0,
                'rating_count' => 0,
                'is_official' => 0,
                'is_featured' => 0,
                'status' => 'active',
                'released_at' => $now,
                'updated_at' => $now,
                'create_time' => $now,
                'update_time' => $now,
            ]);
            Db::name('market_plugin_owner')->insert([
                'plugin_id' => $pluginId,
                'developer_id' => $developerId,
                'create_time' => $now,
            ]);
        }

        $existsVersion = Db::name('market_plugin_version')
            ->where('plugin_id', $pluginId)
            ->where('version', $version)
            ->find();
        if ($existsVersion) {
            Db::name('market_plugin_version')->where('id', (int) $existsVersion['id'])->update([
                'download_url' => $downloadUrl,
                'file_size' => $fileSize,
                'min_version' => '',
                'max_version' => '',
                'is_stable' => 1,
                'released_at' => $now,
            ]);
        } else {
            Db::name('market_plugin_version')->insert([
                'plugin_id' => $pluginId,
                'version' => $version,
                'changelog' => '',
                'download_url' => $downloadUrl,
                'file_size' => $fileSize,
                'min_version' => '',
                'max_version' => '',
                'is_stable' => 1,
                'downloads' => 0,
                'released_at' => $now,
                'create_time' => $now,
            ]);
        }

        return $this->success('发布成功', ['plugin_id' => $pluginId]);
    }

    public function myPlugins(): Response
    {
        $developerId = $this->getDeveloperId();
        if ($developerId <= 0) {
            return $this->error('请先登录开发者中心');
        }
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(60, (int) $this->request->get('limit', 20)));
        $q = Db::name('market_plugin_owner')->alias('o')
            ->join('market_plugin p', 'p.id = o.plugin_id')
            ->where('o.developer_id', $developerId)
            ->order('p.id', 'desc')
            ->field('p.*');
        $result = $q->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        return $this->success('', $this->normalizePluginList($result));
    }

    public function upload(): Response
    {
        $developerId = $this->getDeveloperId();
        if ($developerId <= 0) {
            return $this->error('请先登录开发者中心', 401);
        }
        
        // 加强文件上传安全验证
        $upload = new Upload();
        $result = $upload->handle($this->request, 0);
        
        if (is_array($result) && isset($result['url'])) {
            // 验证上传的文件
            $filePath = root_path() . 'public' . parse_url($result['url'], PHP_URL_PATH);
            if (is_file($filePath)) {
                // 验证文件扩展名
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if ($ext !== 'zip') {
                    @unlink($filePath);
                    return $this->error('仅支持 .zip 格式文件');
                }
                
                // 验证MIME类型
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $filePath);
                finfo_close($finfo);
                
                if ($mimeType !== 'application/zip' && $mimeType !== 'application/x-zip-compressed') {
                    @unlink($filePath);
                    return $this->error('文件类型错误，请上传有效的ZIP文件');
                }
                
                // 验证ZIP文件结构
                $zip = new \ZipArchive();
                if ($zip->open($filePath) !== true) {
                    @unlink($filePath);
                    return $this->error('ZIP文件已损坏或无法打开');
                }
                $zip->close();
            }
            
            return $this->success('上传成功', $result);
        }
        return $this->error(is_string($result) ? $result : '上传失败');
    }

    public function myOrders(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0 || $userId <= 0) {
            return $this->error('请先登录');
        }
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(60, (int) $this->request->get('limit', 20)));
        $q = Db::name('market_plugin_order')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->order('id', 'desc');
        $result = $q->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        return $this->success('', $result);
    }

    public function createOrder(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0 || $userId <= 0) {
            return $this->error('请先登录');
        }

        $pluginId = (int) $this->request->post('plugin_id', 0);
        if ($pluginId <= 0) {
            return $this->error('参数错误');
        }

        $plugin = Db::name('market_plugin')->where('id', $pluginId)->where('status', 'active')->find();
        if (!$plugin) {
            return $this->error('应用不存在');
        }

        $price = (float) ($plugin['price'] ?? 0);
        if ($price > 0) {
            $paid = Db::name('market_plugin_order')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('plugin_id', $pluginId)
                ->where('status', 1)
                ->find();
            if ($paid) {
                return $this->success('已购买', [
                    'order_no' => (string) ($paid['order_no'] ?? ''),
                    'amount' => $price,
                    'status' => 1,
                    'plugin' => [
                        'id' => (int) $pluginId,
                        'name' => (string) ($plugin['name'] ?? ''),
                        'title' => (string) ($plugin['title'] ?? ''),
                    ],
                ]);
            }
        }
        $orderNo = date('YmdHis') . str_pad((string) $tenantId, 4, '0', STR_PAD_LEFT) . str_pad((string) $userId, 6, '0', STR_PAD_LEFT) . mt_rand(100, 999);
        $now = time();

        Db::name('market_plugin_order')->insert([
            'order_no' => $orderNo,
            'plugin_id' => $pluginId,
            'plugin_name' => (string) ($plugin['name'] ?? ''),
            'plugin_title' => (string) ($plugin['title'] ?? ''),
            'amount' => $price,
            'status' => $price <= 0 ? 1 : 0,
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'pay_time' => $price <= 0 ? $now : 0,
            'create_time' => $now,
            'update_time' => $now,
        ]);

        return $this->success($price <= 0 ? '购买成功' : '订单创建成功', [
            'order_no' => $orderNo,
            'amount' => $price,
            'status' => $price <= 0 ? 1 : 0,
            'plugin' => [
                'id' => (int) $pluginId,
                'name' => (string) ($plugin['name'] ?? ''),
                'title' => (string) ($plugin['title'] ?? ''),
            ],
        ]);
    }

    public function pay(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0 || $userId <= 0) {
            return $this->error('请先登录');
        }
        if (!$this->request->isPost()) {
            return $this->error('请求方式错误');
        }

        $orderNo = trim((string) $this->request->post('order_no', ''));
        if ($orderNo === '') {
            return $this->error('订单号不能为空');
        }
        $gatewayId = (int) $this->request->post('gateway_id', 0);
        if ($gatewayId <= 0) {
            return $this->error('请选择支付方式');
        }

        $order = Db::name('market_plugin_order')
            ->where('order_no', $orderNo)
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->find();
        if (!$order) {
            return $this->error('订单不存在');
        }
        if ((int) ($order['status'] ?? 0) === 1) {
            return $this->success('已支付', ['order_no' => $orderNo, 'status' => 1]);
        }
        $amount = (float) ($order['amount'] ?? 0);
        if ($amount <= 0) {
            Db::name('market_plugin_order')->where('id', (int) $order['id'])->update([
                'status' => 1,
                'pay_time' => time(),
                'update_time' => time(),
            ]);
            return $this->success('支付成功', ['order_no' => $orderNo, 'status' => 1]);
        }

        $notifyUrl = $this->request->domain() . '/api/payment/notify/' . $gatewayId;
        $returnUrl = $this->request->domain() . '/index/store';
        $ret = PaymentService::create(
            $gatewayId,
            $orderNo,
            $amount,
            '购买应用：' . (string) ($order['plugin_title'] ?? $orderNo),
            $notifyUrl,
            $returnUrl,
            $tenantId
        );
        if (!empty($ret['error'])) {
            return $this->error((string) $ret['error']);
        }
        return $this->success('发起支付成功', [
            'order_no' => $orderNo,
            'pay_url' => $ret['pay_url'] ?? '',
            'form_html' => $ret['form_html'] ?? '',
        ]);
    }

    public function orderStatus(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0 || $userId <= 0) {
            return $this->error('请先登录');
        }

        $orderNo = trim((string) $this->request->get('order_no', ''));
        if ($orderNo === '') {
            return $this->error('订单号不能为空');
        }

        $order = Db::name('market_plugin_order')
            ->where('order_no', $orderNo)
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->find();
        if (!$order) {
            return $this->error('订单不存在');
        }

        $po = Db::name('payment_order')->where('order_no', $orderNo)->find();
        if ($po && (int) ($po['status'] ?? 0) === 1 && (int) ($order['status'] ?? 0) !== 1) {
            Db::name('market_plugin_order')->where('id', (int) $order['id'])->update([
                'status' => 1,
                'pay_time' => (int) ($po['pay_time'] ?? time()),
                'update_time' => time(),
            ]);
            $order['status'] = 1;
            $order['pay_time'] = (int) ($po['pay_time'] ?? time());
        }

        return $this->success('', [
            'order_no' => $orderNo,
            'status' => (int) ($order['status'] ?? 0),
            'amount' => (float) ($order['amount'] ?? 0),
            'pay_time' => (int) ($order['pay_time'] ?? 0),
            'payment' => $po ? [
                'status' => (int) ($po['status'] ?? 0),
                'gateway_code' => (string) ($po['gateway_code'] ?? ''),
                'third_order_id' => (string) ($po['third_order_id'] ?? ''),
                'pay_time' => (int) ($po['pay_time'] ?? 0),
            ] : null,
        ]);
    }

    public function download(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();
        if ($tenantId <= 0 || $userId <= 0) {
            return $this->error('请先登录');
        }

        $pluginId = (int) $this->request->get('plugin_id', 0);
        $version = trim((string) $this->request->get('version', ''));
        if ($pluginId <= 0 || $version === '') {
            return $this->error('参数错误');
        }

        $plugin = Db::name('market_plugin')->where('id', $pluginId)->where('status', 'active')->find();
        if (!$plugin) {
            return $this->error('应用不存在');
        }
        $price = (float) ($plugin['price'] ?? 0);
        if ($price > 0) {
            $paid = Db::name('market_plugin_order')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('plugin_id', $pluginId)
                ->where('status', 1)
                ->find();
            if (!$paid) {
                return $this->error('请先购买');
            }
        }

        $ver = Db::name('market_plugin_version')
            ->where('plugin_id', $pluginId)
            ->where('version', $version)
            ->find();
        if (!$ver) {
            return $this->error('版本不存在');
        }
        $downloadUrl = trim((string) ($ver['download_url'] ?? ''));
        $path = '';
        $urlPath = parse_url($downloadUrl, PHP_URL_PATH);
        if (is_string($urlPath) && $urlPath !== '' && str_starts_with($urlPath, '/uploads/')) {
            $path = root_path() . 'public' . $urlPath;
        }
        if ($path === '' || !is_file($path)) {
            return $this->error('文件不存在');
        }
        $content = file_get_contents($path);
        if ($content === false) {
            return $this->error('读取失败');
        }
        Db::name('market_plugin')->where('id', $pluginId)->inc('downloads', 1)->update();
        Db::name('market_plugin_version')->where('id', (int) $ver['id'])->inc('downloads', 1)->update();
        $filename = (string) ($plugin['name'] ?? 'plugin') . '-' . $version . '.zip';
        return response($content, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
