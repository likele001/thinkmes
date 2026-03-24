<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class MarketPluginVersion extends Model
{
    protected $name = 'market_plugin_version';

    protected $autoWriteTimestamp = true;

    protected $type = [
        'plugin_id'     => 'integer',
        'file_size'     => 'integer',
        'downloads'     => 'integer',
        'is_stable'     => 'integer',
        'released_at'   => 'timestamp',
    ];

    public function plugin()
    {
        return $this->belongsTo(MarketPlugin::class, 'plugin_id');
    }

    public function incrementDownloads(): bool
    {
        return $this->setInc('downloads');
    }

    public function getVersionsByPlugin(int $pluginId): array
    {
        return $this->where('plugin_id', $pluginId)
            ->order('released_at', 'desc')
            ->select()
            ->toArray();
    }

    public function getLatestVersion(int $pluginId): ?self
    {
        return $this->where('plugin_id', $pluginId)
            ->order('released_at', 'desc')
            ->find();
    }

    public function getStableVersions(int $pluginId): array
    {
        return $this->where('plugin_id', $pluginId)
            ->where('is_stable', 1)
            ->order('released_at', 'desc')
            ->select()
            ->toArray();
    }
}
