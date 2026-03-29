<?php
declare(strict_types=1);

namespace app\admin\controller\restaurant;

use app\admin\controller\Backend;
use app\admin\model\restaurant\ReviewAlertModel;
use app\admin\model\restaurant\ReviewModel;
use app\admin\model\restaurant\ReviewReplyTemplateModel;
use app\common\lib\restaurant\OpenClawClient;
use app\common\lib\restaurant\ReviewAnalyzer;
use think\facade\Db;
use think\facade\View;
use think\Response;

class Review extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            $tenantId = $this->getTenantId();
            View::assign('storeList', Db::name('restaurant_store')->where('tenant_id', $tenantId)->where('status', 1)->order('id', 'desc')->select());
            View::assign('title', '评价管理');
            return $this->fetchWithLayout('restaurant/review/index');
        }
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = $this->request->get('offset');
        $page = $offset !== null && $offset !== '' ? (int) floor((int) $offset / $limit) + 1 : max(1, (int) $this->request->get('page', 1));
        $tenantId = $this->getTenantId();
        $query = ReviewModel::with(['store'])->where('tenant_id', $tenantId)->order('review_time', 'desc')->order('id', 'desc');
        $storeId = (int) $this->request->get('store_id', 0);
        if ($storeId > 0) $query->where('store_id', $storeId);
        $platform = trim((string) $this->request->get('platform', ''));
        if ($platform !== '') $query->where('platform', $platform);
        $replyStatus = $this->request->get('reply_status');
        if ($replyStatus !== null && $replyStatus !== '') $query->where('reply_status', (int) $replyStatus);
        $rating = $this->request->get('rating');
        if ($rating !== null && $rating !== '') $query->where('rating', (int) $rating);
        $kw = trim((string) $this->request->get('keyword', ''));
        if ($kw !== '') $query->where('content', 'like', '%' . $kw . '%');
        $total = $query->count();
        $list = $query->page($page, $limit)->select()->toArray();
        foreach ($list as &$row) {
            $row['store_name'] = $row['store']['name'] ?? '-';
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function sync(): Response
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $tenantId = $this->getTenantId();
        $since = trim((string) $this->request->post('since', date('Y-m-d')));
        $until = trim((string) $this->request->post('until', date('Y-m-d')));
        $client = new OpenClawClient($tenantId);
        $r = $client->pullReviews($since, $until);
        if (!$r['ok']) return $this->error($r['error'] ?? '同步失败');
        $list = (array) (($r['data']['list'] ?? $r['data']['data']['list'] ?? $r['data'] ?? []) ?: []);
        $count = $this->upsertReviews($tenantId, $list);
        return $this->success('已同步', ['count' => $count]);
    }

    public function autoReply(): Response
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $tenantId = $this->getTenantId();
        $limit = max(1, min(200, (int) $this->request->post('limit', 50)));
        $threshold = (int) Db::name('config')->where('name', 'restaurant_review_bad_threshold')->value('value');
        if ($threshold <= 0) $threshold = 3;

        $rows = ReviewModel::where('tenant_id', $tenantId)
            ->where('reply_status', 0)
            ->order('review_time', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
        if (!$rows) return $this->success('无待回复评价', ['count' => 0]);

        $tpls = ReviewReplyTemplateModel::where('tenant_id', $tenantId)->where('status', 1)->select()->toArray();
        $pickTemplate = function (array $row) use ($tpls, $threshold): ?string {
            $rating = (int) ($row['rating'] ?? 0);
            $scene = $rating <= $threshold ? 'bad' : 'good';
            foreach ($tpls as $t) {
                if ($t['platform'] !== '' && $t['platform'] !== ($row['platform'] ?? '')) continue;
                if (($t['scene'] ?? 'good') !== $scene) continue;
                if ($rating < (int) ($t['rating_min'] ?? 0) || $rating > (int) ($t['rating_max'] ?? 5)) continue;
                $txt = trim((string) ($t['template'] ?? ''));
                if ($txt !== '') return $txt;
            }
            return $scene === 'bad' ? '非常抱歉给您带来不好的体验，我们会立即核查并改进，欢迎您再次光临。' : '感谢您的支持与好评，欢迎下次再来～';
        };

        $toReply = [];
        foreach ($rows as $row) {
            $reply = trim((string) ($row['suggest_reply'] ?? ''));
            if ($reply === '') $reply = (string) $pickTemplate($row);
            if ($reply === '') continue;
            $toReply[] = [
                'platform' => (string) ($row['platform'] ?? ''),
                'external_id' => (string) ($row['external_id'] ?? ''),
                'reply_text' => $reply,
            ];
        }
        if (!$toReply) return $this->success('无可回复评价', ['count' => 0]);

        $client = new OpenClawClient($tenantId);
        $r = $client->replyReviews($toReply);
        if (!$r['ok']) return $this->error($r['error'] ?? '回写失败');

        $now = time();
        $successIds = [];
        $resultList = (array) (($r['data']['list'] ?? $r['data']['data']['list'] ?? $r['data'] ?? []) ?: []);
        if ($resultList) {
            foreach ($resultList as $it) {
                if (!is_array($it)) continue;
                if (!empty($it['external_id']) && ((int) ($it['ok'] ?? 0) === 1 || ($it['ok'] ?? '') === true)) {
                    $successIds[] = (string) $it['external_id'];
                }
            }
        } else {
            foreach ($toReply as $it) $successIds[] = (string) $it['external_id'];
        }
        if ($successIds) {
            foreach ($toReply as $it) {
                if (!in_array((string) $it['external_id'], $successIds, true)) continue;
                ReviewModel::where('tenant_id', $tenantId)
                    ->where('platform', (string) $it['platform'])
                    ->where('external_id', (string) $it['external_id'])
                    ->update(['reply_status' => 1, 'reply_content' => (string) $it['reply_text'], 'update_time' => $now]);
            }
        }
        return $this->success('已回写', ['count' => count($successIds)]);
    }

    public function stats(): Response
    {
        $tenantId = $this->getTenantId();
        $days = max(1, min(60, (int) $this->request->get('days', 7)));
        $since = strtotime("-{$days} days");
        $q = Db::name('restaurant_review')->where('tenant_id', $tenantId)->where('review_time', '>=', $since);
        $badThreshold = (int) Db::name('config')->where('name', 'restaurant_review_bad_threshold')->value('value');
        if ($badThreshold <= 0) $badThreshold = 3;
        $badCount = (clone $q)->where('rating', '<=', $badThreshold)->count();
        $allCount = (clone $q)->count();
        $top = (clone $q)
            ->where('rating', '<=', $badThreshold)
            ->where('keywords', '<>', '')
            ->field('keywords')
            ->limit(2000)
            ->select()
            ->toArray();
        $kwCount = [];
        $catCount = [];
        $kwMap = Db::name('restaurant_review_keyword')
            ->whereIn('tenant_id', [0, $tenantId])
            ->where('status', 1)
            ->column('category', 'keyword');
        foreach ($top as $row) {
            $parts = array_filter(array_map('trim', explode(',', (string) ($row['keywords'] ?? ''))));
            foreach ($parts as $p) {
                $kwCount[$p] = ($kwCount[$p] ?? 0) + 1;
                $cat = (string) ($kwMap[$p] ?? '');
                if ($cat !== '') $catCount[$cat] = ($catCount[$cat] ?? 0) + 1;
            }
        }
        arsort($kwCount);
        arsort($catCount);
        $kwTop = [];
        foreach (array_slice($kwCount, 0, 20, true) as $k => $c) {
            $kwTop[] = ['keyword' => $k, 'count' => $c];
        }
        $catTop = [];
        foreach (array_slice($catCount, 0, 10, true) as $k => $c) {
            $catTop[] = ['category' => $k, 'count' => $c];
        }
        $alerts = ReviewAlertModel::where('tenant_id', $tenantId)->where('review_time', '>=', $since)->where('alert_type', 'bad_review')->count();
        return $this->success('', [
            'days' => $days,
            'total_reviews' => $allCount,
            'bad_reviews' => $badCount,
            'alerts' => $alerts,
            'bad_keyword_top' => $kwTop,
            'bad_category_top' => $catTop,
        ]);
    }

    private function upsertReviews(int $tenantId, array $list): int
    {
        $now = time();
        $threshold = (int) Db::name('config')->where('name', 'restaurant_review_bad_threshold')->value('value');
        if ($threshold <= 0) $threshold = 3;
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
            if ($rating > 0 && $rating <= $threshold) {
                $this->createAlertIfNeeded($tenantId, $storeId, $platform, $externalId, $rating, $content, (int) $data['review_time']);
            }
            $count++;
        }
        return $count;
    }

    private function createAlertIfNeeded(int $tenantId, int $storeId, string $platform, string $externalId, int $rating, string $content, int $reviewTime): void
    {
        $enabled = (string) Db::name('config')->where('name', 'restaurant_review_alert_enabled')->value('value');
        if ($enabled === '' || $enabled === '0') return;
        $exist = Db::name('restaurant_review_alert')
            ->where('tenant_id', $tenantId)
            ->where('alert_type', 'bad_review')
            ->where('platform', $platform)
            ->where('external_id', $externalId)
            ->find();
        if ($exist) return;
        $now = time();
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
            'create_time' => $now,
        ]);
        $push = (string) Db::name('config')->where('name', 'restaurant_review_alert_push_openclaw')->value('value');
        if ($push === '' || $push === '1') {
            $client = new OpenClawClient($tenantId);
            $client->pushAlert([
                'type' => 'bad_review',
                'store_id' => $storeId,
                'platform' => $platform,
                'external_id' => $externalId,
                'rating' => $rating,
                'content' => mb_substr(trim($content), 0, 500),
                'review_time' => $reviewTime,
            ]);
        }
    }
}
