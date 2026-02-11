<?php
declare(strict_types=1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;

class Crud extends Command
{
    protected function configure(): void
    {
        $this->setName('crud')->setDescription('生成CRUD代码')
            ->addOption('table', 't', Option::VALUE_REQUIRED, '数据表名(不含前缀)')
            ->addOption('app', null, Option::VALUE_OPTIONAL, '应用名', 'admin')
            ->addOption('ignore', null, Option::VALUE_OPTIONAL, '忽略字段', '')
            ->addOption('template', null, Option::VALUE_OPTIONAL, '模板路径', '');
    }

    protected function execute(Input $input, Output $output): int
    {
        $table = $input->getOption('table');
        if (empty($table)) {
            $output->writeln('<error>请指定 -t 表名</error>');
            return 1;
        }
        $prefix = config('database.connections.mysql.prefix') ?? 'fa_';
        $fullTable = $prefix . $table;
        try {
            Db::getTableFields($fullTable);
        } catch (\Throwable $e) {
            $output->writeln('<error>表不存在: ' . $fullTable . '</error>');
            return 1;
        }
        $tableName = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
        $tplPath = $input->getOption('template') ?: (root_path() . 'template' . DIRECTORY_SEPARATOR);
        $appPath = app_path() . $input->getOption('app') . DIRECTORY_SEPARATOR;
        $vars = ['{$table}' => $table, '{$tableName}' => $tableName, '{$TableName}' => ucfirst($tableName)];

        $fields = Db::getTableFields($fullTable);
        $hasTenant = in_array('tenant_id', $fields, true);
        $controllerTpl = $tplPath . 'controller' . DIRECTORY_SEPARATOR . ($hasTenant ? 'Base.tpl' : 'BaseNoTenant.tpl');
        if (is_file($controllerTpl)) {
            $content = str_replace(array_keys($vars), array_values($vars), file_get_contents($controllerTpl));
            file_put_contents($appPath . 'controller' . DIRECTORY_SEPARATOR . $tableName . '.php', $content);
            $output->writeln('控制器已生成');
        }
        $modelTpl = $tplPath . 'model' . DIRECTORY_SEPARATOR . ($hasTenant ? 'Base.tpl' : 'BaseNoTenant.tpl');
        if (is_file($modelTpl)) {
            $content = str_replace(array_keys($vars), array_values($vars), file_get_contents($modelTpl));
            file_put_contents($appPath . 'model' . DIRECTORY_SEPARATOR . $tableName . 'Model.php', $content);
            $output->writeln('模型已生成');
        }

        $viewDir = $appPath . 'view' . DIRECTORY_SEPARATOR . $table . DIRECTORY_SEPARATOR;
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0755, true);
            $indexHtml = '<div class="card panel-intro"><div class="card-header"><div class="panel-lead"><em>' . $tableName . '管理</em></div></div><div class="card-body"><div id="toolbar" class="toolbar mb-2"><a href="javascript:;" class="btn btn-primary btn-refresh"><i class="fas fa-sync-alt"></i> 刷新</a><a href="{:url(\'' . $table . '/add\')}" class="btn btn-success btn-add"><i class="fas fa-plus"></i> 添加</a><a href="javascript:;" class="btn btn-success btn-edit btn-disabled disabled"><i class="fas fa-edit"></i> 编辑</a><a href="javascript:;" class="btn btn-danger btn-del btn-disabled disabled"><i class="fas fa-trash-alt"></i> 删除</a></div><table id="table" class="table table-striped table-bordered table-hover" width="100%"></table></div></div>';
            file_put_contents($viewDir . 'index.html', $indexHtml);
            file_put_contents($viewDir . 'add.html', '<div class="card"><div class="card-header">添加' . $tableName . '</div><div class="card-body"><p>请根据表结构编辑 add.html 表单项</p></div></div>');
            file_put_contents($viewDir . 'edit.html', '<div class="card"><div class="card-header">编辑' . $tableName . '</div><div class="card-body"><p>请根据表结构编辑 edit.html 表单项</p></div></div>');
            $output->writeln('视图占位已生成: ' . $table . '/index,add,edit');
        }

        $output->writeln('<info>CRUD生成完成</info>');
        return 0;
    }
}
