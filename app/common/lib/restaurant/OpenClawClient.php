<?php
declare(strict_types=1);

namespace app\common\lib\restaurant;

use think\facade\Db;

class OpenClawClient
{
    protected int $tenantId = 0;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    protected function cfg(): array
    {
        $get = function (string $k, string $d = '') {
            $v = Db::name('config')->where('name', $k)->value('value');
            return $v === null ? $d : (string) $v;
        };
        return [
            'enabled' => $get('restaurant_openclaw_enabled', '0') === '1',
            'api_base' => $get('restaurant_openclaw_api_base', ''),
            'api_key' => $get('restaurant_openclaw_api_key', ''),
            'workspace' => $get('restaurant_openclaw_workspace', ''),
        ];
    }

    protected function call(string $path, string $method = 'GET', array $body = []): array
    {
        $cfg = $this->cfg();
        $base = rtrim($cfg['api_base'] ?? '', '/');
        if ($base === '') return ['ok' => false, 'error' => 'api_base empty'];
        $url = $base . '/' . ltrim($path, '/');
        $headers = ['Content-Type: application/json'];
        $key = (string) ($cfg['api_key'] ?? '');
        if ($key !== '') $headers[] = 'Authorization: Bearer ' . $key;
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $headers,
        ];
        if (strtoupper($method) !== 'GET') {
            $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
            $opts[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE);
        }
        curl_setopt_array($ch, $opts);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($err) return ['ok' => false, 'error' => $err];
        $respStr = (string) $resp;
        $data = json_decode($respStr, true);
        if ($code >= 200 && $code < 300) {
            if ($respStr !== '' && $data === null) {
                return ['ok' => false, 'error' => 'invalid json response', 'code' => $code];
            }
            return ['ok' => true, 'data' => $data, 'code' => $code];
        }
        return ['ok' => false, 'error' => substr((string) $resp, 0, 300), 'code' => $code];
    }

    public function ping(): array
    {
        $r = $this->call('/health', 'GET');
        if ($r['ok'] && is_array($r['data']) && (($r['data']['ok'] ?? null) === true)) {
            return $r;
        }
        $r2 = $this->call('/api/ping', 'GET');
        if ($r2['ok'] && is_array($r2['data']) && ((int) ($r2['data']['code'] ?? 0) === 1)) {
            return $r2;
        }
        return ['ok' => false, 'error' => 'OpenClaw ping failed', 'data' => $r2['data'] ?? ($r['data'] ?? null), 'code' => $r2['code'] ?? ($r['code'] ?? 0)];
    }

    public function install(): array
    {
        $cfg = $this->cfg();
        $body = [
            'tenant_id' => $this->tenantId,
            'workspace' => $cfg['workspace'] ?: ('tenant_' . $this->tenantId),
        ];
        return $this->call('/api/install', 'POST', $body);
    }

    public function pushSummary(string $date): array
    {
        $since = strtotime($date . ' 00:00:00');
        $until = strtotime($date . ' 23:59:59');
        $orders = Db::name('restaurant_order')
            ->where('tenant_id', $this->tenantId)
            ->whereBetweenTime('create_time', $since, $until)
            ->field('id,total_amount,status,create_time')
            ->select()
            ->toArray();
        $items = Db::name('restaurant_order_item')
            ->alias('oi')
            ->leftJoin('restaurant_item i', 'i.id = oi.item_id AND i.tenant_id = oi.tenant_id')
            ->where('oi.tenant_id', $this->tenantId)
            ->whereBetweenTime('oi.create_time', $since, $until)
            ->field('oi.item_id, IFNULL(i.name, \'\') as name, SUM(oi.quantity) as qty, SUM(oi.amount) as amount')
            ->group('oi.item_id')
            ->order('amount desc')
            ->limit(100)
            ->select()
            ->toArray();
        $data = [
            'tenant_id' => $this->tenantId,
            'date' => $date,
            'orders' => $orders,
            'items' => $items,
        ];
        return $this->call('/api/ingest/summary', 'POST', $data);
    }

    public function pullReviews(string $sinceDate, string $untilDate): array
    {
        $cfg = $this->cfg();
        if (empty($cfg['enabled'])) return ['ok' => false, 'error' => 'OpenClaw disabled'];
        $q = http_build_query([
            'tenant_id' => $this->tenantId,
            'since' => $sinceDate,
            'until' => $untilDate,
            'workspace' => $cfg['workspace'] ?: ('tenant_' . $this->tenantId),
        ]);
        return $this->call('/api/restaurant/reviews?' . $q, 'GET');
    }

    public function probeRestaurantApi(): array
    {
        $today = date('Y-m-d');
        $r = $this->pullReviews($today, $today);
        if ($r['ok'] && is_array($r['data']) && ((int) ($r['data']['code'] ?? 0) === 1)) {
            return $r;
        }
        return ['ok' => false, 'error' => 'restaurant api not available', 'data' => $r['data'] ?? null, 'code' => $r['code'] ?? 0];
    }

    public function replyReviews(array $list): array
    {
        $cfg = $this->cfg();
        if (empty($cfg['enabled'])) return ['ok' => false, 'error' => 'OpenClaw disabled'];
        $body = [
            'tenant_id' => $this->tenantId,
            'workspace' => $cfg['workspace'] ?: ('tenant_' . $this->tenantId),
            'list' => $list,
        ];
        return $this->call('/api/restaurant/reviews/reply', 'POST', $body);
    }

    public function pushAlert(array $payload): array
    {
        $cfg = $this->cfg();
        if (empty($cfg['enabled'])) return ['ok' => false, 'error' => 'OpenClaw disabled'];
        $body = array_merge([
            'tenant_id' => $this->tenantId,
            'workspace' => $cfg['workspace'] ?: ('tenant_' . $this->tenantId),
        ], $payload);
        return $this->call('/api/restaurant/alerts', 'POST', $body);
    }
}
