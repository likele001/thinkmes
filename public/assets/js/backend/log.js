(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : (base + '/log/index');

    var Controller = {
        index: function () {
            var $table = $('#table');
            $table.bootstrapTable({
                url: indexUrl,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'admin_id', title: '管理员ID' },
                    { field: 'type', title: '类型' },
                    { field: 'content', title: '内容' },
                    { field: 'ip', title: 'IP' },
                    { field: 'create_time', title: '时间' }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#toolbar .btn-export').on('click', '#toolbar .btn-export', function () {
                var params = $table.bootstrapTable('getOptions').queryParams({});
                window.location.href = base + '/log/export?' + $.param(params || {});
            });
        }
    };
    window.__backendController = Controller;
})();
