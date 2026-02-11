/**
 * 生产计划管理页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/production_plan/index';
    var addUrl = base + '/mes/production_plan/add';
    var editUrl = base + '/mes/production_plan/edit';
    var delUrl = base + '/mes/production_plan/del';

    function statusFmt(v) {
        var statusMap = {0: '待开始', 1: '进行中', 2: '已完成', 3: '已暂停'};
        var classMap = {0: 'secondary', 1: 'primary', 2: 'success', 3: 'warning'};
        return '<span class="badge badge-' + (classMap[v] || 'secondary') + '">' + (statusMap[v] || '未知') + '</span>';
    }

    function progressFmt(v) {
        return '<div class="progress" style="height: 20px;"><div class="progress-bar" role="progressbar" style="width: ' + v + '%;">' + v + '%</div></div>';
    }

    function operFmt(value, row) {
        return '<a href="' + editUrl + '?ids=' + row.id + '" class="btn btn-xs btn-success btn-edit">编辑</a> ' +
            '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
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
                    {field: 'plan_code', title: '计划编码', align: 'left'},
                    {field: 'plan_name', title: '计划名称', align: 'left'},
                    {field: 'order.order_no', title: '订单号', align: 'left'},
                    {field: 'model.product.name', title: '产品', align: 'left'},
                    {field: 'total_quantity', title: '计划数量', width: 100, align: 'right'},
                    {field: 'completed_quantity', title: '完成数量', width: 100, align: 'right'},
                    {field: 'progress', title: '完成进度', width: 120, formatter: progressFmt},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {field: 'planned_start_time', title: '计划开始', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                    }},
                    {field: 'planned_end_time', title: '计划结束', width: 180, formatter: function(value) {
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
            Controller.api.initOrderModelSelect();
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.initOrderModelSelect();
            Controller.api.bindevent();
        },
        api: {
            initOrderModelSelect: function () {
                var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
                // 订单选择变化时加载型号
                $('#order_id').off('change').on('change', function() {
                    var orderId = $(this).val();
                    if (!orderId) {
                        $('#model_id').html('<option value="">请先选择订单</option>');
                        return;
                    }
                    $.get(base + '/mes/production_plan/getOrderModels', {order_id: orderId}, function(r) {
                        if (r.code == 1 && r.data) {
                            var html = '<option value="">请选择型号</option>';
                            $.each(r.data, function(i, item) {
                                html += '<option value="' + item.id + '">' + item.name + ' (数量: ' + item.quantity + ')</option>';
                            });
                            $('#model_id').html(html);
                        } else {
                            $('#model_id').html('<option value="">该订单暂无型号</option>');
                        }
                    }, 'json');
                });
            },
            bindevent: function () {
                var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
                var form = $('form#form-add, form#form-edit');
                if (form.length) {
                    var action = form.attr('action') || (form.attr('id') === 'form-add' ? (base + '/mes/production_plan/add') : (base + '/mes/production_plan/edit'));
                    form.attr('action', action);
                    form.on('submit', function (e) {
                        e.preventDefault();
                        var url = $(this).attr('action');
                        if (url.indexOf('?ids=') === -1 && form.attr('id') === 'form-edit') {
                            var id = $('input[name="row[id]"]').val();
                            if (id) url += '?ids=' + id;
                        }
                        // 转换datetime-local为时间戳
                        var startTime = $('input[name="row[planned_start_time]"]').val();
                        var endTime = $('input[name="row[planned_end_time]"]').val();
                        if (startTime) {
                            $('input[name="row[planned_start_time]"]').val(Math.floor(new Date(startTime).getTime() / 1000));
                        }
                        if (endTime) {
                            $('input[name="row[planned_end_time]"]').val(Math.floor(new Date(endTime).getTime() / 1000));
                        }
                        $.post(url, $(this).serialize(), function (r) {
                            if (r && r.msg) {
                                alert(r.msg);
                            }
                            if (r && r.code === 1) {
                                location.href = base + '/mes/production_plan/index';
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
            }
        }
    };

    window.__backendController = Controller;
})();
