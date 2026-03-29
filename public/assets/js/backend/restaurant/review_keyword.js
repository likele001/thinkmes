(function () {
    var Controller = {
        index: function () {
            var table = $('#table');
            if (!table.length) return;
            table.bootstrapTable({
                url: Config.moduleurl + '/restaurant/review_keyword/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                sortName: 'weight',
                sortOrder: 'desc',
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                columns: [
                    { checkbox: true },
                    { field: 'id', title: 'ID', width: 70 },
                    { field: 'keyword', title: '关键词', width: 160 },
                    { field: 'category', title: '分类', width: 120 },
                    { field: 'weight', title: '权重', width: 80 },
                    { field: 'status', title: '状态', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-secondary">禁用</span>'; } }
                ]
            });
            $('#toolbar .btn-refresh').off('click').on('click', function () { table.bootstrapTable('refresh'); });
            function getSelectedId() { var rows = table.bootstrapTable('getSelections') || []; return rows.length ? rows[0].id : null; }
            table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
                var id = getSelectedId();
                var enable = !!id;
                $('#toolbar .btn-edit,#toolbar .btn-del').toggleClass('disabled', !enable).toggleClass('btn-disabled', !enable);
            });
            $('#toolbar .btn-edit').off('click').on('click', function () {
                var id = getSelectedId();
                if (!id) return;
                location.href = Config.moduleurl + '/restaurant/review_keyword/edit?id=' + id;
            });
            $('#toolbar .btn-del').off('click').on('click', function () {
                var id = getSelectedId();
                if (!id) return;
                if (!confirm('确定删除？')) return;
                $.post(Config.moduleurl + '/restaurant/review_keyword/del', { ids: id }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已删除' : '失败'));
                    if (r.code == 1) table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
