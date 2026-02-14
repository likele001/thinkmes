(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/bom/index';
    var editUrl = base + '/mes/bom/edit';
    var delUrl = base + '/mes/bom/del';
    var itemsUrl = base + '/mes/bom/items';

    function statusFmt(v) {
        var statusMap = {0: '草稿', 1: '审核中', 2: '已发布', 3: '已废弃'};
        return statusMap[v] || '未知';
    }

    function operFmt(v, row) {
        var html = '<a class="btn btn-xs btn-primary" href="' + editUrl + '?ids=' + v + '">编辑</a> ';
        html += '<a class="btn btn-xs btn-info" href="' + itemsUrl + '?ids=' + v + '">明细</a> ';
        html += '<button class="btn btn-xs btn-danger" data-id="' + v + '" type="button">删除</button>';
        return html;
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }
            $table.bootstrapTable({
                url: indexUrl,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'bom_no', title: 'BOM编号', width: 150 },
                    { field: 'version', title: '版本号', width: 100 },
                    { field: 'base_quantity', title: '基准数量', width: 100 },
                    { field: 'status', title: '状态', width: 100, formatter: statusFmt },
                    { field: 'create_time', title: '创建时间', width: 150 },
                    { field: 'id', title: '操作', width: 200, formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除该BOM？')) return;
                $.post(delUrl, { ids: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
