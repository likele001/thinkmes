/**
 * 追溯码管理页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/trace_code/index';
    var delUrl = base + '/mes/trace_code/del';

    function statusFmt(v) {
        return v == 1 ? '<span class="badge badge-success">有效</span>' : '<span class="badge badge-danger">失效</span>';
    }

    function operFmt(value, row) {
        return '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
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
                    {field: 'trace_code', title: '追溯码', align: 'left'},
                    {field: 'item_no', title: '产品编号', align: 'left'},
                    {field: 'order.order_no', title: '订单号', align: 'left'},
                    {field: 'model.product.name', title: '产品', align: 'left'},
                    {field: 'process.name', title: '工序', align: 'left'},
                    {field: 'scan_count', title: '扫码次数', width: 100, align: 'right'},
                    {field: 'last_scan_time', title: '最后扫码时间', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '-';
                    }},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {field: 'create_time', title: '创建时间', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                    }},
                    {field: 'operate', title: '操作', width: 120, events: {
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
            
            $(document).off('click', '.btn-generate').on('click', '.btn-generate', function () {
                alert('请从报工记录页面生成追溯码');
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
                    $('.btn-del').removeClass('disabled btn-disabled');
                } else {
                    $('.btn-del').addClass('disabled btn-disabled');
                }
            });
        }
    };

    window.__backendController = Controller;
})();
