<?php
declare(strict_types=1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Cache;

class Clear extends Command
{
    protected function configure(): void
    {
        $this->setName('cache:clear')
            ->setDescription('清理缓存(权限/配置/模板)');
    }

    protected function execute(Input $input, Output $output): int
    {
        try {
            Cache::clear();
            $output->writeln('<info>缓存已清理</info>');
        } catch (\Throwable $e) {
            $output->writeln('<comment>' . $e->getMessage() . '</comment>');
        }
        $runtimePath = runtime_path();
        $dirs = ['temp', 'cache'];
        foreach ($dirs as $dir) {
            $path = $runtimePath . $dir;
            if (is_dir($path)) {
                $this->rmDir($path);
                $output->writeln('已清理: ' . $path);
            }
        }
        return 0;
    }

    protected function rmDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->rmDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
