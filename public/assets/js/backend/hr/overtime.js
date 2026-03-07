(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/hr/overtime/index',
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
                    { field: 'employee_name', title: '员工', width: 100 },
                    { field: 'overtime_date', title: '加班日期', width: 100 },
                    { field: 'hours', title: '时长(小时)', width: 100 },
                    { field: 'reason', title: '原因', align: 'left' },
                    { field: 'status', title: '状态', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">已通过</span>' : (v == 2 ? '<span class="badge badge-danger">已拒绝</span>' : '<span class="badge badge-warning">待审批</span>'); }},
                    { field: 'operate', title: '操作', width: 140, formatter: function (v, row) {
                        var html = '<a href="' + base + '/hr/overtime/edit?id=' + row.id + '" class="btn btn-xs btn-success">编辑</a> ';
                        if (row.status == 0) html += '<a href="javascript:;" class="btn btn-xs btn-primary btn-approve" data-id="' + row.id + '">审批</a> ';
                        html += '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
                        return html;
                    }}
                ]
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-del').on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(base + '/hr/overtime/del', { ids: id }, function (r) { if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); } else alert(r.msg || '删除失败'); }, 'json');
            });
        },
        add: function () {},
        edit: function () {},
        approve: function () {}
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
