/**
 * 分工分配管理页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/allocation/index';
    var addUrl = base + '/mes/allocation/add';
    var editUrl = base + '/mes/allocation/edit';
    var delUrl = base + '/mes/allocation/del';
    var generateQrcodeUrl = base + '/mes/allocation/generateQrcode';

    function statusFmt(v) {
        var statusMap = {0: '待开始', 1: '进行中', 2: '已完成'};
        var classMap = {0: 'secondary', 1: 'primary', 2: 'success'};
        return '<span class="badge badge-' + (classMap[v] || 'secondary') + '">' + (statusMap[v] || '未知') + '</span>';
    }

    function progressFmt(v) {
        return '<div class="progress" style="height: 20px;"><div class="progress-bar" role="progressbar" style="width: ' + v + '%;">' + v + '%</div></div>';
    }

    function operFmt(value, row) {
        return '<a href="' + editUrl + '?ids=' + row.id + '" class="btn btn-xs btn-success btn-edit">编辑</a> ' +
            '<a href="javascript:;" class="btn btn-xs btn-info btn-qrcode">二维码</a> ' +
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
                    {field: 'allocation_code', title: '分配编码', align: 'left'},
                    {field: 'order.order_no', title: '订单号', align: 'left'},
                    {field: 'model.product.name', title: '产品', align: 'left'},
                    {field: 'process.name', title: '工序', align: 'left'},
                    {field: 'quantity', title: '分配数量', width: 100, align: 'right'},
                    {field: 'completed_quantity', title: '完成数量', width: 100, align: 'right'},
                    {field: 'completion_rate', title: '完成率', width: 120, formatter: progressFmt},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {field: 'operate', title: '操作', width: 200, events: {
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
                        },
                        'click .btn-qrcode': function(e, value, row) {
                            if (confirm('确定要重新生成二维码吗？')) {
                                $.post(generateQrcodeUrl, {id: row.id}, function(r) {
                                    alert(r.msg || (r.code == 1 ? '二维码生成成功' : '生成失败'));
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
                            action = base + '/mes/allocation/add';
                        } else if (formId === 'form-edit') {
                            action = base + '/mes/allocation/edit';
                        } else if (formId === 'form-batch') {
                            action = base + '/mes/allocation/batch';
                        }
                    }
                    form.attr('action', action);
                    form.on('submit', function (e) {
                        e.preventDefault();
                        var url = $(this).attr('action');
                        if (formId === 'form-edit' && url.indexOf('?') === -1) {
                            var id = $('input[name="row[id]"]').val();
                            if (id) url += '?ids=' + id;
                        }
                        $.post(url, $(this).serialize(), function (r) {
                            if (r && r.msg) {
                                alert(r.msg);
                            }
                            if (r && r.code === 1) {
                                location.href = base + '/mes/allocation/index';
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
