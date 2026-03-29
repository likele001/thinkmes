(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/restaurant/combo/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                sortName: 'id',
                sortOrder: 'desc',
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                columns: [
                    { checkbox: true },
                    { field: 'id', title: 'ID', width: 80 },
                    { field: 'name', title: '套餐名称', align: 'left' },
                    { field: 'price', title: '价格', width: 100, formatter: function (v) { return v != null ? parseFloat(v).toFixed(2) : ''; } },
                    { field: 'sold_out', title: '售罄', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-warning">是</span>' : '<span class="badge badge-success">否</span>'; } },
                    { field: 'status', title: '上架', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">上架</span>' : '<span class="badge badge-secondary">下架</span>'; } },
                    { field: 'operate', title: '操作', width: 140, formatter: function (v, row) {
                        return '<a href="' + base + '/restaurant/combo/edit?id=' + row.id + '" class="btn btn-xs btn-success">编辑</a> <a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
                    } }
                ]
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-del').on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(base + '/restaurant/combo/del', { ids: id }, function (r) {
                    if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); }
                    else alert(r.msg || '删除失败');
                }, 'json');
            });
        },
        add: function () { if (window.BackendUtil) window.BackendUtil.initGenericAddForm(); },
        edit: function () { if (window.BackendUtil) window.BackendUtil.initGenericEditForm(); }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();

