(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/ai/daily_report/index',
                method: 'get',
                pagination: false,
                sidePagination: 'client',
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.list || [] };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'report_type', title: '类型', width: 80, formatter: function (v) {
                        return v === 'weekly' ? '周报' : '日报';
                    }},
                    { field: 'report_date', title: '日期', width: 120 },
                    { field: 'summary', title: '摘要', align: 'left' },
                    { field: 'create_time', title: '生成时间', width: 160, formatter: function (v) {
                        return v ? new Date(v * 1000).toLocaleString('zh-CN') : '';
                    }}
                ]
            });
        }
    };
    window.__backendController = Controller;
})();
