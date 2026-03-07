<?php
declare(strict_types=1);

namespace app\command;

use app\common\service\CrudGenerator;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Crud extends Command
{
    protected function configure(): void
    {
        $this->setName('crud')->setDescription('生成CRUD代码')
            ->addOption('table', 't', Option::VALUE_REQUIRED, '数据表名(不含前缀)')
            ->addOption('app', null, Option::VALUE_OPTIONAL, '应用名', 'admin')
            ->addOption('ignore', null, Option::VALUE_OPTIONAL, '忽略字段', '')
            ->addOption('template', null, Option::VALUE_OPTIONAL, '模板路径', '')
            ->addOption('menu', 'u', Option::VALUE_OPTIONAL, '1=同时生成菜单', '0');
    }

    protected function execute(Input $input, Output $output): int
    {
        $table = $input->getOption('table');
        if (empty($table)) {
            $output->writeln('<error>请指定 -t 表名</error>');
            return 1;
        }
        $gen = new CrudGenerator();
        $withMenu = $input->getOption('menu') === '1' || $input->getOption('menu') === true;
        $result = $gen->generate(
            $table,
            (string) $input->getOption('app'),
            (string) $input->getOption('ignore'),
            (string) $input->getOption('template'),
            $withMenu
        );
        if (!$result['success']) {
            $output->writeln('<error>' . $result['message'] . '</error>');
            return 1;
        }
        foreach ($result['files'] as $f) {
            $output->writeln('  ' . $f);
        }
        $output->writeln('<info>CRUD生成完成</info>');
        return 0;
    }
}
