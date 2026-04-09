(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table-list');
            if (!table.length || typeof table.bootstrapTable !== 'function') return;
            table.bootstrapTable({
                url: base + '/prompt/generation/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                queryParams: function (p) {
                    return $.extend(p, {
                        keyword: $('#search-keyword').val(),
                        status: $('#filter-status').val()
                    });
                },
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.rows || d.list || [] };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 70 },
                    { field: 'user_id', title: '用户ID', width: 80 },
                    { field: 'template_title', title: '模板', width: 120 },
                    { field: 'output_text', title: 'AI回复', formatter: function (v) { var s = (v || '').substr(0, 60); return s.length < (v || '').length ? s + '...' : s; } },
                    { field: 'tokens_used', title: 'Token', width: 80 },
                    { field: 'cost_ms', title: '耗时(ms)', width: 90 },
                    { field: 'status', title: '状态', width: 70, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">成功</span>' : '<span class="badge badge-danger">失败</span>'; } },
                    { field: 'create_time', title: '时间', width: 150, formatter: function (v) { return v ? new Date(v * 1000).toLocaleString() : '-'; } },
                    { field: 'id', title: '操作', width: 80, formatter: function (v) {
                        return '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + v + '">删除</a>';
                    }}
                ]
            });
            $('#btn-search, #search-keyword').on('click keypress', function (e) { if (e.type === 'click' || e.which === 13) table.bootstrapTable('refresh'); });
            $('#filter-status').on('change', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-del').on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(base + '/prompt/generation/del', { ids: id }, function (r) {
                    if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); }
                    else alert(r.msg || '删除失败');
                }, 'json');
            });
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
