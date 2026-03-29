(function () {
    var Controller = {
        index: function () {
            var table = $('#table');
            if (!table.length) return;
            table.bootstrapTable({
                url: Config.moduleurl + '/restaurant/review_template/index',
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
                    { field: 'platform', title: '平台', width: 90, formatter: function (v) { return v || '全平台'; } },
                    { field: 'scene', title: '场景', width: 80, formatter: function (v) { return v === 'bad' ? '差评' : '好评'; } },
                    { field: 'rating_min', title: '最小分', width: 80 },
                    { field: 'rating_max', title: '最大分', width: 80 },
                    { field: 'status', title: '状态', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-secondary">禁用</span>'; } },
                    { field: 'template', title: '模板', align: 'left', formatter: function (v) { return '<div style="max-width:640px;white-space:normal">' + (v || '') + '</div>'; } },
                    { field: 'operate', title: '操作', width: 160, formatter: function (v, row) {
                        return '<a href="' + Config.moduleurl + '/restaurant/review_template/edit?id=' + row.id + '" class="btn btn-xs btn-success">编辑</a> ' +
                            '<a href="javascript:;" class="btn btn-xs btn-danger btn-del-one" data-id="' + row.id + '">删除</a>';
                    } }
                ]
            });

            $('#toolbar .btn-refresh').off('click').on('click', function () { table.bootstrapTable('refresh'); });

            function getSelectedId() {
                var rows = table.bootstrapTable('getSelections') || [];
                return rows.length ? rows[0].id : null;
            }
            table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
                var id = getSelectedId();
                var enable = !!id;
                $('#toolbar .btn-edit,#toolbar .btn-del').toggleClass('disabled', !enable).toggleClass('btn-disabled', !enable);
            });
            $('#toolbar .btn-edit').off('click').on('click', function () {
                var id = getSelectedId();
                if (!id) return;
                location.href = Config.moduleurl + '/restaurant/review_template/edit?id=' + id;
            });
            $('#toolbar .btn-del').off('click').on('click', function () {
                var id = getSelectedId();
                if (!id) return;
                if (!confirm('确定删除？')) return;
                $.post(Config.moduleurl + '/restaurant/review_template/del', { ids: id }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已删除' : '失败'));
                    if (r.code == 1) table.bootstrapTable('refresh');
                }, 'json');
            });
            $(document).off('click', '.btn-del-one').on('click', '.btn-del-one', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(Config.moduleurl + '/restaurant/review_template/del', { ids: id }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已删除' : '失败'));
                    if (r.code == 1) table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
