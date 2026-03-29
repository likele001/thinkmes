(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    function statusBadge(v) {
        var map = {
            0: { t: '待确认', c: 'badge badge-secondary' },
            1: { t: '制作中', c: 'badge badge-info' },
            2: { t: '待上菜', c: 'badge badge-warning' },
            3: { t: '已完成', c: 'badge badge-success' },
            4: { t: '已结账', c: 'badge badge-primary' }
        };
        var m = map[v] || map[0];
        return '<span class="' + m.c + '">' + m.t + '</span>';
    }
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/restaurant/order/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                sortName: 'id',
                sortOrder: 'desc',
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                columns: [
                    { checkbox: true },
                    { field: 'id', title: 'ID', width: 70 },
                    { field: 'order_no', title: '订单号', width: 160 },
                    { field: 'table_id', title: '桌台', width: 100 },
                    { field: 'total_amount', title: '金额', width: 100, formatter: function (v) { return v != null ? parseFloat(v).toFixed(2) : ''; } },
                    { field: 'status', title: '状态', width: 90, formatter: statusBadge },
                    { field: 'create_time', title: '下单时间', width: 170, formatter: function (v) { return v ? new Date(v * 1000).toLocaleString() : '-'; } },
                    { field: 'operate', title: '操作', width: 240, formatter: function (v, row) {
                        var url = base + '/restaurant/order/detail?id=' + row.id;
                        var btns = '<a href="' + url + '" class="btn btn-xs btn-success">详情</a> ';
                        btns += '<div class="btn-group btn-group-xs" role="group">';
                        btns += '<a href="javascript:;" class="btn btn-secondary btn-status" data-id="' + row.id + '" data-status="0">待确认</a>';
                        btns += '<a href="javascript:;" class="btn btn-info btn-status" data-id="' + row.id + '" data-status="1">制作中</a>';
                        btns += '<a href="javascript:;" class="btn btn-warning btn-status" data-id="' + row.id + '" data-status="2">待上菜</a>';
                        btns += '<a href="javascript:;" class="btn btn-success btn-status" data-id="' + row.id + '" data-status="3">完成</a>';
                        btns += '<a href="javascript:;" class="btn btn-primary btn-status" data-id="' + row.id + '" data-status="4">结账</a>';
                        btns += '</div>';
                        return btns;
                    } }
                ]
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-status').on('click', '.btn-status', function () {
                var id = $(this).data('id');
                var status = $(this).data('status');
                $.post(base + '/restaurant/order/updateStatus', { id: id, status: status }, function (r) {
                    if (r.code == 1) { table.bootstrapTable('refresh'); }
                    alert(r.msg || (r.code == 1 ? '成功' : '失败'));
                }, 'json');
            });
        },
        detail: function () {}
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();

