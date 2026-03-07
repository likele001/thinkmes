<?php
declare(strict_types=1);

namespace app\common\service;

use think\facade\Db;

/**
 * CRUD 代码生成服务（FastAdmin 风格：表/字段注释 → 列表列与表单控件）
 */
class CrudGenerator
{
    protected string $prefix = 'fa_';
    protected string $tplPath;
    protected string $appPath;

    /** 表单中不展示的字段（自动维护或主键） */
    protected array $formSkipFields = ['id', 'tenant_id', 'create_time', 'update_time', 'deletetime'];

    public function __construct()
    {
        $this->prefix = config('database.connections.mysql.prefix') ?? 'fa_';
        $this->tplPath = root_path() . 'template' . DIRECTORY_SEPARATOR;
        $this->appPath = app_path();
    }

    /**
     * 获取项目 database 目录下的 SQL 文件列表（供「选择 SQL 文件」用）
     * @return array [['path'=>'database/xxx.sql', 'name'=>'xxx.sql']]
     */
    public function getSqlFiles(): array
    {
        $dir = root_path() . 'database' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            return [];
        }
        $list = [];
        foreach (scandir($dir) ?: [] as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            if (str_ends_with(strtolower($f), '.sql')) {
                $list[] = ['path' => 'database/' . $f, 'name' => $f];
            }
        }
        usort($list, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $list;
    }

