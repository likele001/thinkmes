<?php
declare(strict_types=1);

namespace app\api\controller\restaurant;

use app\common\controller\BaseController;
use app\common\lib\restaurant\ReviewAnalyzer;
use think\facade\Db;
use think\Response;

class OpenClaw extends BaseController
{
    public function webhook(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('非法请求');
        }
        $secret = (string) Db::name('config')->where('name', 'restaurant_openclaw_webhook_secret')->value('value');
        if ($secret === '') {
            return $this->error('未配置 webhook secret');
        }
        $sig = (string) $this->request->header('X-OpenClaw-Signature', '');
        $raw = (string) file_get_contents('php://input');
        $calc = hash_hmac('sha256', $raw, $secret);
        if ($sig === '' || !hash_equals($calc, $sig)) {
            return $this->error('签名错误');
        }
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return $this->error('参数错误');
        }
        $tenantId = (int) ($payload['tenant_id'] ?? 0);
        $event = (string) ($payload['event'] ?? '');
        $data = $payload['data'] ?? null;
        if ($tenantId <= 0 || $event === '' || !is_array($data)) {
            return $this->error('参数错误');
        }
        if ($event === 'reviews.batch') {
            $list = $data['list'] ?? [];
            if (!is_array($list)) $list = [];
            $count = $this->upsertReviews($tenantId, $list);
            return $this->success('ok', ['count' => $count]);
        }
        return $this->success('ignored');
    }

    private function upsertReviews(int $tenantId, array $list): int
    {
        $now = time();
        $threshold = (int) Db::name('config')->where('name', 'restaurant_review_bad_threshold')->value('value');
        if ($threshold <= 0) $threshold = 3;
        $alertEnabled = (string) Db::name('config')->where('name', 'restaurant_review_alert_enabled')->value('value');
        $count = 0;
        foreach ($list as $r) {
            if (!is_array($r)) continue;
            $platform = trim((string) ($r['platform'] ?? ''));
            $externalId = trim((string) ($r['external_id'] ?? $r['id'] ?? ''));
            if ($platform === '' || $externalId === '') continue;
            $storeId = (int) ($r['store_id'] ?? 0);
            $content = (string) ($r['content'] ?? '');
            $rating = (int) ($r['rating'] ?? 0);
            $analysis = ReviewAnalyzer::analyze($tenantId, $content, $rating);
            $data = [
                'tenant_id' => $tenantId,
                'store_id' => $storeId,
                'platform' => $platform,
                'external_id' => $externalId,
                'rating' => $rating,
                'content' => $content,
                'images' => json_encode($r['images'] ?? [], JSON_UNESCAPED_UNICODE),
                'review_time' => (int) ($r['review_time'] ?? $r['time'] ?? $now),
                'keywords' => (string) ($r['keywords'] ?? '') ?: (string) ($analysis['keywords'] ?? ''),
                'sentiment' => (int) (($r['sentiment'] ?? null) !== null ? (int) $r['sentiment'] : (int) ($analysis['sentiment'] ?? 0)),
                'suggest_reply' => (string) ($r['suggest_reply'] ?? $r['reply_suggest'] ?? ''),
                'raw' => json_encode($r, JSON_UNESCAPED_UNICODE),
                'update_time' => $now,
            ];
            $exist = Db::name('restaurant_review')->where('tenant_id', $tenantId)->where('platform', $platform)->where('external_id', $externalId)->find();
            if ($exist) {
                Db::name('restaurant_review')->where('id', (int) $exist['id'])->update($data);
            } else {
                $data['create_time'] = $now;
                Db::name('restaurant_review')->insert($data);
            }
            if (($alertEnabled === '' || $alertEnabled === '1') && $rating > 0 && $rating <= $threshold) {
                $this->createAlertIfNeeded($tenantId, $storeId, $platform, $externalId, $rating, $content, (int) $data['review_time']);
            }
            $count++;
        }
        return $count;
    }

    private function createAlertIfNeeded(int $tenantId, int $storeId, string $platform, string $externalId, int $rating, string $content, int $reviewTime): void
    {
        $exist = Db::name('restaurant_review_alert')
            ->where('tenant_id', $tenantId)
            ->where('alert_type', 'bad_review')
            ->where('platform', $platform)
            ->where('external_id', $externalId)
            ->find();
        if ($exist) return;
        Db::name('restaurant_review_alert')->insert([
            'tenant_id' => $tenantId,
            'store_id' => $storeId,
            'platform' => $platform,
            'external_id' => $externalId,
            'alert_type' => 'bad_review',
            'rating' => $rating,
            'content' => mb_substr(trim($content), 0, 500),
            'review_time' => $reviewTime,
            'status' => 0,
            'create_time' => time(),
        ]);
    }
}
