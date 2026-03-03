(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/ai/qa/index',
                method: 'get',
                pagination: false,
                sidePagination: 'client',
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.list || [] };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'question', title: '问题', align: 'left' },
                    { field: 'answer', title: '回答', align: 'left', formatter: function (v) {
                        return v ? (v.length > 100 ? v.substring(0, 100) + '...' : v) : '';
                    }},
                    { field: 'create_time', title: '时间', width: 160, formatter: function (v) {
                        return v ? new Date(v * 1000).toLocaleString('zh-CN') : '';
                    }}
                ]
            });
            $('#btn-ask').on('click', function () {
                var q = $('#question').val().trim();
                if (!q) { alert('请输入问题'); return; }
                $(this).prop('disabled', true);
                $.post(base + '/ai/qa/ask', { question: q }, function (r) {
                    $('#btn-ask').prop('disabled', false);
                    if (r.code == 1 && r.data && r.data.answer) {
                        $('#answer-area').html(r.data.answer).show();
                        table.bootstrapTable('refresh');
                    } else {
                        alert(r.msg || '请求失败');
                    }
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
