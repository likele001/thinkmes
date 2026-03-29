(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/restaurant/store/index',
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
                    { field: 'name', title: '门店名称', align: 'left' },
                    { field: 'contact_phone', title: '电话', width: 140 },
                    { field: 'address', title: '地址', align: 'left' },
                    { field: 'status', title: '状态', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-secondary">禁用</span>'; } },
                    { field: 'create_time', title: '创建时间', width: 170, formatter: function (v) { return v ? new Date(v * 1000).toLocaleString() : '-'; } },
                    { field: 'operate', title: '操作', width: 140, formatter: function (v, row) {
                        return '<a href="' + base + '/restaurant/store/edit?id=' + row.id + '" class="btn btn-xs btn-success">编辑</a> <a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
                    } }
                ]
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-del').on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(base + '/restaurant/store/del', { ids: id }, function (r) {
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

