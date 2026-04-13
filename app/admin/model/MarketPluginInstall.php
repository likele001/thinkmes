<?php
declare(strict_types=1);

namespace app\admin\model;

use think\Model;

class MarketPluginInstall extends Model
{
    protected $name = 'market_plugin_install';

    protected $autoWriteTimestamp = true;

    protected $type = [
        'plugin_id'     => 'integer',
        'user_id'       => 'integer',
        'tenant_id'     => 'integer',
        'install_time'  => 'timestamp',
        'update_time'   => 'timestamp',
    ];

    public function getInstalledPlugins(int $userId, int $tenantId, int $page = 1, int $limit = 20): array
    {
        return $this->alias('i')
            ->join('market_plugin p', 'i.plugin_id = p.id')
            ->field('i.*, p.name as plugin_name, p.title as plugin_title')
            ->where('i.user_id', $userId)
            ->where('i.tenant_id', $tenantId)
            ->order('i.install_time', 'desc')
            ->paginate([
                'list_rows' => $limit,
                'page' => $page,
            ])
            ->toArray();
    }

    public function getInstalledPlugin(string $name, int $userId, int $tenantId): ?self
    {
        return $this->alias('i')
            ->join('market_plugin p', 'i.plugin_id = p.id')
            ->where('p.name', $name)
            ->where('i.user_id', $userId)
            ->where('i.tenant_id', $tenantId)
            ->find();
    }

    public function installPlugin(int $pluginId, string $version, int $userId, int $tenantId): bool
    {
        $existing = $this->where('plugin_id', $pluginId)
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->find();

        if ($existing) {
            $existing->version = $version;
            return $existing->save();
        }

        $this->plugin_id = $pluginId;
        $this->version = $version;
        $this->user_id = $userId;
        $this->tenant_id = $tenantId;

        return $this->save();
    }

    public function uninstallPlugin(string $name, int $userId, int $tenantId): bool
    {
        $install = $this->alias('i')
            ->join('market_plugin p', 'i.plugin_id = p.id')
            ->where('p.name', $name)
            ->where('i.user_id', $userId)
            ->where('i.tenant_id', $tenantId)
            ->find();

        if ($install) {
            return $install->delete();
        }

        return false;
    }
}
