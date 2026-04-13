<?php
declare(strict_types=1);

namespace app\admin\service;

use app\admin\model\MarketPlugin;
use app\admin\model\MarketPluginVersion;
use app\admin\model\MarketPluginInstall;
use think\facade\Db;
use think\facade\Cache;

class MarketService
{
    protected string $downloadPath = '/tmp/addon_downloads/';
    protected string $addonPath = '/www/wwwroot/thinkmes/addons/';
    protected int $cacheTime = 3600;

    protected array $builtinPlugins = [
        'cloudstorage' => [
            'title' => '云存储插件',
        ],
        'demo' => [
            'title' => '示例插件',
        ],
    ];

    public function getPluginList(string $keyword = '', string $category = '', int $page = 1, int $limit = 20): array
    {
        $cacheKey = "market_plugins_{$keyword}_{$category}_{$page}_{$limit}";
        $result = Cache::get($cacheKey);

        if ($result === null) {
            $pluginModel = new MarketPlugin();
            $result = $pluginModel->searchPlugins($keyword, $category, $page, $limit);
            Cache::set($cacheKey, $result, $this->cacheTime);
        }

        return $result;
    }

    public function getPluginDetail(int $id): ?array
    {
        $plugin = MarketPlugin::find($id);
        if (!$plugin) {
            return null;
        }

        $latestVersion = $plugin->getLatestVersion();
        $reviews = $plugin->reviews()->order('create_time', 'desc')->limit(10)->select();

        return [
            'plugin'        => $plugin->toArray(),
            'latestVersion' => $latestVersion ? $latestVersion->toArray() : null,
            'reviews'       => $reviews->toArray(),
        ];
    }

    public function downloadPlugin(string $name, string $version, string $downloadUrl): array
    {
        if (!is_dir($this->downloadPath)) {
            mkdir($this->downloadPath, 0755, true);
        }

        $filename = "{$name}-{$version}.zip";
        $filepath = $this->downloadPath . $filename;

        if ($this->isBuiltinPlugin($name) && ($downloadUrl === '' || str_starts_with($downloadUrl, 'builtin://'))) {
            $this->buildBuiltinPluginZip($name, $version, $filepath);
        } else {
            $content = @file_get_contents($downloadUrl);
            if ($content === false) {
                if ($this->isBuiltinPlugin($name)) {
                    $this->buildBuiltinPluginZip($name, $version, $filepath);
                } else {
                    throw new \Exception('下载失败');
                }
            } else {
                if ($this->isBuiltinPlugin($name) && substr($content, 0, 2) !== 'PK') {
                    $this->buildBuiltinPluginZip($name, $version, $filepath);
                } else {
                    file_put_contents($filepath, $content);
                }
            }
        }

        $pluginPath = $this->addonPath . $name;

        if (is_dir($pluginPath)) {
            $this->removeDirectory($pluginPath);
        }

        mkdir($pluginPath, 0755, true);

        $zip = new \ZipArchive();
        if ($zip->open($filepath) !== true) {
            throw new \Exception('解压失败');
        }

        $zip->extractTo($pluginPath);
        $zip->close();

        unlink($filepath);

        return [
            'path'     => $pluginPath,
            'filename' => $filename,
        ];
    }

    protected function isBuiltinPlugin(string $name): bool
    {
        return isset($this->builtinPlugins[$name]);
    }

