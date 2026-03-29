<?php
declare(strict_types=1);

namespace app\common\lib\restaurant;

use think\facade\Db;

class ReviewAnalyzer
{
    public static function analyze(int $tenantId, string $content, int $rating): array
    {
        $content = trim($content);
        $rating = max(0, min(5, $rating));

        $keywords = self::extractKeywords($tenantId, $content);
        $sentiment = 0;
        if ($rating >= 4) $sentiment = 1;
        if ($rating <= 2) $sentiment = -1;
        if ($sentiment === 0 && $keywords) {
            $negCats = ['出餐', '口味', '服务', '卫生', '价格', '分量', '环境', '外卖'];
            foreach ($keywords as $kw) {
                if (!empty($kw['category']) && in_array($kw['category'], $negCats, true)) {
                    $sentiment = -1;
                    break;
                }
            }
        }
        return [
            'keywords' => implode(',', array_values(array_unique(array_map(fn ($x) => (string) ($x['keyword'] ?? ''), $keywords)))),
            'sentiment' => $sentiment,
            'keyword_items' => $keywords,
        ];
    }

    public static function extractKeywords(int $tenantId, string $content): array
    {
        if ($content === '') return [];
        $list = Db::name('restaurant_review_keyword')
            ->whereIn('tenant_id', [0, $tenantId])
            ->where('status', 1)
            ->order('weight', 'desc')
            ->select()
            ->toArray();
        if (!$list) return [];
        $hit = [];
        foreach ($list as $k) {
            $kw = (string) ($k['keyword'] ?? '');
            if ($kw === '') continue;
            if (mb_strpos($content, $kw) !== false) {
                $hit[] = [
                    'keyword' => $kw,
                    'category' => (string) ($k['category'] ?? ''),
                    'weight' => (int) ($k['weight'] ?? 1),
                ];
            }
        }
        return $hit;
    }
}
