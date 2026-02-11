(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/material/index';
    var editUrl = base + '/mes/material/edit';
    var delUrl = base + '/mes/material/del';

    function statusFmt(v) { return v == 'active' ? '正常' : '禁用'; }

    function operFmt(v) {
        return '<a class="btn btn-xs btn-primary" href="' + editUrl + '?ids=' + v + '">编辑</a> ' +
            '<button class="btn btn-xs btn-danger" data-id="' + v + '" type="button">删除</button>';
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
                    { field: 'name', title: '物料名称', width: 150 },
                    { field: 'code', title: '物料编码', width: 120 },
                    { field: 'unit', title: '单位', width: 80 },
                    { field: 'current_price', title: '当前价格', width: 100 },
                    { field: 'stock', title: '库存', width: 100 },
                    { field: 'status', title: '状态', width: 100, formatter: statusFmt },
                    { field: 'create_time', title: '创建时间', width: 150 },
                    { field: 'id', title: '操作', width: 150, formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除该物料？')) return;
                $.post(delUrl, { ids: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
