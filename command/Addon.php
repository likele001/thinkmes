<?php
declare(strict_types=1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\facade\Db;

class Addon extends Command
{
    protected function configure(): void
    {
        $this->setName('addon')->setDescription('插件管理')
            ->addArgument('action', Argument::REQUIRED, 'install|uninstall|enable|disable')
            ->addArgument('name', Argument::REQUIRED, '插件名');
    }

    protected function execute(Input $input, Output $output): int
    {
        $action = $input->getArgument('action');
        $name = $input->getArgument('name');
        $path = config('addon.addons_path') . $name . DIRECTORY_SEPARATOR;
        if (!in_array($action, ['install', 'uninstall', 'enable', 'disable'], true)) {
            $output->writeln('<error>action 仅支持 install|uninstall|enable|disable</error>');
            return 1;
        }
        if ($action === 'install' && !is_dir($path)) {
            $output->writeln('<error>插件目录不存在</error>');
            return 1;
        }
        if (($action === 'install' || $action === 'uninstall') && is_file($path . $action . '.sql')) {
            $sql = file_get_contents($path . $action . '.sql');
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                if ($stmt !== '') {
                    try {
                        Db::execute($stmt);
                    } catch (\Throwable $e) {
                        $output->writeln('<comment>' . $e->getMessage() . '</comment>');
                    }
                }
            }
        }
        if ($action === 'install' && is_file($path . 'bootstrap.php')) {
            (function () use ($path) {
                include $path . 'bootstrap.php';
            })();
            $output->writeln('已加载插件 bootstrap（钩子注册）');
        }
        $output->writeln('<info>插件 ' . $name . ' ' . $action . ' 完成</info>');
        return 0;
    }
}
