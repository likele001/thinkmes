<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\CommandModel;
use app\common\service\CrudGenerator;
use think\facade\View;
use think\Response;

/**
 * CRUD 一键生成（后台可视化，仿 FastAdmin 在线命令）
 * 仅平台超级管理员可访问
 */
class CrudGen extends Backend
{
    protected function checkSuperAdmin(): ?Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可访问');
        }
        return null;
    }

    /** 列表页：非 Ajax 渲染列表视图，Ajax 返回分页数据 */
    public function index(): string|Response
    {
        if ($err = $this->checkSuperAdmin()) {
            return $err;
        }
        if ($this->request->isAjax()) {
            return $this->indexData();
        }
        View::assign('title', '在线命令');
        return $this->fetchWithLayout('crud_gen/index');
    }

    /** 列表数据（分页） */
    protected function indexData(): Response
    {
        $limit = (int) $this->request->get('limit', 20);
        $offset = (int) $this->request->get('offset', 0);
        $page = max(1, (int) $this->request->get('page', 1));
        if ($limit <= 0) {
            $limit = 20;
        }
        if ($offset <= 0) {
            $offset = ($page - 1) * $limit;
        }

        $query = (new CommandModel())->order('id', 'desc');
        $total = $query->count();
        $list = $query->limit($limit, $offset)->select()->toArray();
        foreach ($list as &$row) {
            $row['type_text'] = CommandModel::typeText($row['type'] ?? '');
            $row['status_text'] = CommandModel::statusText((int) ($row['status'] ?? 0));
        }
        unset($row);
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    /** 添加页（一键生成 CRUD 表单） */
    public function add(): string|Response
    {
        if ($err = $this->checkSuperAdmin()) {
            return $err;
        }
        View::assign('title', '一键生成CRUD');
        return $this->fetchWithLayout('crud_gen/add');
    }

    /** 获取表字段列表（供「显示字段」多选） */
    public function getFieldList(): Response
    {
        if ($err = $this->checkSuperAdmin()) {
            return $err;
        }
        $table = trim((string) $this->request->get('table', ''));
        if ($table === '') {
            return $this->success('', ['list' => []]);
        }
        $prefix = config('database.connections.mysql.prefix', 'fa_');
        $fullTable = $prefix . $table;
        try {
            $gen = new CrudGenerator();
            $cols = $gen->getFullColumns($fullTable);
            $list = [];
            foreach ($cols as $c) {
                $list[] = [
                    'field'   => $c['field'] ?? '',
                    'comment' => $c['comment'] ?? $c['field'] ?? '',
                ];
            }
            return $this->success('', ['list' => $list]);
        } catch (\Throwable $e) {
            return $this->error('获取字段失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成命令行 / 立即执行
     * action=command：只返回生成概要，不执行
     * action=execute：执行并写入记录，返回结果
     */
    public function command(): Response
    {
        if ($err = $this->checkSuperAdmin()) {
            return $err;
        }
        $action = $this->request->post('action', $this->request->get('action', ''));
        if ($action === 'command') {
            return $this->buildCommandPreview();
        }
        if ($action === 'execute') {
            return $this->doExecute();
        }
        return $this->error('参数错误');
    }

    /** 仅生成命令行/概要文案 */
    protected function buildCommandPreview(): Response
    {
        $params = $this->collectParams();
        $table = $params['table'] ?? '';
        $sqlFile = $params['sql_file'] ?? '';
        if ($table === '' && $sqlFile === '') {
            return $this->error('请选择数据表或 SQL 文件');
        }
        $app = $params['app'] ?? 'admin';
        $withMenu = !empty($params['with_menu']);
        $tableName = $table ?: '(从 SQL 解析)';
        $lines = [
            'php think crud -t ' . $tableName . ' -a ' . $app,
            '将生成：控制器、模型、视图(index/add/edit)、backend JS',
        ];
        if ($withMenu) {
            $lines[] = '并写入系统管理菜单';
        }
        $command = implode("\n", $lines);
        return $this->success('', ['command' => $command]);
    }

    /** 执行生成并写库，返回结果 */
    protected function doExecute(): Response
    {
        $params = $this->collectParams();
        $table = $params['table'] ?? '';
        $sqlFile = $params['sql_file'] ?? '';
        if ($table === '' && $sqlFile === '') {
            return $this->error('请选择数据表或 SQL 文件');
        }
        $app = $params['app'] ?? 'admin';
        $ignore = $params['ignore'] ?? '';
        $withMenu = !empty($params['with_menu']);
        $sqlContent = '';
        if ($sqlFile !== '') {
            $path = root_path() . str_replace(['../', '..\\'], '', $sqlFile);
            if (!is_file($path) || !str_ends_with(strtolower($path), '.sql')) {
                return $this->error('所选 SQL 文件无效');
            }
            $sqlContent = (string) file_get_contents($path);
        }

        $commandPreview = trim($params['command_preview'] ?? '');
        if ($commandPreview === '') {
            $commandPreview = '一键生成CRUD：' . ($table ?: 'SQL') . ' @ ' . $app;
        }

        $start = time();
        $out = [];
        try {
            $gen = new CrudGenerator();
            $result = $gen->generate($table, $app, $ignore, '', $withMenu, $sqlContent);
            if (!$result['success']) {
                $out[] = '失败：' . $result['message'];
                $this->saveCommand('crud', $commandPreview, $params, implode("\n", $out), 0, $start);
                return $this->success('', ['result' => implode("\n", $out), 'status' => 0]);
            }
            $out[] = $result['message'];
            foreach ($result['files'] ?? [] as $f) {
                $out[] = '  - ' . $f;
            }
            $content = implode("\n", $out);
            $this->saveCommand('crud', $commandPreview, $params, $content, 1, $start);
            return $this->success('', ['result' => $content, 'status' => 1]);
        } catch (\Throwable $e) {
            $out[] = '异常：' . $e->getMessage();
            $content = implode("\n", $out);
            $this->saveCommand('crud', $commandPreview, $params, $content, 0, $start);
            return $this->success('', ['result' => $content, 'status' => 0]);
        }
    }

    protected function collectParams(): array
    {
        $table = trim((string) $this->request->post('table', ''));
        $app = trim((string) $this->request->post('app', 'admin'));
        if ($app === '') {
            $app = 'admin';
        }
        $ignore = trim((string) $this->request->post('ignore', ''));
        $withMenu = $this->request->post('with_menu') === '1' || $this->request->post('menu') === '1';
        $sqlFile = trim((string) $this->request->post('sql_file', ''));
        return [
            'table'   => $table,
            'app'     => $app,
            'ignore'  => $ignore,
            'with_menu' => $withMenu,
            'sql_file' => $sqlFile,
        ];
    }

    protected function saveCommand(string $type, string $command, array $params, string $content, int $status, int $executetime): void
    {
        $m = new CommandModel();
        $m->save([
            'type'        => $type,
            'command'    => $command,
            'params'     => json_encode($params, JSON_UNESCAPED_UNICODE),
            'content'    => $content,
            'status'     => $status,
            'executetime' => $executetime,
            'create_time' => time(),
            'update_time' => time(),
        ]);
    }

    /** 详情（单条记录） */
    public function detail(): Response
    {
        if ($err = $this->checkSuperAdmin()) {
            return $err;
        }
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = CommandModel::find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $data = $row->toArray();
        $data['type_text'] = CommandModel::typeText($data['type'] ?? '');
        $data['status_text'] = CommandModel::statusText((int) ($data['status'] ?? 0));
        if (!empty($data['params'])) {
            $data['params'] = is_string($data['params']) ? json_decode($data['params'], true) : $data['params'];
        }
        return $this->success('', $data);
    }

    /** 再次执行（根据记录 params 重新执行） */
    public function reExecute(): Response
    {
        if ($err = $this->checkSuperAdmin()) {
            return $err;
        }
        $id = (int) $this->request->post('id', $this->request->get('id', 0));
        if ($id <= 0) {
            return $this->error('参数错误');
        }
        $row = CommandModel::find($id);
        if (!$row) {
            return $this->error('记录不存在');
        }
        $params = $row['params'];
        if (is_string($params)) {
            $params = json_decode($params, true) ?: [];
        }
        $table = $params['table'] ?? '';
        $sqlFile = $params['sql_file'] ?? '';
        if ($table === '' && $sqlFile === '') {
            return $this->error('原记录无有效参数');
        }
        $app = $params['app'] ?? 'admin';
        $ignore = $params['ignore'] ?? '';
        $withMenu = !empty($params['with_menu']);
        $sqlContent = '';
        if (!empty($sqlFile)) {
            $path = root_path() . str_replace(['../', '..\\'], '', $sqlFile);
            $sqlContent = is_file($path) ? (string) file_get_contents($path) : '';
        }
        $start = time();
        $out = [];
        try {
            $gen = new CrudGenerator();
            $result = $gen->generate($table, $app, $ignore, '', $withMenu, $sqlContent);
            if (!$result['success']) {
                $out[] = '失败：' . $result['message'];
                $this->saveCommand('crud', $row['command'] ?? '再次执行', $params, implode("\n", $out), 0, $start);
                return $this->success('', ['result' => implode("\n", $out), 'status' => 0]);
            }
            $out[] = $result['message'];
            foreach ($result['files'] ?? [] as $f) {
                $out[] = '  - ' . $f;
            }
            $content = implode("\n", $out);
            $this->saveCommand('crud', $row['command'] ?? '再次执行', $params, $content, 1, $start);
            return $this->success('', ['result' => $content, 'status' => 1]);
        } catch (\Throwable $e) {
            $out[] = '异常：' . $e->getMessage();
            $content = implode("\n", $out);
            $this->saveCommand('crud', $row['command'] ?? '再次执行', $params, $content, 0, $start);
            return $this->success('', ['result' => $content, 'status' => 0]);
        }
    }

    /** 删除一条或批量 */
    public function del(): Response
    {
        if ($err = $this->checkSuperAdmin()) {
            return $err;
        }
        $ids = $this->request->post('ids');
        if (is_array($ids)) {
            $ids = array_filter(array_map('intval', $ids));
        } else {
            $ids = [(int) $ids];
        }
        $ids = array_filter($ids);
        if (empty($ids)) {
            return $this->error('请选择要删除的记录');
        }
        CommandModel::destroy($ids);
        return $this->success('删除成功');
    }

    /** 兼容：原 tables/sqlFiles/generate 保留，add 页和再次执行会用到 */
    public function tables(): Response
    {
        if ($err = $this->checkSuperAdmin()) {
            return $err;
        }
        try {
            $gen = new CrudGenerator();
            $list = $gen->getTables();
            return $this->success('', ['total' => count($list), 'list' => $list]);
        } catch (\Throwable $e) {
            return $this->error('获取表列表失败: ' . $e->getMessage());
        }
    }

    public function sqlFiles(): Response
    {
        if ($err = $this->checkSuperAdmin()) {
            return $err;
        }
        try {
            $gen = new CrudGenerator();
            $list = $gen->getSqlFiles();
            return $this->success('', ['total' => count($list), 'list' => $list]);
        } catch (\Throwable $e) {
            return $this->error('获取 SQL 文件列表失败: ' . $e->getMessage());
        }
    }
}
