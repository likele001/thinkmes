<?php
declare(strict_types=1);

namespace app\admin\controller;

use think\facade\Db;
use think\facade\View;
use think\Response;

class Backup extends Backend
{
    protected function backupDir(): string
    {
        $dir = rtrim((string) runtime_path(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'db_backups';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        return $dir;
    }

    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        $offsetParam = $this->request->get('offset');
        $isDataRequest = ($limitParam !== null && $limitParam !== '') || ($offsetParam !== null && $offsetParam !== '');
        if (!$isDataRequest && !$this->request->isAjax()) {
            View::assign('title', '数据备份');
            return $this->fetchWithLayout('backup/index');
        }
        $dir = $this->backupDir();
        $files = [];
        foreach (glob($dir . '/*.sql') ?: [] as $path) {
            $stat = @stat($path);
            if (!$stat) continue;
            $files[] = [
                'name' => basename($path),
                'size' => (int) ($stat['size'] ?? 0),
                'mtime' => (int) ($stat['mtime'] ?? 0),
            ];
        }
        usort($files, fn ($a, $b) => ($b['mtime'] <=> $a['mtime']) ?: strcmp((string) $a['name'], (string) $b['name']));

        $limit = max(1, min(100, (int) $this->request->get('limit', 20)));
        $offset = (int) $this->request->get('offset', 0);
        $slice = array_slice($files, $offset, $limit);
        foreach ($slice as &$row) {
            $row['size_text'] = $row['size'] >= 1024 * 1024 ? round($row['size'] / 1024 / 1024, 2) . ' MB' : round($row['size'] / 1024, 2) . ' KB';
            $row['mtime_text'] = $row['mtime'] ? date('Y-m-d H:i:s', $row['mtime']) : '';
        }
        return $this->success('', ['total' => count($files), 'list' => $slice]);
    }

    public function create(): Response
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $dir = $this->backupDir();
        $cfg = config('database.connections.mysql');
        $db = (string) ($cfg['database'] ?? '');
        if ($db === '') return $this->error('数据库配置缺失');

        $mysqldump = trim((string) @shell_exec('command -v mysqldump 2>/dev/null'));
        if ($mysqldump === '') return $this->error('mysqldump 不可用');

        $host = (string) ($cfg['hostname'] ?? '127.0.0.1');
        $port = (string) ($cfg['hostport'] ?? '3306');
        $user = (string) ($cfg['username'] ?? '');
        $pass = (string) ($cfg['password'] ?? '');
        $charset = (string) ($cfg['charset'] ?? 'utf8mb4');

        $filename = 'db_' . $db . '_' . date('YmdHis') . '.sql';
        $target = $dir . DIRECTORY_SEPARATOR . $filename;

        $tmp = $dir . DIRECTORY_SEPARATOR . '.mysqldump.' . uniqid('', true) . '.cnf';
        $cnf = "[client]\nuser={$user}\npassword={$pass}\nhost={$host}\nport={$port}\n";
        file_put_contents($tmp, $cnf);
        @chmod($tmp, 0600);

        $cmd = implode(' ', [
            escapeshellcmd($mysqldump),
            '--defaults-extra-file=' . escapeshellarg($tmp),
            '--single-transaction',
            '--quick',
            '--routines',
            '--events',
            '--triggers',
            '--skip-comments',
            '--set-charset',
            '--default-character-set=' . escapeshellarg($charset),
            escapeshellarg($db),
        ]);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['file', $target, 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($proc)) {
            @unlink($tmp);
            return $this->error('备份启动失败');
        }
        fclose($pipes[0]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $code = proc_close($proc);
        @unlink($tmp);
        if ($code !== 0) {
            @unlink($target);
            return $this->error('备份失败：' . trim((string) $stderr));
        }
        return $this->success('备份成功', ['file' => $filename]);
    }

    public function download(): Response
    {
        $name = basename((string) $this->request->get('name', ''));
        if ($name === '' || strpos($name, '..') !== false) return $this->error('参数错误');
        $path = $this->backupDir() . DIRECTORY_SEPARATOR . $name;
        if (!is_file($path)) return $this->error('文件不存在');
        $content = file_get_contents($path);
        if ($content === false) return $this->error('读取失败');
        return response($content, 200, [
            'Content-Type' => 'application/sql; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);
    }

    public function del(): Response
    {
        if (!$this->request->isPost()) return $this->error('非法请求');
        $name = basename((string) $this->request->post('name', ''));
        if ($name === '' || strpos($name, '..') !== false) return $this->error('参数错误');
        $path = $this->backupDir() . DIRECTORY_SEPARATOR . $name;
        if (!is_file($path)) return $this->error('文件不存在');
        @unlink($path);
        return $this->success('已删除');
    }
}
