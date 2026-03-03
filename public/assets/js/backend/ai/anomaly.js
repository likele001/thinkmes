(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/ai/anomaly/index',
                method: 'get',
                pagination: false,
                sidePagination: 'client',
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.list || [] };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'report_id', title: '报工ID', width: 80 },
                    { field: 'anomaly_type', title: '异常类型', width: 100 },
                    { field: 'score', title: '分数', width: 80 },
                    { field: 'ai_reason', title: 'AI分析', align: 'left' },
                    { field: 'status', title: '状态', width: 80, formatter: function (v) {
                        if (v == 0) return '<span class="badge badge-warning">待处理</span>';
                        if (v == 1) return '<span class="badge badge-success">已确认</span>';
                        if (v == 2) return '<span class="badge badge-secondary">已忽略</span>';
                        return v;
                    }},
                    { field: 'create_time', title: '检测时间', width: 160, formatter: function (v) {
                        return v ? new Date(v * 1000).toLocaleString('zh-CN') : '';
                    }}
                ]
            });
        }
    };
    window.__backendController = Controller;
})();
