(function () {
    var Controller = {
        index: function () {
            var table = $('#table');
            if (!table.length) return;
            table.bootstrapTable({
                url: Config.moduleurl + '/restaurant/review_alert/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                sortName: 'review_time',
                sortOrder: 'desc',
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                columns: [
                    { field: 'id', title: 'ID', width: 70 },
                    { field: 'store_name', title: '门店', width: 120 },
                    { field: 'platform', title: '平台', width: 90 },
                    { field: 'rating', title: '评分', width: 70 },
                    { field: 'content', title: '内容', align: 'left', formatter: function (v) { return '<div style="max-width:560px;white-space:normal">' + (v || '') + '</div>'; } },
                    { field: 'review_time', title: '时间', width: 160, formatter: function (v) { if (!v) return '-'; return new Date(v * 1000).toLocaleString(); } },
                    { field: 'status', title: '状态', width: 90, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">已处理</span>' : '<span class="badge badge-warning">待处理</span>'; } },
                    { field: 'operate', title: '操作', width: 120, formatter: function (v, row) {
                        if (row.status == 1) return '-';
                        return '<a href="javascript:;" class="btn btn-xs btn-success btn-done" data-id="' + row.id + '">标记已处理</a>';
                    } }
                ]
            });
            $('#toolbar .btn-refresh').off('click').on('click', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-done').on('click', '.btn-done', function () {
                var id = $(this).data('id');
                $.post(Config.moduleurl + '/restaurant/review_alert/markDone', { id: id }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已处理' : '失败'));
                    if (r.code == 1) table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
