(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/crm/customer_tag/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                sortName: 'sort',
                sortOrder: 'asc',
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                columns: [
                    { checkbox: true },
                    { field: 'id', title: 'ID', width: 80 },
                    { field: 'name', title: '标签名称', align: 'left' },
                    { field: 'color', title: '颜色', width: 100, formatter: function (v) { return v ? '<span class="badge" style="background-color:' + v + '">' + v + '</span>' : ''; }},
                    { field: 'sort', title: '排序', width: 80 },
                    { field: 'operate', title: '操作', width: 120, formatter: function (v, row) {
                        return '<a href="' + base + '/crm/customer_tag/edit?id=' + row.id + '" class="btn btn-xs btn-success">编辑</a> <a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
                    }}
                ]
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '#toolbar .btn-edit').on('click', '#toolbar .btn-edit', function () {
                var rows = table.bootstrapTable('getSelections');
                if (rows.length != 1) { alert('请选择一条记录'); return; }
                location.href = base + '/crm/customer_tag/edit?id=' + rows[0].id;
            });
            $(document).off('click', '.btn-del').on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(base + '/crm/customer_tag/del', { ids: id }, function (r) { if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); } else alert(r.msg || '删除失败'); }, 'json');
            });
            table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
                var rows = table.bootstrapTable('getSelections');
                $('#toolbar .btn-edit').toggleClass('disabled btn-disabled', rows.length !== 1);
                $('#toolbar .btn-del').toggleClass('disabled btn-disabled', rows.length === 0);
            });
        },
        add: function () {
            if (window.BackendUtil) window.BackendUtil.initGenericAddForm();
        },
        edit: function () {
            var $ = jQuery, form = $('#form-edit');
            if (!form.length) return;
            form.off('submit').on('submit', function (e) {
                e.preventDefault();
                var id = form.data('id');
                $.post(base + '/crm/customer_tag/edit?ids=' + id, form.serialize(), function (r) {
                    if (r.code == 1) {
                        alert(r.msg || '保存成功');
                        location.href = base + '/crm/customer_tag/index';
                    } else {
                        alert(r.msg || '保存失败');
                    }
                }, 'json');
            });
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
