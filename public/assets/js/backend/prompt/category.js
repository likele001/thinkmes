(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table-list');
            if (!table.length || typeof table.bootstrapTable !== 'function') return;
            table.bootstrapTable({
                url: base + '/prompt/category/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                queryParams: function (p) {
                    return $.extend(p, { keyword: $('#search-keyword').val() });
                },
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.rows || d.list || [] };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'name', title: '分类名称' },
                    { field: 'icon', title: '图标', formatter: function (v) { return v ? '<i class="' + v + '"></i> ' + v : '-'; } },
                    { field: 'sort', title: '排序', width: 70 },
                    { field: 'status', title: '状态', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-secondary">禁用</span>'; } },
                    { field: 'id', title: '操作', width: 140, formatter: function (v, row) {
                        return '<a href="' + base + '/prompt/category/edit?id=' + v + '" class="btn btn-xs btn-success">编辑</a> <a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + v + '">删除</a>';
                    }}
                ]
            });
            $('#btn-search, #search-keyword').on('click keypress', function (e) { if (e.type === 'click' || e.which === 13) table.bootstrapTable('refresh'); });
            $('#btn-add').on('click', function () {
                layer.open({ type: 2, title: '添加分类', area: ['520px', '380px'], content: base + '/prompt/category/add', end: function () { table.bootstrapTable('refresh'); } });
            });
            $(document).off('click', '.btn-del').on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(base + '/prompt/category/del', { ids: id }, function (r) {
                    if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); }
                    else alert(r.msg || '删除失败');
                }, 'json');
            });
        },
        add: function () {
            if (window.BackendUtil) window.BackendUtil.initGenericAddForm();
        },
        edit: function () {
            if (window.BackendUtil) window.BackendUtil.initGenericEditForm();
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
