<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class MarketPlugin extends Model
{
    protected $name = 'market_plugin';

    protected $autoWriteTimestamp = true;

    protected $type = [
        'price'         => 'float',
        'file_size'     => 'integer',
        'downloads'     => 'integer',
        'rating'        => 'float',
        'rating_count'  => 'integer',
        'is_official'   => 'integer',
        'is_featured'   => 'integer',
        'released_at'   => 'timestamp',
        'dependencies'  => 'json',
    ];

    public function versions()
    {
        return $this->hasMany(MarketPluginVersion::class, 'plugin_id');
    }

    public function reviews()
    {
        return $this->hasMany(MarketPluginReview::class, 'plugin_id');
    }

    public function getLatestVersion()
    {
        return $this->versions()->order('released_at', 'desc')->find();
    }

    public function incrementDownloads(): bool
    {
        return $this->setInc('downloads');
    }

    public function updateRating(float $newRating, int $ratingCount): bool
    {
        $totalRating = $this->rating * $this->rating_count + $newRating;
        $this->rating = round($totalRating / $ratingCount, 2);
        $this->rating_count = $ratingCount;
        return $this->save();
    }

    public function getFeaturedPlugins(int $limit = 10): array
    {
        return $this->where('status', 'active')
            ->where('is_featured', 1)
            ->order('rating', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }

    public function getOfficialPlugins(int $limit = 10): array
    {
        return $this->where('status', 'active')
            ->where('is_official', 1)
            ->order('rating', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }

    public function searchPlugins(string $keyword, string $category = '', int $page = 1, int $limit = 20): array
    {
        $query = $this->where('status', 'active');

        if (!empty($keyword)) {
            $query->where('title|name|keywords|description', 'like', '%' . $keyword . '%');
        }

        if (!empty($category) && $category !== 'all') {
            $query->where('category', $category);
        }

        $query->order('is_featured', 'desc')
            ->order('rating', 'desc')
            ->order('downloads', 'desc');

        return $query->paginate([
            'list_rows' => $limit,
            'page' => $page,
        ])->toArray();
    }

    public function getPluginsByCategory(string $category, int $limit = 20): array
    {
        return $this->where('status', 'active')
            ->where('category', $category)
            ->order('rating', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }
}
