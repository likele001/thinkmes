/**
 * 管理员管理页：表格初始化与操作（多语言从 Config.lang 读取）
 */
(function () {
    var L = (typeof Config !== 'undefined' && Config.lang) ? Config.lang : {};
    var indexUrl = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : '';
    var base = indexUrl ? indexUrl.replace(/\/index\/?(\?.*)?$/, '') : '';
    var addUrl = base ? base + '/add' : '';
    var editUrl = base ? base + '/edit' : '';
    var delUrl = base ? base + '/del' : '';

    function statusFmt(v) { return v == 1 ? (L.status_normal || '正常') : (L.status_disabled || '禁用'); }
    function operFmt(v) {
        return '<a class="btn btn-xs btn-primary" href="' + editUrl + '?id=' + v + '">' + (L.edit || '编辑') + '</a> ' +
            '<button class="btn btn-xs btn-danger" data-id="' + v + '" type="button">' + (L.delete || '删除') + '</button>';
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
                    { field: 'username', title: L.username || '账号' },
                    { field: 'nickname', title: L.nickname || '昵称' },
                    { field: 'tenant_name', title: L.tenant || '租户' },
                    { field: 'pid', title: L.parent_id || '父级ID', width: 70 },
                    { field: 'data_scope_text', title: L.data_scope || '数据权限' },
                    { field: 'role_ids', title: L.role || '角色' },
                    { field: 'login_time', title: L.last_login || '最后登录' },
                    { field: 'status', title: L.status || '状态', formatter: statusFmt },
                    { field: 'id', title: L.operation || '操作', formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm(L.confirm_del || '确定删除？')) return;
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? (L.delete_success || '删除成功') : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };

    window.__backendController = Controller;
})();
