/**
 * 工作流-模块接入
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/workflow/module/index';
    var optionsUrl = base + '/workflow/module/options';
    var saveUrl = base + '/workflow/module/save';

    function enabledBadge(value) {
        return value == 1
            ? '<span class="badge badge-success">已启用</span>'
            : '<span class="badge badge-secondary">未启用</span>';
    }

    function definitionBadge(value) {
        return value > 0
            ? '<span class="badge badge-info">已配置</span>'
            : '<span class="badge badge-warning">未配置</span>';
    }

    function buildDefinitionOptions(list, current) {
        var html = '<option value="0">请选择审批流程</option>';
        (list || []).forEach(function (item) {
            var selected = Number(item.id) === Number(current) ? ' selected' : '';
            var statusText = Number(item.status) === 1 ? '[启用]' : '[禁用]';
            html += '<option value="' + item.id + '"' + selected + '>' + item.name + ' ' + statusText + '</option>';
        });
        return html;
    }

    function openConfigDialog(row, table) {
        $.get(optionsUrl, function (res) {
            var defs = (res.data && res.data.definitions) || [];
            var html = [
                '<div style="padding:15px 20px;">',
                '<form id="workflow-module-form">',
                '<div class="form-group"><label>模块标识</label><input type="text" class="form-control" value="' + row.module_code + '" disabled></div>',
                '<div class="form-group"><label>默认流程</label><select name="definition_id" class="form-control">' + buildDefinitionOptions(defs, row.definition_id) + '</select></div>',
                '<div class="form-group"><label>业务表名</label><input type="text" name="table_name" class="form-control" value="' + (row.table_name || '') + '"></div>',
                '<div class="form-group"><label>标题字段</label><input type="text" name="title_field" class="form-control" value="' + (row.title_field || '') + '"></div>',
                '<div class="form-group"><label>状态字段</label><input type="text" name="status_field" class="form-control" value="' + (row.status_field || '') + '"></div>',
                '<div class="form-group"><label>审批中值</label><input type="text" name="in_progress_value" class="form-control" value="' + (row.in_progress_value || '') + '"></div>',
                '<div class="form-group"><label>通过值</label><input type="text" name="approved_value" class="form-control" value="' + (row.approved_value || '') + '"></div>',
                '<div class="form-group"><label>拒绝值</label><input type="text" name="rejected_value" class="form-control" value="' + (row.rejected_value || '') + '"></div>',
                '<div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="enabled" value="1"' + (row.enabled ? ' checked' : '') + '><label class="form-check-label">启用</label></div>',
                '</form>',
                '</div>'
            ].join('');

            layer.open({
                type: 1,
                title: '配置模块：' + row.module_name,
                area: ['640px', '650px'],
                content: html,
                btn: ['保存', '取消'],
                yes: function (index) {
                    var form = $('#workflow-module-form');
                    var payload = {
                        module_code: row.module_code,
                        enabled: form.find('[name="enabled"]').is(':checked') ? 1 : 0,
                        definition_id: Number(form.find('[name="definition_id"]').val() || 0),
                        table_name: form.find('[name="table_name"]').val() || '',
                        title_field: form.find('[name="title_field"]').val() || '',
                        status_field: form.find('[name="status_field"]').val() || '',
                        in_progress_value: form.find('[name="in_progress_value"]').val() || '',
                        approved_value: form.find('[name="approved_value"]').val() || '',
                        rejected_value: form.find('[name="rejected_value"]').val() || ''
                    };
                    $.post(saveUrl, payload, function (saveRes) {
                        if (saveRes.code === 1) {
                            Toastr.success(saveRes.msg || '保存成功');
                            layer.close(index);
                            table.bootstrapTable('refresh');
                        } else {
                            Toastr.error(saveRes.msg || '保存失败');
                        }
                    }, 'json').fail(function () {
                        Toastr.error('保存失败');
                    });
                }
            });
        }, 'json');
    }

    var Controller = {
        index: function () {
            var table = $('#table');
            if (!table.length) return;

            table.bootstrapTable({
                url: indexUrl,
                pagination: false,
                responseHandler: function (res) {
                    var data = res.data || {};
                    return {
                        total: data.total || 0,
                        rows: data.list || []
                    };
                },
                columns: [
                    { field: 'module_code', title: '模块标识', width: 180 },
                    { field: 'module_name', title: '模块名称', width: 180 },
                    { field: 'enabled', title: '启用状态', width: 110, formatter: enabledBadge },
                    { field: 'definition_id', title: '流程配置', width: 110, formatter: definitionBadge },
                    { field: 'table_name', title: '业务表名' },
                    { field: 'status_field', title: '状态字段', width: 120 },
                    {
                        field: 'operate',
                        title: '操作',
                        width: 170,
                        formatter: function (value, row) {
                            return [
                                '<a href="javascript:;" class="btn btn-xs btn-primary btn-config" data-code="' + row.module_code + '">配置</a>',
                                '<a href="javascript:;" class="btn btn-xs btn-warning btn-toggle" data-code="' + row.module_code + '" data-enabled="' + (row.enabled ? 0 : 1) + '">' + (row.enabled ? '禁用' : '启用') + '</a>'
                            ].join(' ');
                        }
                    }
                ]
            });

            $('#toolbar .btn-refresh').off('click').on('click', function () {
                table.bootstrapTable('refresh');
            });

            $(document).off('click.workflow.module');
            $(document).on('click.workflow.module', '.btn-config', function () {
                var code = $(this).data('code');
                var rows = table.bootstrapTable('getData') || [];
                var row = rows.find(function (item) { return item.module_code === code; });
                if (row) openConfigDialog(row, table);
            });
            $(document).on('click.workflow.module', '.btn-toggle', function () {
                var code = $(this).data('code');
                var enabled = $(this).data('enabled');
                var rows = table.bootstrapTable('getData') || [];
                var row = rows.find(function (item) { return item.module_code === code; });
                if (!row) {
                    Toastr.error('未找到模块数据');
                    return;
                }
                layer.confirm('确定要' + (enabled ? '启用' : '禁用') + '此模块吗？', function (idx) {
                    $.post(saveUrl, {
                        module_code: code,
                        enabled: enabled,
                        definition_id: row.definition_id || 0,
                        table_name: row.table_name || '',
                        title_field: row.title_field || '',
                        status_field: row.status_field || '',
                        in_progress_value: row.in_progress_value || '',
                        approved_value: row.approved_value || '',
                        rejected_value: row.rejected_value || ''
                    }, function (res) {
                        if (res.code === 1) {
                            Toastr.success(res.msg || '操作成功');
                            table.bootstrapTable('refresh');
                            layer.close(idx);
                        } else {
                            Toastr.error(res.msg || '操作失败');
                        }
                    }, 'json').fail(function () {
                        Toastr.error('操作失败');
                    });
                });
            });
        },
        options: function () {},
        save: function () {}
    };

    window.__backendController = Controller;
})();
