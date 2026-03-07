(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/finance/payable/index',
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
                    { field: 'title', title: '摘要', align: 'left' },
                    { field: 'amount', title: '应付金额', width: 100, formatter: function (v) { return v != null ? parseFloat(v).toFixed(2) : ''; }},
                    { field: 'paid', title: '已付', width: 100, formatter: function (v) { return v != null ? parseFloat(v).toFixed(2) : ''; }},
                    { field: 'status', title: '状态', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">已结清</span>' : '<span class="badge badge-warning">未结清</span>'; }},
                    { field: 'operate', title: '操作', width: 120, formatter: function (v, row) {
                        return '<a href="' + base + '/finance/payable/edit?id=' + row.id + '" class="btn btn-xs btn-success">编辑</a> <a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
                    }}
                ]
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-del').on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(base + '/finance/payable/del', { ids: id }, function (r) { if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); } else alert(r.msg || '删除失败'); }, 'json');
            });
        },
        add: function () {},
        edit: function () {}
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