    /**
     * 从 CREATE TABLE 语句中解析表名、表注释、字段列表
     * @return array{table: string, comment: string, columns: array}|null 解析失败返回 null
     */
    public function parseCreateTableSql(string $sql): ?array
    {
        $sql = preg_replace('/\s+/', ' ', trim($sql));
        if (!preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`]?(\w+)[`]?\s*\(/i', $sql, $m)) {
            return null;
        }
        $fullTable = $m[1];
        $prefix = $this->prefix;
        $table = (strpos($fullTable, $prefix) === 0) ? substr($fullTable, strlen($prefix)) : $fullTable;

        $tableComment = '';
        if (preg_match('/\)\s*[^)]*COMMENT\s*=\s*[\'"]([^\'"]*)[\'"]/i', $sql, $cm)) {
            $tableComment = $cm[1];
        }

        $start = strpos($sql, '(', strpos($sql, $fullTable));
        $end = strrpos($sql, ')');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }
        $colBlock = substr($sql, $start + 1, $end - $start - 1);
        $columns = [];
        $lines = preg_split('/,\s*(?=[`a-zA-Z_])/', $colBlock);
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(PRIMARY\s+KEY|KEY\s+|UNIQUE\s+|CONSTRAINT\s+)/i', $line)) {
                continue;
            }
            if (!preg_match('/^[`]?(\w+)[`]?\s+(\S+)(?:\s+.*)?/', $line, $col)) {
                continue;
            }
            $field = $col[1];
            $type = $col[2];
            if (preg_match('/\bunsigned\b/i', $line)) {
                $type .= ' unsigned';
            }
            $comment = '';
            if (preg_match('/COMMENT\s+[\'"]([^\'"]*)[\'"]/i', $line, $cc)) {
                $comment = $cc[1];
            }
            $columns[] = [
                'field'   => $field,
                'type'    => $type,
                'comment' => $comment,
                'null'    => '',
                'default' => null,
                'key'     => '',
            ];
        }

        return ['table' => $table, 'comment' => $tableComment, 'columns' => $columns];
    }

    /**
     * 获取所有数据表名（不含前缀）
     */
    public function getTables(): array
    {
        $fullPrefix = $this->prefix;
        $tables = Db::query("SHOW TABLE STATUS LIKE ?", [$fullPrefix . '%']);
        $list = [];
        $len = strlen($fullPrefix);
        foreach ($tables as $row) {
            $row = array_change_key_case((array) $row, CASE_LOWER);
            $name = $row['name'] ?? '';
            if ($name === '' || substr($name, 0, $len) !== $fullPrefix) {
                continue;
            }
            $list[] = [
                'table'   => substr($name, $len),
                'comment' => $row['comment'] ?? '',
            ];
        }
        usort($list, fn($a, $b) => strcmp($a['table'], $b['table']));
        return $list;
    }

    /**
     * 获取表注释
     */
    public function getTableComment(string $fullTable): string
    {
        $rows = Db::query("SHOW TABLE STATUS WHERE Name = ?", [$fullTable]);
        if (empty($rows)) {
            return '';
        }
        $row = array_change_key_case((array) $rows[0], CASE_LOWER);
        return (string) ($row['comment'] ?? '');
    }

    /**
     * 获取表字段完整信息（含类型、注释）
     * @return array [['field'=>'','type'=>'','comment'=>'','null'=>'','default'=>'']]
     */
    public function getFullColumns(string $fullTable): array
    {
        $safeTable = '`' . str_replace('`', '``', $fullTable) . '`';
        $rows = Db::query("SHOW FULL COLUMNS FROM " . $safeTable);
        $list = [];
        foreach ($rows as $r) {
            $r = array_change_key_case((array) $r, CASE_LOWER);
            $list[] = [
                'field'   => $r['field'] ?? '',
                'type'    => $r['type'] ?? '',
                'comment' => $r['comment'] ?? '',
                'null'    => $r['null'] ?? '',
                'default' => $r['default'] ?? null,
                'key'     => $r['key'] ?? '',
            ];
        }
        return $list;
    }

    /**
     * 根据字段名、类型、注释 映射为表单控件类型（FastAdmin 风格）
     * control: input|textarea|select|switch|date|datetime|hidden|number|editor
     * list_show: 是否在列表中显示
     */
    public function buildFieldConfigs(array $columns, bool $hasTenant, array $ignoreList = []): array
    {
        $configs = [];
        foreach ($columns as $col) {
            $field = $col['field'] ?? '';
            if ($field === '') {
                continue;
            }
            if (in_array($field, $ignoreList, true)) {
                continue;
            }
            $type = strtolower($col['type'] ?? '');
            $comment = trim($col['comment'] ?? '');
            $label = $comment !== '' ? $comment : $field;

            $listShow = true;
            $control = 'input';
            $options = []; // for select

            if (in_array($field, $this->formSkipFields, true)) {
                $control = 'hidden';
                $listShow = !in_array($field, ['create_time', 'update_time'], true);
                if ($field === 'id') {
                    $listShow = true;
                }
            } elseif ($field === 'status') {
                $control = 'select';
                $options = [['value' => '1', 'text' => '正常'], ['value' => '0', 'text' => '禁用']];
            } elseif (preg_match('/\b(weigh|sort)\b/i', $field) || $field === 'weigh') {
                $control = 'number';
            } elseif (preg_match('/(image|img|pic|avatar|cover)(s|_)?$/i', $field)) {
                $control = 'image';
            } elseif (preg_match('/(content|content_[\w]+|intro|desc|description|remark)$/i', $field) || preg_match('/^text\b/i', $type)) {
                $control = 'textarea';
                if (preg_match('/content$/i', $field)) {
                    $control = 'editor';
                }
            } elseif (preg_match('/\b(file|attachment)(s|_)?$/i', $field)) {
                $control = 'file';
            } elseif (preg_match('/\b(date|time)\b/i', $type) || preg_match('/(_at|_time|date|time)$/i', $field)) {
                $control = (strpos($type, 'datetime') !== false || strpos($field, 'datetime') !== false) ? 'datetime' : 'date';
                $listShow = true;
            } elseif (preg_match('/^(tinyint|smallint|mediumint|int|bigint)/', $type)) {
                if (preg_match('/\(1\)/', $type) || in_array(strtolower($field), ['status', 'state', 'is_show', 'enabled'], true)) {
                    $control = 'select';
                    $options = [['value' => '0', 'text' => '否'], ['value' => '1', 'text' => '是']];
                } else {
                    $control = 'number';
                }
            } elseif (preg_match('/^(decimal|float|double)/', $type)) {
                $control = 'number';
            }

            $configs[] = [
                'field'    => $field,
                'label'    => $label,
                'control'  => $control,
                'list_show' => $listShow,
                'options'  => $options,
                'type_raw' => $type,
            ];
        }
        return $configs;
    }

    /**
     * 生成表单 HTML（add/edit 用）
     */
    protected function buildFormHtml(array $fieldConfigs, string $tableName, string $table, bool $isEdit): string
    {
        $lines = [];
        foreach ($fieldConfigs as $f) {
            if ($f['control'] === 'hidden') {
                if ($f['field'] === 'id' && $isEdit) {
                    $lines[] = '<input type="hidden" name="id" value="{$data.id|default=\'\'}">';
                }
                continue;
            }
            $name = $f['field'];
            $label = htmlspecialchars($f['label']);
            $val = $isEdit ? '{$data.' . $name . '|default=\'\'}' : '';
            $input = '';
            switch ($f['control']) {
                case 'select':
                    $input = '<select name="' . $name . '" class="form-control">';
                    foreach ($f['options'] as $opt) {
                        $v = $opt['value'];
                        $t = $opt['text'];
                        $sel = $isEdit ? '{if isset($data.' . $name . ') && (string)$data.' . $name . '==\'' . addslashes($v) . '\'}selected{/if}' : '';
                        $input .= '<option value="' . htmlspecialchars($v) . '" ' . $sel . '>' . htmlspecialchars($t) . '</option>';
                    }
                    $input .= '</select>';
                    break;
                case 'textarea':
                case 'editor':
                    $input = '<textarea name="' . $name . '" class="form-control" rows="4" placeholder="' . $label . '">' . ($isEdit ? $val : '') . '</textarea>';
                    break;
                case 'number':
                    $input = '<input type="number" name="' . $name . '" class="form-control" value="' . ($isEdit ? $val : '') . '">';
                    break;
                case 'date':
                    $input = '<input type="date" name="' . $name . '" class="form-control" value="' . ($isEdit ? $val : '') . '">';
                    break;
                case 'datetime':
                    $input = '<input type="datetime-local" name="' . $name . '" class="form-control" value="' . ($isEdit ? $val : '') . '">';
                    break;
                case 'image':
                case 'file':
                    $input = '<input type="text" name="' . $name . '" class="form-control" placeholder="URL或路径" value="' . ($isEdit ? $val : '') . '">';
                    break;
                default:
                    $input = '<input type="text" name="' . $name . '" class="form-control" placeholder="' . $label . '" value="' . ($isEdit ? $val : '') . '">';
            }
            $lines[] = '<div class="form-group row"><label class="col-sm-2 col-form-label">' . $label . '</label><div class="col-sm-6">' . $input . '</div></div>';
        }
        $lines[] = '<div class="form-group row"><div class="col-sm-6 offset-sm-2"><button type="submit" class="btn btn-primary">提交</button><a href="{:url(\'' . $table . '/index\')}" class="btn btn-default ml-2">返回</a></div></div>';
        return implode("\n            ", $lines);
    }

    /**
     * 生成 Bootstrap Table 的 columns 配置（JS 数组项）
     */
    protected function buildTableColumnsJs(array $fieldConfigs, string $table): string
    {
        $items = [];
        foreach ($fieldConfigs as $f) {
            if (!($f['list_show'] ?? true)) {
                continue;
            }
            $field = $f['field'];
            $title = addslashes($f['label']);
            if ($field === 'status') {
                $items[] = "{ field: '" . $field . "', title: '" . $title . "', formatter: function(v){ return v == 1 ? '正常' : '禁用'; } }";
            } elseif (in_array($f['control'], ['date', 'datetime'], true) && ($f['type_raw'] ?? '') !== '') {
                $items[] = "{ field: '" . $field . "', title: '" . $title . "' }";
            } else {
                $items[] = "{ field: '" . $field . "', title: '" . $title . "' }";
            }
        }
        $items[] = "{ field: 'id', title: '操作', formatter: function(v){ return '<a class=\"btn btn-xs btn-primary\" href=\"' + editUrl + '?id=' + v + '\">编辑</a> <button class=\"btn btn-xs btn-danger btn-row-del\" data-id=\"'+v+'\" type=\"button\">删除</button>'; } }";
        return implode(",\n                ", $items);
    }

    /**
     * 生成 CRUD（含动态表单、列表列、backend JS）
     * @param string $table 表名（不含前缀），当 $sqlContent 有值时可为空（从 SQL 解析）
     * @param string $sqlContent 可选，CREATE TABLE 的 SQL 内容；有则从 SQL 解析表结构，不读库
     */
    public function generate(string $table, string $app = 'admin', string $ignore = '', string $template = '', bool $withMenu = false, string $sqlContent = ''): array
    {
        $ignoreList = array_filter(array_map('trim', explode(',', $ignore)));

        if ($sqlContent !== '') {
            $parsed = $this->parseCreateTableSql($sqlContent);
            if ($parsed === null) {
                return ['success' => false, 'message' => '无法从 SQL 中解析出 CREATE TABLE（请确保包含完整建表语句）', 'files' => []];
            }
            $table = $parsed['table'];
            $tableComment = $parsed['comment'];
            $columns = $parsed['columns'];
            $hasTenant = false;
            foreach ($columns as $c) {
                if (($c['field'] ?? '') === 'tenant_id') {
                    $hasTenant = true;
                    break;
                }
            }
        } else {
            $table = trim($table);
            if ($table === '') {
                return ['success' => false, 'message' => '请选择数据表或选择 SQL 文件', 'files' => []];
            }
            $fullTable = $this->prefix . $table;
            try {
                Db::getTableFields($fullTable);
            } catch (\Throwable $e) {
                return ['success' => false, 'message' => '表不存在: ' . $fullTable, 'files' => []];
            }
            $columns = $this->getFullColumns($fullTable);
            $tableComment = $this->getTableComment($fullTable);
            $fields = Db::getTableFields($fullTable);
            $hasTenant = in_array('tenant_id', $fields, true);
        }

        $tableName = str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
        $TableName = ucfirst($tableName);
        $fieldConfigs = $this->buildFieldConfigs($columns, $hasTenant, $ignoreList);

        $tplPath = $template !== '' ? rtrim($template, '/\\') . DIRECTORY_SEPARATOR : $this->tplPath;
        $appPath = $this->appPath . $app . DIRECTORY_SEPARATOR;
        $title = $tableComment !== '' ? $tableComment : ($tableName . '管理');
        $vars = [
            '{$table}'     => $table,
            '{$tableName}' => $tableName,
            '{$TableName}' => $TableName,
            '{$title}'     => $title,
        ];

        $files = [];

        // 控制器
        $controllerTpl = $tplPath . 'controller' . DIRECTORY_SEPARATOR . ($hasTenant ? 'Base.tpl' : 'BaseNoTenant.tpl');
        if (is_file($controllerTpl)) {
            $content = str_replace(array_keys($vars), array_values($vars), file_get_contents($controllerTpl));
            $controllerFile = $appPath . 'controller' . DIRECTORY_SEPARATOR . $TableName . '.php';
            if (!is_dir(dirname($controllerFile))) {
                mkdir(dirname($controllerFile), 0755, true);
            }
            file_put_contents($controllerFile, $content);
            $files[] = 'controller/' . $TableName . '.php';
        }

        // 模型
        $modelTpl = $tplPath . 'model' . DIRECTORY_SEPARATOR . ($hasTenant ? 'Base.tpl' : 'BaseNoTenant.tpl');
        if (is_file($modelTpl)) {
            $content = str_replace(array_keys($vars), array_values($vars), file_get_contents($modelTpl));
            $modelFile = $appPath . 'model' . DIRECTORY_SEPARATOR . $TableName . 'Model.php';
            if (!is_dir(dirname($modelFile))) {
                mkdir(dirname($modelFile), 0755, true);
            }
            file_put_contents($modelFile, $content);
            $files[] = 'model/' . $TableName . 'Model.php';
        }

        // 视图目录
        $viewDir = $appPath . 'view' . DIRECTORY_SEPARATOR . $table . DIRECTORY_SEPARATOR;
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0755, true);
        }

        // index.html
        $indexHtml = '<div class="card panel-intro"><div class="card-header"><div class="panel-lead"><em>' . htmlspecialchars($title) . '</em></div></div><div class="card-body">'
            . '<div id="toolbar" class="toolbar mb-2">'
            . '<a href="javascript:;" class="btn btn-primary btn-refresh"><i class="fas fa-sync-alt"></i> 刷新</a> '
            . '<a href="{:url(\'' . $table . '/add\')}" class="btn btn-success btn-add"><i class="fas fa-plus"></i> 添加</a> '
            . '<a href="javascript:;" class="btn btn-success btn-edit btn-disabled disabled"><i class="fas fa-edit"></i> 编辑</a> '
            . '<a href="javascript:;" class="btn btn-danger btn-del btn-disabled disabled"><i class="fas fa-trash-alt"></i> 删除</a>'
            . '</div><table id="table" class="table table-striped table-bordered table-hover" width="100%"></table></div></div>';
        file_put_contents($viewDir . 'index.html', $indexHtml);
        $files[] = 'view/' . $table . '/index.html';

        // add.html（动态表单）
        $formAdd = $this->buildFormHtml($fieldConfigs, $tableName, $table, false);
        $addHtml = '<div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title">添加' . htmlspecialchars($title) . '</h3></div><div class="card-body"><form id="form-add" method="post" class="form-horizontal">' . $formAdd . '</form></div></div>';
        file_put_contents($viewDir . 'add.html', $addHtml);
        $files[] = 'view/' . $table . '/add.html';

        // edit.html（动态表单）
        $formEdit = $this->buildFormHtml($fieldConfigs, $tableName, $table, true);
        $editHtml = '<div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title">编辑' . htmlspecialchars($title) . '</h3></div><div class="card-body"><form id="form-edit" method="post" class="form-horizontal">' . $formEdit . '</form></div></div>';
        file_put_contents($viewDir . 'edit.html', $editHtml);
        $files[] = 'view/' . $table . '/edit.html';

        // backend JS（Bootstrap Table 列 + 事件）
        $columnsJs = $this->buildTableColumnsJs($fieldConfigs, $table);
        $jsTpl = $this->getCrudJsTemplate();
        $jsContent = str_replace(
            ['{$table}', '{$columnsJs}', '{$tableName}'],
            [$table, $columnsJs, $tableName],
            $jsTpl
        );
        $jsDir = root_path() . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR;
        $jsFile = $jsDir . str_replace('/', '_', $table) . '.js';
        if (!is_dir($jsDir)) {
            mkdir($jsDir, 0755, true);
        }
        file_put_contents($jsFile, $jsContent);
        $files[] = 'assets/js/backend/' . str_replace('/', '_', $table) . '.js';

        // 添加 add/edit 页表单提交脚本（内联到 add/edit 或通过公共脚本；backend-loader 会加载 backend/xxx.js，add/edit 页需单独提交表单）
        $this->appendFormScript($viewDir . 'add.html', $table, 'add');
        $this->appendFormScript($viewDir . 'edit.html', $table, 'edit');

        if ($withMenu) {
            $menuFile = $this->addMenu($table, $TableName, $title);
            if ($menuFile !== '') {
                $files[] = $menuFile;
            }
        }

        return ['success' => true, 'message' => '生成完成', 'files' => $files];
    }

    protected function getCrudJsTemplate(): string
    {
        return <<<'JS'
/**
 * CRUD 列表（一键生成）
 */
(function () {
    var indexUrl = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : '';
    var base = indexUrl ? indexUrl.replace(/\/index\/?(\?.*)?$/, '') : '';
    if (!base && typeof Config !== 'undefined' && Config.moduleurl) base = Config.moduleurl + '/{$table}';
    var editUrl = base ? base + '/edit' : '';
    var delUrl = base ? base + '/del' : '';

    var Controller = {
        index: function () {
            var $table = $('#table');
            $table.bootstrapTable({
                url: indexUrl || (base + '/index'),
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                columns: [
                    {$columnsJs}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-row-del').on('click', '#table button.btn-row-del', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除？')) return;
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
            var updateToolbar = function () {
                var sel = $table.bootstrapTable('getSelections') || [];
                $('.btn-edit, .btn-del').toggleClass('disabled btn-disabled', sel.length === 0);
            };
            $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', updateToolbar);
            $(document).off('click', '#toolbar .btn-edit').on('click', '#toolbar .btn-edit', function () {
                var sel = $table.bootstrapTable('getSelections') || [];
                if (sel.length === 0) return;
                if (sel[0].id) location.href = editUrl + '?id=' + sel[0].id;
            });
            $(document).off('click', '#toolbar .btn-del').on('click', '#toolbar .btn-del', function () {
                var sel = $table.bootstrapTable('getSelections') || [];
                if (sel.length === 0) return;
                if (!confirm('确定删除？')) return;
                $.post(delUrl, { id: sel[0].id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
JS;
    }

    protected function appendFormScript(string $viewPath, string $table, string $action): void
    {
        $content = file_get_contents($viewPath);
        if (strpos($content, 'form-submit-script') !== false) {
            return;
        }
        $script = <<<SCRIPT

<script>
(function(){
    var \$ = (typeof jQuery !== 'undefined') ? jQuery : null;
    if (!\$) { setTimeout(arguments.callee, 50); return; }
    \$(function(){
        var form = \$('#form-{$action}');
        var action = (typeof Config !== 'undefined' && Config.moduleurl) ? (Config.moduleurl + '/{$table}/{$action}') : '';
        form.attr('action', action);
        form.on('submit', function(e){
            e.preventDefault();
            \$.post(form.attr('action'), form.serialize(), function(r){
                alert(r.msg || '');
                if (r.code === 1) location.href = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : (Config.moduleurl + '/{$table}/index');
            }, 'json');
        });
    });
})();
</script>
SCRIPT;
        file_put_contents($viewPath, $content . $script);
    }

    /**
     * 一键生成菜单（写入 fa_auth_rule，pid=9 系统管理）
     */
    protected function addMenu(string $table, string $TableName, string $title): string
    {
        $name = 'admin/' . $table . '/index';
        $exists = Db::name('auth_rule')->where('name', $name)->find();
        if ($exists) {
            return '';
        }
        Db::name('auth_rule')->insert([
            'name'        => $name,
            'title'       => $title,
            'type'        => 1,
            'ismenu'      => 1,
            'status'      => 1,
            'pid'         => 9,
            'icon'        => 'fas fa-list',
            'sort'        => 100,
            'create_time' => time(),
            'update_time' => time(),
        ]);
        return 'menu: ' . $name;
    }
}
