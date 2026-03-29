<?php
declare(strict_types=1);

require __DIR__ . '/OpenClawStorage.php';

function jsonOut(int $status, array $payload): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function ok(array $data = [], string $msg = 'ok'): void
{
    jsonOut(200, ['code' => 1, 'msg' => $msg, 'data' => $data]);
}

function fail(string $msg, int $status = 400, array $data = []): void
{
    jsonOut($status, ['code' => 0, 'msg' => $msg, 'data' => $data]);
}

function rawBody(): string
{
    return (string) file_get_contents('php://input');
}

function jsonBody(): array
{
    $raw = rawBody();
    if ($raw === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function bearerToken(): string
{
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!$h) return '';
    if (stripos($h, 'Bearer ') !== 0) return '';
    return trim(substr($h, 7));
}

$dataDir = getenv('OC_DATA_DIR') ?: '/tmp/openclaw_data';
$apiKey = getenv('OC_API_KEY') ?: '';
$gwToken = '';
if (is_file('/root/.openclaw/openclaw.json')) {
    $gwCfg = json_decode((string) @file_get_contents('/root/.openclaw/openclaw.json'), true);
    if (is_array($gwCfg)) {
        $t = $gwCfg['gateway']['auth']['token'] ?? '';
        if (is_string($t) && $t !== '') $gwToken = $t;
    }
}
$store = new OpenClawStorage($dataDir);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($path === '/ping' || $path === '/api/ping') {
    ok(['pong' => true]);
}

if ($apiKey !== '' || $gwToken !== '') {
    $tok = bearerToken();
    if (!in_array($tok, array_filter([$apiKey, $gwToken]), true)) {
        fail('unauthorized', 401);
    }
}

if ($path === '/api/install' && $method === 'POST') {
    $b = jsonBody();
    $tenantId = (int) ($b['tenant_id'] ?? 0);
    $workspace = (string) ($b['workspace'] ?? '');
    if ($tenantId <= 0) fail('tenant_id required');
    $store->upsertBy('tenants', ['tenant_id' => $tenantId, 'workspace' => $workspace, 'update_time' => time()], ['tenant_id']);
    ok(['tenant_id' => $tenantId, 'workspace' => $workspace]);
}

if ($path === '/api/ingest/summary' && $method === 'POST') {
    $b = jsonBody();
    $tenantId = (int) ($b['tenant_id'] ?? 0);
    $date = (string) ($b['date'] ?? '');
    if ($tenantId <= 0 || $date === '') fail('tenant_id/date required');
    $row = [
        'tenant_id' => $tenantId,
        'date' => $date,
        'orders' => $b['orders'] ?? [],
        'items' => $b['items'] ?? [],
        'create_time' => time(),
    ];
    $store->append('summary_' . $tenantId, $row);
    ok(['saved' => true]);
}

if ($path === '/api/restaurant/reviews' && $method === 'GET') {
    $tenantId = (int) ($_GET['tenant_id'] ?? 0);
    $since = (string) ($_GET['since'] ?? '');
    $until = (string) ($_GET['until'] ?? '');
    if ($tenantId <= 0) fail('tenant_id required');
    $rows = $store->getAll('reviews_' . $tenantId);
    $sinceTs = $since ? strtotime($since . ' 00:00:00') : 0;
    $untilTs = $until ? strtotime($until . ' 23:59:59') : 0;
    $out = [];
    foreach (array_reverse($rows) as $r) {
        if (!is_array($r)) continue;
        $t = (int) ($r['review_time'] ?? $r['time'] ?? 0);
        if ($sinceTs && $t < $sinceTs) continue;
        if ($untilTs && $t > $untilTs) continue;
        $out[] = $r;
        if (count($out) >= 200) break;
    }
    ok(['list' => $out]);
}

if ($path === '/api/restaurant/reviews/reply' && $method === 'POST') {
    $b = jsonBody();
    $tenantId = (int) ($b['tenant_id'] ?? 0);
    $list = $b['list'] ?? [];
    if ($tenantId <= 0 || !is_array($list)) fail('tenant_id/list required');
    $result = [];
    foreach ($list as $it) {
        if (!is_array($it)) continue;
        $platform = (string) ($it['platform'] ?? '');
        $externalId = (string) ($it['external_id'] ?? '');
        $replyText = (string) ($it['reply_text'] ?? '');
        if ($platform === '' || $externalId === '' || $replyText === '') continue;
        $row = [
            'tenant_id' => $tenantId,
            'platform' => $platform,
            'external_id' => $externalId,
            'reply_text' => $replyText,
            'ok' => 1,
            'create_time' => time(),
        ];
        $store->append('replies_' . $tenantId, $row);
        $result[] = ['platform' => $platform, 'external_id' => $externalId, 'ok' => 1];
    }
    ok(['list' => $result]);
}

if ($path === '/api/restaurant/alerts' && $method === 'POST') {
    $b = jsonBody();
    $tenantId = (int) ($b['tenant_id'] ?? 0);
    $type = (string) ($b['type'] ?? '');
    if ($tenantId <= 0 || $type === '') fail('tenant_id/type required');
    $row = $b;
    $row['create_time'] = time();
    $store->append('alerts_' . $tenantId, $row);
    ok(['saved' => true]);
}

if ($path === '/api/restaurant/reviews/seed' && $method === 'POST') {
    $b = jsonBody();
    $tenantId = (int) ($b['tenant_id'] ?? 0);
    $list = $b['list'] ?? [];
    if ($tenantId <= 0 || !is_array($list)) fail('tenant_id/list required');
    foreach ($list as $r) {
        if (!is_array($r)) continue;
        $store->append('reviews_' . $tenantId, $r);
    }
    ok(['count' => count($list)]);
}

fail('not found', 404);