    protected function buildBuiltinPluginZip(string $name, string $version, string $filepath): void
    {
        $meta = $this->builtinPlugins[$name] ?? [];
        $title = (string) ($meta['title'] ?? $name);
        $installFn = 'addon_install_' . $name;
        $uninstallFn = 'addon_uninstall_' . $name;

        $installPhp = "<?php\n"
            . "function {$installFn}()\n"
            . "{\n"
            . "    return true;\n"
            . "}\n";

        $uninstallPhp = "<?php\n"
            . "function {$uninstallFn}()\n"
            . "{\n"
            . "    return true;\n"
            . "}\n";

        $info = json_encode([
            'name' => $name,
            'title' => $title,
            'version' => $version,
            'built_in' => true,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $zip = new \ZipArchive();
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if ($zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('生成插件包失败');
        }
        $zip->addFromString('install.php', $installPhp);
        $zip->addFromString('uninstall.php', $uninstallPhp);
        $zip->addFromString('plugin.json', $info ?: '{}');
        $zip->close();
    }

    public function installPlugin(string $name, string $version, int $userId, int $tenantId): bool
    {
        $installModel = new MarketPluginInstall();
        $installed = $installModel->getInstalledPlugin($name, $userId, $tenantId);
        if ($installed) {
            $installedVersion = (string) ($installed->version ?? '');
            if ($installedVersion === $version) {
                throw new \Exception('已安装该插件（版本 ' . $installedVersion . '），无需重复安装');
            }
            throw new \Exception('该插件已安装（当前版本 ' . ($installedVersion !== '' ? $installedVersion : '-') . '），请到「我的插件」执行更新');
        }

        $plugin = MarketPlugin::where('name', $name)->find();
        if (!$plugin) {
            throw new \Exception('插件不存在');
        }

        $pluginVersion = MarketPluginVersion::where('plugin_id', $plugin->id)
            ->where('version', $version)
            ->find();

        if (!$pluginVersion) {
            $downloadUrl = (string) ($plugin->download_url ?? '');
            if ($downloadUrl === '') {
                throw new \Exception('插件版本不存在，且缺少下载地址');
            }
            $pluginVersion = new MarketPluginVersion();
            $pluginVersion->save([
                'plugin_id' => (int) $plugin->id,
                'version' => $version,
                'changelog' => '',
                'download_url' => $downloadUrl,
                'file_size' => (int) ($plugin->file_size ?? 0),
                'min_version' => (string) ($plugin->min_version ?? ''),
                'max_version' => (string) ($plugin->max_version ?? ''),
                'is_stable' => 1,
                'downloads' => 0,
                'released_at' => (int) ($plugin->released_at ?? time()),
            ]);
        }

        $pluginPath = $this->addonPath . $name;
        if (!is_dir($pluginPath)) {
            $this->downloadPlugin($name, $version, $pluginVersion->download_url);
        }

        $this->runInstallScript($pluginPath, $name);

        $installModel->installPlugin($plugin->id, $version, $userId, $tenantId);

        $plugin->incrementDownloads();
        $pluginVersion->incrementDownloads();

        Cache::clear('market_plugins_');

        return true;
    }

    protected function runInstallScript(string $pluginPath, string $name): void
    {
        $installFile = $pluginPath . '/install.php';
        if (file_exists($installFile)) {
            require_once $installFile;
            if (function_exists('addon_install_' . $name)) {
                call_user_func('addon_install_' . $name);
            }
        }
    }

    protected function runUninstallScript(string $pluginPath, string $name): void
    {
        $uninstallFile = $pluginPath . '/uninstall.php';
        if (file_exists($uninstallFile)) {
            require_once $uninstallFile;
            if (function_exists('addon_uninstall_' . $name)) {
                call_user_func('addon_uninstall_' . $name);
            }
        }
    }

    public function uninstallPlugin(string $name, int $userId, int $tenantId): bool
    {
        $installModel = new MarketPluginInstall();
        $install = $installModel->getInstalledPlugin($name, $userId, $tenantId);

        if (!$install) {
            throw new \Exception('插件未安装');
        }

        $pluginPath = $this->addonPath . $name;
        if (is_dir($pluginPath)) {
            $this->runUninstallScript($pluginPath, $name);
            $this->removeDirectory($pluginPath);
        }

        $installModel->uninstallPlugin($name, $userId, $tenantId);

        Cache::clear('market_plugins_');

        return true;
    }

    public function updatePlugin(string $name, int $userId, int $tenantId): bool
    {
        $installModel = new MarketPluginInstall();
        $install = $installModel->getInstalledPlugin($name, $userId, $tenantId);

        if (!$install) {
            throw new \Exception('插件未安装');
        }

        $plugin = MarketPlugin::where('name', $name)->find();
        if (!$plugin) {
            throw new \Exception('插件不存在');
        }

        $latestVersion = $plugin->getLatestVersion();
        if (!$latestVersion) {
            throw new \Exception('没有可用的更新版本');
        }

        if ($install->version === $latestVersion->version) {
            throw new \Exception('已是最新版本');
        }

        $this->downloadPlugin($name, $latestVersion->version, $latestVersion->download_url);
        $install->version = $latestVersion->version;
        $install->save();

        $latestVersion->incrementDownloads();

        Cache::clear('market_plugins_');

        return true;
    }

    public function submitPlugin(array $params, int $userId): bool
    {
        Db::startTrans();
        try {
            $plugin = MarketPlugin::where('name', $params['name'])->find();

            if ($plugin) {
                $plugin->title = $params['title'];
                $plugin->description = $params['description'] ?? '';
                $plugin->author = $params['author'] ?? '';
                $plugin->version = $params['version'];
                $plugin->category = $params['category'];
                $plugin->homepage = $params['homepage'] ?? '';
                $plugin->keywords = $params['keywords'] ?? '';
                $plugin->price = $params['price'] ?? 0.00;
                $plugin->updated_at = time();
                $plugin->status = 'draft';
                $plugin->save();

                $pluginId = $plugin->id;
            } else {
                $plugin = MarketPlugin::create([
                    'name'          => $params['name'],
                    'title'         => $params['title'],
                    'description'   => $params['description'] ?? '',
                    'author'        => $params['author'] ?? '',
                    'version'       => $params['version'],
                    'category'      => $params['category'],
                    'homepage'      => $params['homepage'] ?? '',
                    'keywords'      => $params['keywords'] ?? '',
                    'price'         => $params['price'] ?? 0.00,
                    'status'        => 'draft',
                    'released_at'   => time(),
                ]);

                $pluginId = $plugin->id;
            }

            MarketPluginVersion::create([
                'plugin_id'     => $pluginId,
                'version'       => $params['version'],
                'changelog'     => $params['changelog'] ?? '',
                'download_url'  => $params['download_url'] ?? '',
                'file_size'     => $params['file_size'] ?? 0,
                'is_stable'     => 1,
                'released_at'   => time(),
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    public function getInstalledPlugins(int $userId, int $tenantId, int $page = 1, int $limit = 20): array
    {
        $installModel = new MarketPluginInstall();
        return $installModel->getInstalledPlugins($userId, $tenantId, $page, $limit);
    }

    protected function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
