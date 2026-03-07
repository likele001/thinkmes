(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/equipment/equipment/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                sortName: 'id',
                sortOrder: 'desc',
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                columns: [
                    { checkbox: true },
                    { field: 'id', title: 'ID', width: 80, sortable: true },
                    { field: 'code', title: '设备编号', width: 120 },
                    { field: 'name', title: '设备名称', align: 'left' },
                    { field: 'category', title: '分类', width: 100 },
                    { field: 'status', title: '状态', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">正常</span>' : '<span class="badge badge-secondary">停用</span>'; }},
                    { field: 'operate', title: '操作', width: 140, formatter: function (v, row) {
                        return '<a href="' + base + '/equipment/equipment/edit?id=' + row.id + '" class="btn btn-xs btn-success">编辑</a> <a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
                    }}
                ]
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-del').on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(base + '/equipment/equipment/del', { ids: id }, function (r) { if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); } else alert(r.msg || '删除失败'); }, 'json');
            });
        },
        add: function () {},
        edit: function () {},
        stat: function () {
            var $ = jQuery;
            $(document).off('click', '.btn-query').on('click', '.btn-query', function () {
                var url = base + '/equipment/equipment/stat?start=' + ($('#start').val() || '') + '&end=' + ($('#end').val() || '');
                location.href = url;
            });
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
