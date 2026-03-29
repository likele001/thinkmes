(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    function stateBadge(v) {
        if (v == 1) return '<span class="badge badge-primary">占用</span>';
        if (v == 2) return '<span class="badge badge-warning">清台中</span>';
        if (v == 3) return '<span class="badge badge-info">预定</span>';
        return '<span class="badge badge-success">空闲</span>';
    }
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/restaurant/table/index',
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
                    { field: 'store_name', title: '门店', width: 160 },
                    { field: 'area_name', title: '区域', width: 160 },
                    { field: 'name', title: '桌台', width: 140 },
                    { field: 'code', title: '编码', width: 120 },
                    { field: 'seats', title: '座位', width: 80 },
                    { field: 'state', title: '状态', width: 90, formatter: stateBadge },
                    { field: 'status', title: '启用', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-secondary">禁用</span>'; } },
                    { field: 'qr_token', title: 'Token', align: 'left', formatter: function (v) { return v ? ('<code>' + v + '</code>') : '-'; } },
                    { field: 'operate', title: '操作', width: 350, formatter: function (v, row) {
                        return '<a href="' + base + '/restaurant/table/edit?id=' + row.id + '" class="btn btn-xs btn-success">编辑</a> ' +
                            '<a href="javascript:;" class="btn btn-xs btn-warning btn-reset-token" data-id="' + row.id + '">重置Token</a> ' +
                            '<a href="' + base + '/restaurant/table/qrcode?id=' + row.id + '" target="_blank" class="btn btn-xs btn-info">H5二维码</a> ' +
                            '<a href="' + base + '/restaurant/table/wxacode?id=' + row.id + '" target="_blank" class="btn btn-xs btn-primary">小程序码</a> ' +
                            '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
                    } }
                ]
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-del').on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(base + '/restaurant/table/del', { ids: id }, function (r) {
                    if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); }
                    else alert(r.msg || '删除失败');
                }, 'json');
            });
            $(document).off('click', '.btn-reset-token').on('click', '.btn-reset-token', function () {
                var id = $(this).data('id');
                if (!confirm('确定重置 Token？旧桌码将失效')) return;
                $.post(base + '/restaurant/table/resetToken', { id: id }, function (r) {
                    if (r.code == 1) { table.bootstrapTable('refresh'); alert('重置成功'); }
                    else alert(r.msg || '重置失败');
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
