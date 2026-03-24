(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            var customerId = '';
            try { customerId = new URLSearchParams(window.location.search).get('customer_id') || ''; } catch (e) {}
            var url = base + '/crm/contact/index';
            if (customerId) url += '?customer_id=' + customerId;
            table.bootstrapTable({
                url: url,
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                pageList: [10, 20, 50],
                sortName: 'id',
                sortOrder: 'desc',
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                columns: [
                    { field: 'id', title: 'ID', width: 80, sortable: true },
                    { field: 'customer_name', title: '客户', align: 'left' },
                    { field: 'name', title: '姓名', align: 'left' },
                    { field: 'role', title: '职位', align: 'left' },
                    { field: 'phone', title: '电话', align: 'left' },
                    { field: 'email', title: '邮箱', align: 'left' },
                    { field: 'is_main', title: '主联系人', width: 100, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">是</span>' : '<span class="badge badge-secondary">否</span>'; }},
                    { field: 'create_time', title: '创建时间', width: 180, formatter: function (v) { return v ? new Date(v * 1000).toLocaleString('zh-CN') : ''; }},
                    { field: 'operate', title: '操作', width: 120, events: {
                        'click .btn-edit': function (e, v, row) { location.href = base + '/crm/contact/edit?id=' + row.id; },
                        'click .btn-del': function (e, v, row) {
                            if (confirm('确定要删除吗？')) {
                                $.post(base + '/crm/contact/del', { ids: row.id }, function (r) {
                                    if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); } else alert(r.msg || '删除失败');
                                }, 'json');
                            }
                        }
                    }, formatter: function (v, row) {
                        return '<a href="' + base + '/crm/contact/edit?id=' + row.id + '" class="btn btn-xs btn-success btn-edit">编辑</a> <a href="javascript:;" class="btn btn-xs btn-danger btn-del">删除</a>';
                    }}
                ]
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '#toolbar .btn-edit').on('click', '#toolbar .btn-edit', function () {
                var rows = table.bootstrapTable('getSelections');
                if (rows.length != 1) { alert('请选择一条记录'); return; }
                location.href = base + '/crm/contact/edit?id=' + rows[0].id;
            });
            $(document).off('click', '#toolbar .btn-del').on('click', '#toolbar .btn-del', function () {
                var rows = table.bootstrapTable('getSelections');
                if (rows.length == 0) { alert('请选择要删除的记录'); return; }
                if (!confirm('确定要删除选中的 ' + rows.length + ' 条记录吗？')) return;
                $.post(base + '/crm/contact/del', { ids: rows.map(function (r) { return r.id; }).join(',') }, function (r) {
                    if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); } else alert(r.msg || '删除失败');
                }, 'json');
            });
            table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
                $('.btn-edit, .btn-del').toggleClass('disabled btn-disabled', table.bootstrapTable('getSelections').length === 0);
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
                $.post(base + '/crm/contact/edit?ids=' + id, form.serialize(), function (r) {
                    if (r.code == 1) {
                        alert(r.msg || '保存成功');
                        location.href = base + '/crm/contact/index';
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
