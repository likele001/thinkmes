(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/tenant_package/index';
    var editUrl = base + '/tenant_package/edit';
    var delUrl = base + '/tenant_package/del';

    var featureUrl = base + '/tenant_package_feature/index';
    function operFmt(v, row) {
        return '<a class="btn btn-xs btn-info" href="' + featureUrl + '?package_id=' + v + '" title="管理功能"><i class="fas fa-cog"></i> 功能</a> ' +
            '<a class="btn btn-xs btn-primary" href="' + editUrl + '?id=' + v + '">编辑</a> ' +
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
                    { field: 'name', title: '套餐名称' },
                    { field: 'max_admin', title: '最大管理员数' },
                    { field: 'max_user', title: '最大用户数' },
                    { field: 'expire_days_text', title: '默认有效期' },
                    { field: 'sort', title: '排序' },
                    { field: 'id', title: '操作', formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除该套餐？删除前请确保没有租户使用此套餐。')) return;
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
