(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table-list');
            if (!table.length || typeof table.bootstrapTable !== 'function') return;
            table.bootstrapTable({
                url: base + '/prompt/quota/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                queryParams: function (p) {
                    return $.extend(p, { keyword: $('#search-keyword').val() });
                },
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.rows || d.list || [] };
                },
                columns: [
                    { field: 'user_id', title: '用户ID', width: 80 },
                    { field: 'username', title: '用户名', width: 110 },
                    { field: 'nickname', title: '昵称', width: 110 },
                    { field: 'mobile', title: '手机', width: 120 },
                    { field: 'free_quota', title: '免费剩余', width: 90, formatter: function (v) { return '<span class="text-info">' + v + '</span>'; } },
                    { field: 'paid_quota', title: '付费剩余', width: 90, formatter: function (v) { return '<span class="text-success">' + v + '</span>'; } },
                    { field: 'total_used', title: '累计使用', width: 90 },
                    { field: 'user_id', title: '操作', width: 120, formatter: function (v) {
                        return '<a href="javascript:;" class="btn btn-xs btn-warning btn-adjust" data-id="' + v + '">调整额度</a>';
                    }}
                ]
            });
            $('#btn-search, #search-keyword').on('click keypress', function (e) { if (e.type === 'click' || e.which === 13) table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-adjust').on('click', '.btn-adjust', function () {
                var uid = $(this).data('id');
                layer.prompt({ title: '调整额度（正数增加，负数减少）', formType: 0, value: 'paid:+10' }, function (val, idx) {
                    layer.close(idx);
                    var parts = val.split(':');
                    var type = parts[0] || 'paid';
                    var amount = parseInt(parts[1]) || 0;
                    $.post(base + '/prompt/quota/adjust', { user_id: uid, type: type, amount: amount }, function (r) {
                        alert(r.msg || (r.code == 1 ? '操作成功' : '操作失败'));
                        if (r.code == 1) table.bootstrapTable('refresh');
                    }, 'json');
                });
            });
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
