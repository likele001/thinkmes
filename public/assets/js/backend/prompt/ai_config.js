(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table-list');
            if (!table.length || typeof table.bootstrapTable !== 'function') return;
            table.bootstrapTable({
                url: base + '/prompt/ai_config/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.rows || d.list || [] };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'name', title: '配置名称' },
                    { field: 'provider', title: '服务商', width: 120 },
                    { field: 'api_base', title: 'API地址', formatter: function (v) { return v || '<span class="text-muted">默认</span>'; } },
                    { field: 'api_key_masked', title: 'API Key' },
                    { field: 'model', title: '模型', width: 160 },
                    { field: 'status', title: '状态', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-secondary">禁用</span>'; } },
                    { field: 'id', title: '操作', width: 200, formatter: function (v, row) {
                        return '<a href="' + base + '/prompt/ai_config/edit?id=' + v + '" class="btn btn-xs btn-success">编辑</a> ' +
                               '<a href="javascript:;" class="btn btn-xs btn-info btn-test" data-id="' + v + '">测试</a> ' +
                               '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + v + '">删除</a>';
                    }}
                ]
            });
            $('#btn-add').on('click', function () {
                layer.open({ type: 2, title: '添加AI配置', area: ['700px', '600px'], content: base + '/prompt/ai_config/add', end: function () { table.bootstrapTable('refresh'); } });
            });
            $(document).off('click', '.btn-edit').on('click', '.btn-edit', function () {
                var id = $(this).data('id');
                layer.open({ type: 2, title: '编辑AI配置', area: ['700px', '600px'], content: base + '/prompt/ai_config/edit?id=' + id, end: function () { table.bootstrapTable('refresh'); } });
            });
            $(document).off('click', '.btn-test').on('click', '.btn-test', function () {
                var id = $(this).data('id');
                var $btn = $(this);
                $btn.text('测试中...').prop('disabled', true);
                $.post(base + '/prompt/ai_config/test', { id: id }, function (r) {
                    $btn.text('测试').prop('disabled', false);
                    alert(r.msg || (r.code == 1 ? '连接成功' : '连接失败'));
                }, 'json');
            });
            $(document).off('click', '.btn-del').on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除？')) return;
                $.post(base + '/prompt/ai_config/del', { ids: id }, function (r) {
                    if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); }
                    else alert(r.msg || '删除失败');
                }, 'json');
            });
        },
        add: function () {
            if (window.BackendUtil) window.BackendUtil.initGenericAddForm();
        },
        edit: function () {
            if (window.BackendUtil) window.BackendUtil.initGenericEditForm();
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
