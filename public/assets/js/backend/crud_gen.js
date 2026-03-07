/**
 * 在线命令（CRUD 生成记录列表）
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/crud_gen/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                sortName: 'id',
                sortOrder: 'desc',
                queryParams: function (p) {
                    p.limit = p.limit || 20;
                    p.offset = p.offset || 0;
                    p.page = p.offset ? Math.floor(p.offset / p.limit) + 1 : 1;
                    return p;
                },
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.list || [] };
                },
                columns: [
                    { checkbox: true },
                    { field: 'id', title: 'ID', width: 70 },
                    { field: 'type_text', title: '类型', width: 120 },
                    { field: 'command', title: '命令', align: 'left', formatter: function (v) {
                        return v ? (v.length > 60 ? v.substring(0, 60) + '...' : v) : '-';
                    }},
                    { field: 'executetime', title: '执行时间', width: 160, formatter: function (v) {
                        if (!v) return '-';
                        var d = new Date(parseInt(v, 10) * 1000);
                        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0') + ' ' +
                            String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0') + ':' + String(d.getSeconds()).padStart(2, '0');
                    }},
                    { field: 'status_text', title: '状态', width: 80, formatter: function (v, row) {
                        var cls = (row.status === 1) ? 'text-success' : 'text-danger';
                        return '<span class="' + cls + '">' + (v || '-') + '</span>';
                    }},
                    { field: 'operate', title: '操作', width: 200, formatter: function (v, row) {
                        return '<a href="javascript:;" class="btn btn-xs btn-success btn-reexec" data-id="' + row.id + '">再次执行</a> ' +
                            '<a href="javascript:;" class="btn btn-xs btn-info btn-detail" data-id="' + row.id + '">详情</a> ' +
                            '<a href="javascript:;" class="btn btn-xs btn-danger btn-del-one" data-id="' + row.id + '">删除</a>';
                    }}
                ]
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-reexec').on('click', '.btn-reexec', function () {
                var id = $(this).data('id');
                if (!confirm('确定再次执行该命令？')) return;
                $.post(base + '/crud_gen/reExecute', { id: id }, function (r) {
                    if (r.code == 1) {
                        table.bootstrapTable('refresh');
                        alert((r.data && r.data.result) ? r.data.result : (r.msg || '执行完成'));
                    } else {
                        alert(r.msg || '执行失败');
                    }
                }, 'json');
            });
            $(document).off('click', '.btn-detail').on('click', '.btn-detail', function () {
                var id = $(this).data('id');
                $.get(base + '/crud_gen/detail', { id: id }, function (r) {
                    if (r.code != 1 || !r.data) { alert(r.msg || '获取详情失败'); return; }
                    var d = r.data;
                    var html = '<div class="p-2"><p><strong>类型：</strong>' + (d.type_text || d.type) + '</p>';
                    html += '<p><strong>命令/概要：</strong><pre class="bg-light p-2">' + (d.command || '-') + '</pre></p>';
                    html += '<p><strong>状态：</strong>' + (d.status_text || '-') + '</p>';
                    html += '<p><strong>执行时间：</strong>' + (d.executetime ? new Date(d.executetime * 1000).toLocaleString() : '-') + '</p>';
                    html += '<p><strong>返回结果：</strong><pre class="bg-light p-2 small" style="max-height:200px;overflow:auto;">' + (d.content || '-').replace(/</g, '&lt;') + '</pre></p></div>';
                    var $modal = $('<div class="modal fade"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">命令详情</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">' + html + '</div></div></div></div>');
                    $modal.appendTo('body').modal('show').on('hidden.bs.modal', function () { $modal.remove(); });
                }, 'json');
            });
            $(document).off('click', '.btn-del-one').on('click', '.btn-del-one', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(base + '/crud_gen/del', { ids: id }, function (r) {
                    if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); } else alert(r.msg || '删除失败');
                }, 'json');
            });
            $(document).off('click', '#toolbar .btn-del').on('click', '#toolbar .btn-del', function () {
                var rows = table.bootstrapTable('getSelections');
                if (rows.length === 0) { alert('请选择记录'); return; }
                if (!confirm('确定删除选中的 ' + rows.length + ' 条？')) return;
                $.post(base + '/crud_gen/del', { ids: rows.map(function (r) { return r.id; }) }, function (r) {
                    if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); } else alert(r.msg || '删除失败');
                }, 'json');
            });
            table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
                var sel = table.bootstrapTable('getSelections') || [];
                $('#toolbar .btn-del').toggleClass('disabled btn-disabled', sel.length === 0);
            });
        },
        add: function () {}
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
