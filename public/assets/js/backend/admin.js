/**
 * 管理员管理页：表格初始化与操作（仿 FastAdmin 页面 JS）
 * 所有链接从 table_index_url 派生，避免 /admin/admin/admin/edit 多一层
 */
(function () {
    var indexUrl = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : '';
    var base = indexUrl ? indexUrl.replace(/\/index\/?(\?.*)?$/, '') : '';
    var addUrl = base ? base + '/add' : '';
    var editUrl = base ? base + '/edit' : '';
    var delUrl = base ? base + '/del' : '';

    function statusFmt(v) { return v == 1 ? '正常' : '禁用'; }
    function operFmt(v) {
        return '<a class="btn btn-xs btn-primary" href="' + editUrl + '?id=' + v + '">编辑</a> ' +
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
                    { field: 'username', title: '账号' },
                    { field: 'nickname', title: '昵称' },
                    { field: 'tenant_name', title: '租户' },
                    { field: 'pid', title: '父级ID', width: 70 },
                    { field: 'data_scope_text', title: '数据权限' },
                    { field: 'role_ids', title: '角色' },
                    { field: 'login_time', title: '最后登录' },
                    { field: 'status', title: '状态', formatter: statusFmt },
                    { field: 'id', title: '操作', formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除？')) return;
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };

    window.__backendController = Controller;
})();
