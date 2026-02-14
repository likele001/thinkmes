/**
 * 工序工价管理页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/process_price/index';
    var addUrl = base + '/mes/process_price/add';
    var editUrl = base + '/mes/process_price/edit';
    var delUrl = base + '/mes/process_price/del';
    var batchUrl = base + '/mes/process_price/batch';

    function statusFmt(v) { 
        return v == 1 ? '<span class="badge badge-success">正常</span>' : '<span class="badge badge-danger">禁用</span>'; 
    }

    function priceFmt(v) {
        return v ? parseFloat(v).toFixed(2) : '0.00';
    }

    function operFmt(value, row) {
        return '<a href="' + editUrl + '?ids=' + row.id + '" class="btn btn-xs btn-success btn-edit">编辑</a> ' +
            '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }
            $table.bootstrapTable({
                url: indexUrl,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                columns: [
                    {checkbox: true},
                    {field: 'id', title: 'ID', width: 80, sortable: true},
                    {field: 'model_name', title: '产品型号', align: 'left'},
                    {field: 'process_name', title: '工序', align: 'left'},
                    {field: 'price', title: '计件工价(元/件)', width: 120, align: 'right', formatter: priceFmt},
                    {field: 'time_price', title: '计时工价(元/小时)', width: 120, align: 'right', formatter: priceFmt},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {field: 'create_time', title: '创建时间', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                    }},
                    {field: 'operate', title: '操作', width: 150, events: {
                        'click .btn-edit': function(e, value, row) {
                            location.href = editUrl + '?ids=' + row.id;
                        },
                        'click .btn-del': function(e, value, row) {
                            if (confirm('确定要删除吗？')) {
                                $.post(delUrl, {ids: row.id}, function(r) {
                                    if (r.code == 1) {
                                        $table.bootstrapTable('refresh');
                                        alert(r.msg || '删除成功');
                                    } else {
                                        alert(r.msg || '删除失败');
                                    }
                                }, 'json');
                            }
                        }
                    }, formatter: operFmt}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            
            // 刷新按钮
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });
            
            // 编辑按钮（工具栏，只绑定到工具栏的按钮，避免影响表格行的编辑按钮）
            $(document).off('click', '#toolbar .btn-edit').on('click', '#toolbar .btn-edit', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length != 1) {
                    alert('请选择一条记录');
                    return;
                }
                location.href = editUrl + '?ids=' + rows[0].id;
            });
            
            // 删除按钮（工具栏，只绑定到工具栏的按钮，避免影响表格行的删除按钮）
            $(document).off('click', '#toolbar .btn-del').on('click', '#toolbar .btn-del', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length == 0) {
                    alert('请选择要删除的记录');
                    return;
                }
                if (!confirm('确定要删除选中的 ' + rows.length + ' 条记录吗？')) {
                    return;
                }
                var ids = rows.map(function(r) { return r.id; });
                $.post(delUrl, {ids: ids.join(',')}, function(r) {
                    if (r.code == 1) {
                        $table.bootstrapTable('refresh');
                        alert(r.msg || '删除成功');
                    } else {
                        alert(r.msg || '删除失败');
                    }
                }, 'json');
            });
            
            // 表格行选择
            $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function() {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length > 0) {
                    $('.btn-edit, .btn-del').removeClass('disabled btn-disabled');
                } else {
                    $('.btn-edit, .btn-del').addClass('disabled btn-disabled');
                }
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        batch: function () {
            Controller.api.bindevent();
            Controller.api.initSelect2();
        },
        api: {
            bindevent: function () {
                var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
                var form = $('form#form-add, form#form-edit, form#form-batch');
                if (form.length) {
                    var formId = form.attr('id');
                    var action = form.attr('action');
                    if (!action) {
                        if (formId === 'form-add') {
                            action = base + '/mes/process_price/add';
                        } else if (formId === 'form-edit') {
                            action = base + '/mes/process_price/edit';
                        } else if (formId === 'form-batch') {
                            action = base + '/mes/process_price/batch';
                        }
                    }
                    form.attr('action', action);
                    form.on('submit', function (e) {
                        e.preventDefault();
                        var url = $(this).attr('action');
                        // 编辑页面需要添加 id 参数
                        if (formId === 'form-edit' && url.indexOf('?') === -1) {
                            var id = $('input[name="row[id]"]').val();
                            if (id) url += '?ids=' + id;
                        }
                        // 批量设置验证
                        if (formId === 'form-batch') {
                            var modelIds = $('#model_ids').val();
                            var processIds = $('#process_ids').val();
                            if (!modelIds || modelIds.length == 0) {
                                alert('请选择产品型号');
                                return;
                            }
                            if (!processIds || processIds.length == 0) {
                                alert('请选择工序');
                                return;
                            }
                            if (!confirm('确定要为 ' + modelIds.length + ' 个型号和 ' + processIds.length + ' 个工序批量设置工价吗？')) {
                                return;
                            }
                        }
                        $.post(url, $(this).serialize(), function (r) {
                            if (r && r.msg) {
                                alert(r.msg);
                            }
                            if (r && r.code === 1) {
                                location.href = base + '/mes/process_price/index';
                            }
                        }, 'json').fail(function(xhr) {
                            try {
                                var r = JSON.parse(xhr.responseText);
                                alert(r.msg || '操作失败');
                            } catch(e) {
                                alert('操作失败');
                            }
                        });
                    });
                }
            },
            initSelect2: function() {
                // 初始化Select2（如果可用）
                if (typeof $.fn.select2 !== 'undefined') {
                    $('.select2').select2({
                        placeholder: '请选择',
                        allowClear: true
                    });
                }
            }
        }
    };

    window.__backendController = Controller;
})();
