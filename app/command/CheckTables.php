<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;

class CheckTables extends Command
{
    protected function configure()
    {
        $this->setName('check:tables')
            ->setDescription('Check MES tables');
    }

    protected function execute(Input $input, Output $output)
    {
        $tables = [
            'fa_mes_order',
            'fa_mes_order_model',
            'fa_mes_bom',
            'fa_mes_bom_item',
            'fa_mes_material',
            'fa_mes_order_material',
            'fa_mes_customer',
            'fa_mes_supplier'
        ];

        foreach ($tables as $table) {
            try {
                $columns = Db::query("SHOW COLUMNS FROM {$table}");
                $output->writeln("Table: {$table}");
                foreach ($columns as $column) {
                    $output->writeln("  - {$column['Field']} ({$column['Type']})");
                }
            } catch (\Exception $e) {
                $output->writeln("Table: {$table} - Error: " . $e->getMessage());
            }
        }
    }
}
