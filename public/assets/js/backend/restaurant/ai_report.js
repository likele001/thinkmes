(function () {
    var Controller = {
        index: function () {
            var table = $('#table');
            if (!table.length) return;
            function tenantId() { return ($('#tenant-id').val() || ''); }
            function tableUrl() {
                var u = Config.moduleurl + '/restaurant/ai_report/index';
                var tid = tenantId();
                if (tid) u += (u.indexOf('?') === -1 ? '?' : '&') + 'tenant_id=' + encodeURIComponent(tid);
                return u;
            }
            table.bootstrapTable({
                url: tableUrl(),
                method: 'get',
                sidePagination: 'server',
                pagination: false,
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                columns: [
                    { field: 'report_date', title: '日期', width: 140 },
                    { field: 'summary', title: '摘要', align: 'left' },
                    { field: 'content', title: '全文', align: 'left', formatter: function (v) { return '<pre style="white-space:pre-wrap;max-height:220px;overflow:auto;margin:0">' + (v || '') + '</pre>'; } }
                ]
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () { table.bootstrapTable('refresh', { url: tableUrl() }); });
            $('#tenant-id').off('change').on('change', function () { table.bootstrapTable('refresh', { url: tableUrl() }); });
            $('#btn-generate').off('click').on('click', function () {
                var d = $('#report-date').val() || '';
                var tid = tenantId();
                $.post(Config.moduleurl + '/restaurant/ai_report/generate', { date: d, tenant_id: tid }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已生成' : '失败'));
                    if (r.code == 1) table.bootstrapTable('refresh', { url: tableUrl() });
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
