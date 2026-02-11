(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/attachment/index';
    var delUrl = base + '/attachment/del';

    function operFmt(v, row) {
        var url = row.url || '';
        var link = url ? '<a class="btn btn-xs btn-info" href="' + url + '" target="_blank">查看</a> ' : '';
        return link + '<button class="btn btn-xs btn-danger" data-id="' + v + '" type="button">删除</button>';
    }

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
                    { field: 'url', title: '地址', formatter: function (v) { return v ? '<a href="' + v + '" target="_blank">' + (v.length > 50 ? v.substring(0, 50) + '...' : v) + '</a>' : '-'; } },
                    { field: 'size', title: '大小(字节)' },
                    { field: 'mime_type', title: '类型' },
                    { field: 'storage', title: '存储' },
                    { field: 'create_time', title: '上传时间' },
                    { field: 'id', title: '操作', formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除该附件记录？')) return;
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
