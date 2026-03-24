<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class MarketPluginReview extends Model
{
    protected $name = 'market_plugin_review';

    protected $autoWriteTimestamp = true;

    protected $type = [
        'plugin_id'     => 'integer',
        'user_id'       => 'integer',
        'rating'        => 'integer',
        'is_verified'   => 'integer',
    ];

    public function plugin()
    {
        return $this->belongsTo(MarketPlugin::class, 'plugin_id');
    }

    public function getReviewsByPlugin(int $pluginId, int $page = 1, int $limit = 20): array
    {
        return $this->where('plugin_id', $pluginId)
            ->order('create_time', 'desc')
            ->paginate([
                'list_rows' => $limit,
                'page' => $page,
            ])
            ->toArray();
    }

    public function getUserReview(int $pluginId, int $userId): ?self
    {
        return $this->where('plugin_id', $pluginId)
            ->where('user_id', $userId)
            ->find();
    }

    public function addReview(int $pluginId, int $userId, string $userName, int $rating, string $content = ''): bool
    {
        if ($rating < 1 || $rating > 5) {
            throw new \Exception('评分必须在1-5之间');
        }

        $existingReview = $this->getUserReview($pluginId, $userId);
        if ($existingReview) {
            throw new \Exception('您已经评价过此插件');
        }

        $this->plugin_id = $pluginId;
        $this->user_id = $userId;
        $this->user_name = $userName;
        $this->rating = $rating;
        $this->content = $content;
        $this->is_verified = 0;

        return $this->save();
    }
}
