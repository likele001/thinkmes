<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use think\Response;

class Addon extends Backend
{
    public function index(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理插件');
        }
        if ($this->request->isAjax()) {
            $addonsPath = config('addon.addons_path');
            $list = [];
            if (is_dir($addonsPath)) {
                $dh = opendir($addonsPath);
                if ($dh !== false) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file === '.' || $file === '..') {
                            continue;
                        }
                        $full = $addonsPath . $file . DIRECTORY_SEPARATOR;
                        if (!is_dir($full)) {
                            continue;
                        }
                        $pluginFile = $full . 'plugin.json';
                        if (!is_file($pluginFile)) {
                            continue;
                        }
                        $json = json_decode((string) file_get_contents($pluginFile), true);
                        if (!is_array($json)) {
                            $json = [];
                        }
                        $name = (string) ($json['name'] ?? $file);
                        $title = (string) ($json['title'] ?? $file);
                        $version = (string) ($json['version'] ?? '');
                        $enabled = 0;
                        $installed = 0;
                        $configRow = Db::name('config')->where('name', 'addon_' . $name . '_status')->find();
                        if ($configRow) {
                            $installed = 1;
                            $enabled = (int) ($configRow['value'] === '1' ? 1 : 0);
                        }
                        $list[] = [
                            'name'      => $name,
                            'title'     => $title,
                            'version'   => $version,
                            'installed' => $installed,
                            'enabled'   => $enabled,
                        ];
                    }
                    closedir($dh);
                }
            }
            return $this->success('', ['total' => count($list), 'list' => $list]);
        }
        View::assign('title', '插件管理');
        return $this->fetchWithLayout('addon/index');
    }

    public function detail(): string|Response
    {
        $name = trim((string) $this->request->get('name', ''));
        if ($name === '') {
            return $this->error('插件名不能为空');
        }
        $addonsPath = config('addon.addons_path');
        $path = $addonsPath . $name . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            return $this->error('插件不存在');
        }
        $info = [
            'name'        => $name,
            'title'       => $name,
            'version'     => '',
            'description' => '',
            'hooks'       => [],
            'installed'   => 0,
            'enabled'     => 0,
        ];
        $pluginFile = $path . 'plugin.json';
        if (is_file($pluginFile)) {
            $json = json_decode((string) file_get_contents($pluginFile), true);
            if (is_array($json)) {
                $info['title'] = (string) ($json['title'] ?? $info['title']);
                $info['version'] = (string) ($json['version'] ?? '');
                $info['description'] = (string) ($json['description'] ?? '');
                $info['hooks'] = (array) ($json['hooks'] ?? []);
            }
        }
        $configRow = Db::name('config')->where('name', 'addon_' . $name . '_status')->find();
        if ($configRow) {
            $info['installed'] = 1;
            $info['enabled'] = $configRow['value'] === '1' ? 1 : 0;
        }
        if ($this->request->isAjax()) {
            return $this->success('', $info);
        }
        View::assign('info', $info);
        View::assign('title', '插件详情');
        return $this->fetchWithLayout('addon/detail');
    }

    public function install(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理插件');
        }
        $name = trim((string) $this->request->post('name', ''));
        if ($name === '') {
            return $this->error('插件名不能为空');
        }
        $addonsPath = config('addon.addons_path');
        $path = $addonsPath . $name . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            return $this->error('插件不存在');
        }
        $sqlFile = $path . 'install.sql';
        if (is_file($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $stmts = array_filter(array_map('trim', explode(';', (string) $sql)));
            foreach ($stmts as $stmt) {
                if ($stmt === '') {
                    continue;
                }
                try {
                    Db::execute($stmt);
                } catch (\Throwable $e) {
                }
            }
        }
        if (is_file($path . 'bootstrap.php')) {
            (function () use ($path) {
                include $path . 'bootstrap.php';
            })();
        }
        $exists = Db::name('config')->where('name', 'addon_' . $name . '_status')->find();
        if ($exists) {
            Db::name('config')->where('id', $exists['id'])->update(['value' => '1']);
        } else {
            Db::name('config')->insert([
                'name'  => 'addon_' . $name . '_status',
                'value' => '1',
                'group' => 'addon',
                'sort'  => 0,
            ]);
        }
        return $this->success('安装成功');
    }

    public function uninstall(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理插件');
        }
        $name = trim((string) $this->request->post('name', ''));
        if ($name === '') {
            return $this->error('插件名不能为空');
        }
        $addonsPath = config('addon.addons_path');
        $path = $addonsPath . $name . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            return $this->error('插件不存在');
        }
        $sqlFile = $path . 'uninstall.sql';
        if (is_file($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $stmts = array_filter(array_map('trim', explode(';', (string) $sql)));
            foreach ($stmts as $stmt) {
                if ($stmt === '') {
                    continue;
                }
                try {
                    Db::execute($stmt);
                } catch (\Throwable $e) {
                }
            }
        }
        Db::name('config')->where('name', 'addon_' . $name . '_status')->delete();
        return $this->success('卸载成功');
    }

    public function enable(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理插件');
        }
        $name = trim((string) $this->request->post('name', ''));
        if ($name === '') {
            return $this->error('插件名不能为空');
        }
        $exists = Db::name('config')->where('name', 'addon_' . $name . '_status')->find();
        if ($exists) {
            Db::name('config')->where('id', $exists['id'])->update(['value' => '1']);
        } else {
            Db::name('config')->insert([
                'name'  => 'addon_' . $name . '_status',
                'value' => '1',
                'group' => 'addon',
                'sort'  => 0,
            ]);
        }
        return $this->success('启用成功');
    }

    public function disable(): Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理插件');
        }
        $name = trim((string) $this->request->post('name', ''));
        if ($name === '') {
            return $this->error('插件名不能为空');
        }
        $exists = Db::name('config')->where('name', 'addon_' . $name . '_status')->find();
        if ($exists) {
            Db::name('config')->where('id', $exists['id'])->update(['value' => '0']);
        }
        return $this->success('禁用成功');
    }

    public function config(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可管理插件');
        }
        $name = trim((string) $this->request->get('name', ''));
        if ($name === '') {
            return $this->error('插件名不能为空');
        }
        $addonsPath = config('addon.addons_path');
        $path = $addonsPath . $name . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            return $this->error('插件不存在');
        }
        $tenantId = (int) $this->request->param('tenant_id', 0);
        if ($tenantId < 0) {
            $tenantId = 0;
        }
        if ($this->request->isPost()) {
            $values = $this->request->post('config');
            if (!is_array($values)) {
                $values = [];
            }
            $value = json_encode($values, JSON_UNESCAPED_UNICODE);
            if ($name === 'cloudstorage') {
                $row = null;
                try {
                    $row = Db::name('addon_cloudstorage_config')->where('tenant_id', $tenantId)->find();
                } catch (\Throwable $e) {
                    $row = null;
                }
                $now = time();
                if ($row) {
                    Db::name('addon_cloudstorage_config')->where('id', $row['id'])->update([
                        'config'      => $value,
                        'update_time' => $now,
                    ]);
                } else {
                    Db::name('addon_cloudstorage_config')->insert([
                        'tenant_id'   => $tenantId,
                        'config'      => $value,
                        'create_time' => $now,
                        'update_time' => $now,
                    ]);
                }
            } else {
                $configName = 'addon_' . $name . '_config';
                $row = Db::name('config')->where('name', $configName)->find();
                if ($row) {
                    Db::name('config')->where('id', $row['id'])->update(['value' => $value]);
                } else {
                    Db::name('config')->insert([
                        'name'  => $configName,
                        'value' => $value,
                        'group' => 'addon',
                        'sort'  => 0,
                    ]);
                }
            }
            return $this->success('保存成功');
        }
        $schemaFile = $path . 'config.json';
        $schema = [];
        if (is_file($schemaFile)) {
            $schema = json_decode((string) file_get_contents($schemaFile), true) ?: [];
        }
        $values = [];
        if ($name === 'cloudstorage') {
            try {
                $row = Db::name('addon_cloudstorage_config')->where('tenant_id', $tenantId)->find();
            } catch (\Throwable $e) {
                $row = null;
            }
            if ($row && is_string($row['config']) && $row['config'] !== '') {
                $values = json_decode((string) $row['config'], true) ?: [];
            }
        } else {
            $configName = 'addon_' . $name . '_config';
            $row = Db::name('config')->where('name', $configName)->find();
            if ($row && is_string($row['value']) && $row['value'] !== '') {
                $values = json_decode((string) $row['value'], true) ?: [];
            }
        }
        View::assign('name', $name);
        View::assign('tenantId', $tenantId);
        View::assign('schema', $schema);
        View::assign('values', $values);
        View::assign('title', '插件配置');
        return $this->fetchWithLayout('addon/config');
    }
}
