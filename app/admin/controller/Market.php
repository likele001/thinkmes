<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\MarketPlugin;
use app\admin\model\MarketPluginVersion;
use app\admin\model\MarketPluginReview;
use app\admin\model\MarketPluginInstall;
use app\admin\service\MarketService;
use think\facade\Validate;
use think\facade\View;
use think\exception\ValidateException;
use think\Response;

class Market extends Backend
{
    protected ?MarketPlugin $model = null;
    protected $noNeedRight = ['detail', 'versions', 'reviews', 'download'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new MarketPlugin();
    }

    public function index(): string|Response
    {
        $keyword = (string) $this->request->get('keyword', '');
        $category = (string) $this->request->get('category', 'all');
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));

        if ($this->request->isAjax()) {
            $result = $this->model->searchPlugins($keyword, $category, $page, $limit);
            return json(['code' => 0, 'data' => $result]);
        }

        View::assign('keyword', $keyword);
        View::assign('category', $category);
        return $this->fetchWithLayout('market/index');
    }

    public function detail($id = null): string
    {
        if (!$id) {
            $this->error('参数错误');
        }

        $plugin = $this->model->find($id);
        if (!$plugin) {
            $this->error('插件不存在');
        }

        $latestVersion = $plugin->getLatestVersion();
        $reviews = $plugin->reviews()->order('create_time', 'desc')->limit(10)->select();

        View::assign('plugin', $plugin);
        View::assign('latestVersion', $latestVersion);
        View::assign('reviews', $reviews);
        return $this->fetchWithLayout('market/detail');
    }

    public function versions($id = null)
    {
        if (!$id) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $versionModel = new MarketPluginVersion();
        $versions = $versionModel->getVersionsByPlugin($id);

        return json(['code' => 0, 'data' => $versions]);
    }

    public function reviews($id = null)
    {
        if (!$id) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $plugin = $this->model->find($id);
        if (!$plugin) {
            return json(['code' => 0, 'msg' => '插件不存在']);
        }

        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);

        $reviews = $plugin->reviews()->order('create_time', 'desc')->paginate([
            'list_rows' => $limit,
            'page' => $page,
        ]);

        return json(['code' => 0, 'data' => $reviews->toArray()]);
    }

    public function install($id = null)
    {
        if (!$id) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        if ($this->request->isPost()) {
            $plugin = $this->model->find($id);
            if (!$plugin) {
                return json(['code' => 0, 'msg' => '插件不存在']);
            }

            $version = $this->request->post('version', '');
            if (empty($version)) {
                return json(['code' => 0, 'msg' => '请选择版本']);
            }

            try {
                $marketService = new MarketService();
                $result = $marketService->installPlugin($plugin->name, $version, $this->auth->id, $this->auth->tenant_id);

                if ($result) {
                    return json(['code' => 1, 'msg' => '安装成功']);
                } else {
                    return json(['code' => 0, 'msg' => '安装失败']);
                }
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => $e->getMessage()]);
            }
        }

        $plugin = $this->model->find($id);
        $versions = (new MarketPluginVersion())->getVersionsByPlugin($id);

        View::assign('plugin', $plugin);
        View::assign('versions', $versions);
        return $this->fetchWithLayout('market/install');
    }

    public function download($id = null, $version = null)
    {
        if (!$id || !$version) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $plugin = $this->model->find($id);
        if (!$plugin) {
            return json(['code' => 0, 'msg' => '插件不存在']);
        }

        $versionModel = MarketPluginVersion::where('plugin_id', $id)
            ->where('version', $version)
            ->find();

        if (!$versionModel) {
            return json(['code' => 0, 'msg' => '版本不存在']);
        }

        try {
            $marketService = new MarketService();
            $result = $marketService->downloadPlugin($plugin->name, $version, $versionModel->download_url);

            if ($result) {
                $versionModel->incrementDownloads();
                return json(['code' => 1, 'msg' => '下载成功', 'data' => $result]);
            } else {
                return json(['code' => 0, 'msg' => '下载失败']);
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }
    }

    public function myPlugins(): string|Response
    {
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));

        if ($this->request->isAjax()) {
            $marketService = new MarketService();
            $result = $marketService->getInstalledPlugins($this->auth->id, $this->auth->tenant_id, $page, $limit);
            return json(['code' => 0, 'data' => $result]);
        }

        return $this->fetchWithLayout('market/my_plugins');
    }

    public function installed(): string
    {
        return $this->myPlugins();
    }

    public function uninstall($name = null)
    {
        if (!$name) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        if ($this->request->isPost()) {
            try {
                $marketService = new MarketService();
                $result = $marketService->uninstallPlugin($name, $this->auth->id, $this->auth->tenant_id);

                if ($result) {
                    return json(['code' => 1, 'msg' => '卸载成功']);
                } else {
                    return json(['code' => 0, 'msg' => '卸载失败']);
                }
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => $e->getMessage()]);
            }
        }
    }

    public function update($name = null)
    {
        if (!$name) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        if ($this->request->isPost()) {
            try {
                $marketService = new MarketService();
                $result = $marketService->updatePlugin($name, $this->auth->id, $this->auth->tenant_id);

                if ($result) {
                    return json(['code' => 1, 'msg' => '更新成功']);
                } else {
                    return json(['code' => 0, 'msg' => '更新失败']);
                }
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => $e->getMessage()]);
            }
        }
    }

    public function enable()
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id');
            if (!$id) {
                return json(['code' => 0, 'msg' => '参数错误']);
            }

            $install = MarketPluginInstall::find($id);
            if (!$install) {
                return json(['code' => 0, 'msg' => '记录不存在']);
            }

            $install->status = 1;
            $install->save();

            return json(['code' => 1, 'msg' => '启用成功']);
        }
    }

    public function disable()
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id');
            if (!$id) {
                return json(['code' => 0, 'msg' => '参数错误']);
            }

            $install = MarketPluginInstall::find($id);
            if (!$install) {
                return json(['code' => 0, 'msg' => '记录不存在']);
            }

            $install->status = 0;
            $install->save();

            return json(['code' => 1, 'msg' => '禁用成功']);
        }
    }

    public function submit()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();

            $validate = Validate::rule([
                'name'      => 'require|alphaNum',
                'title'     => 'require',
                'version'   => 'require',
                'category'  => 'require|in:tool,plugin,template,theme,other',
            ]);

            if (!$validate->check($params)) {
                return json(['code' => 0, 'msg' => $validate->getError()]);
            }

            try {
                $marketService = new MarketService();
                $result = $marketService->submitPlugin($params, $this->auth->id);

                if ($result) {
                    return json(['code' => 1, 'msg' => '提交成功，等待审核']);
                } else {
                    return json(['code' => 0, 'msg' => '提交失败']);
                }
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => $e->getMessage()]);
            }
        }

        return $this->fetchWithLayout('market/submit');
    }
}
