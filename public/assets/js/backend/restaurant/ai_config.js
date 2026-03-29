(function () {
    var Controller = {
        index: function () {
            var table = $('#table');
            if (!table.length) return;
            table.bootstrapTable({
                url: Config.moduleurl + '/restaurant/ai_config/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                sortName: 'id',
                sortOrder: 'desc',
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                columns: [
                    { checkbox: true },
                    { field: 'id', title: 'ID', width: 80 },
                    { field: 'provider', title: '供应商', width: 100 },
                    { field: 'model', title: '模型', width: 160 },
                    { field: 'api_base', title: 'API Base', align: 'left' },
                    { field: 'api_key_masked', title: 'API Key', width: 120 },
                    { field: 'status', title: '状态', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-secondary">禁用</span>'; } },
                    { field: 'operate', title: '操作', width: 220, formatter: function (v, row) {
                        return '<a href="' + Config.moduleurl + '/restaurant/ai_config/edit?id=' + row.id + '" class="btn btn-xs btn-success">编辑</a> ' +
                            '<a href="javascript:;" class="btn btn-xs btn-info btn-test-one" data-id="' + row.id + '">测试</a> ' +
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
                $('#toolbar .btn-edit,#toolbar .btn-del,#toolbar .btn-test').toggleClass('disabled', !enable).toggleClass('btn-disabled', !enable);
            });

            $('#toolbar .btn-edit').off('click').on('click', function () {
                var id = getSelectedId();
                if (!id) return;
                location.href = Config.moduleurl + '/restaurant/ai_config/edit?id=' + id;
            });
            $('#toolbar .btn-del').off('click').on('click', function () {
                var id = getSelectedId();
                if (!id) return;
                if (!confirm('确定删除？')) return;
                $.post(Config.moduleurl + '/restaurant/ai_config/del', { ids: id }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已删除' : '失败'));
                    if (r.code == 1) table.bootstrapTable('refresh');
                }, 'json');
            });
            function testId(id) {
                $.post(Config.moduleurl + '/restaurant/ai_config/test', { id: id }, function (r) {
                    alert(r.msg || (r.code == 1 ? ('测试成功：' + (r.data && r.data.reply ? r.data.reply : 'OK')) : '测试失败'));
                }, 'json');
            }
            $('#toolbar .btn-test').off('click').on('click', function () {
                var id = getSelectedId();
                if (!id) return;
                testId(id);
            });

            $(document).off('click', '.btn-test-one').on('click', '.btn-test-one', function () { testId($(this).data('id')); });
            $(document).off('click', '.btn-del-one').on('click', '.btn-del-one', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(Config.moduleurl + '/restaurant/ai_config/del', { ids: id }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已删除' : '失败'));
                    if (r.code == 1) table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
