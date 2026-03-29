(function () {
    var L = (typeof Config !== 'undefined' && Config.lang) ? Config.lang : {};
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var packageId = (new URLSearchParams(window.location.search)).get('package_id') || 0;
    var indexUrl = base + '/tenant_package_feature/index?package_id=' + packageId;
    var delUrl = base + '/tenant_package_feature/del';
    var multiUrl = base + '/tenant_package_feature/multi';

    function operFmt(v, row) {
        var statusBtn = row.is_enabled == 1
            ? '<button class="btn btn-xs btn-warning btn-toggle" data-id="' + v + '" data-status="0" type="button">禁用</button>'
            : '<button class="btn btn-xs btn-success btn-toggle" data-id="' + v + '" data-status="1" type="button">启用</button>';
        var delBtn = '<button class="btn btn-xs btn-danger btn-delete" data-id="' + v + '" type="button">删除</button>';
        return statusBtn + ' ' + delBtn;
    }

    function statusFmt(v) {
        return v == 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-danger">禁用</span>';
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
                    { checkbox: true, width: 40 },
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'feature_code', title: L.feature_code || '功能代码' },
                    { field: 'feature_name', title: L.feature_name || '功能名称' },
                    { field: 'is_enabled', title: L.status || '状态', width: 80, formatter: statusFmt },
                    { field: 'id', title: L.operation || '操作', width: 150, formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });

            // 批量启用
            $(document).off('click', '#toolbar .btn-enable').on('click', '#toolbar .btn-enable', function () {
                var ids = $table.bootstrapTable('getSelections').map(function (row) { return row.id; });
                if (ids.length === 0) return alert(L.select_rows || '请选择要操作的记录');
                if (!confirm(L.confirm_enable || '确定启用选中的功能？')) return;
                $.post(multiUrl, { ids: ids.join(','), action: 'enable' }, function (r) {
                    alert(r.msg || (r.code === 1 ? (L.operate_success || '操作成功') : '操作失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });

            // 批量禁用
            $(document).off('click', '#toolbar .btn-disable').on('click', '#toolbar .btn-disable', function () {
                var ids = $table.bootstrapTable('getSelections').map(function (row) { return row.id; });
                if (ids.length === 0) return alert(L.select_rows || '请选择要操作的记录');
                if (!confirm(L.confirm_disable || '确定禁用选中的功能？')) return;
                $.post(multiUrl, { ids: ids.join(','), action: 'disable' }, function (r) {
                    alert(r.msg || (r.code === 1 ? (L.operate_success || '操作成功') : '操作失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });

            // 单条切换状态
            $(document).off('click', '#table .btn-toggle').on('click', '#table .btn-toggle', function () {
                var id = $(this).data('id');
                var status = $(this).data('status');
                if (!id) return;
                $.post(multiUrl, { ids: id, action: status == 1 ? 'enable' : 'disable' }, function (r) {
                    alert(r.msg || (r.code === 1 ? (L.operate_success || '操作成功') : '操作失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });

            // 删除按钮
            $(document).off('click', '#table .btn-delete').on('click', '#table .btn-delete', function () {
                var id = $(this).data('id');
                if (!id || !confirm(L.confirm_del_feature || '确定删除该功能？')) return;
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? (L.delete_success || '删除成功') : '操作失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
