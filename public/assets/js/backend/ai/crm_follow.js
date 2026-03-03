(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/ai/crm_follow/index',
                method: 'get',
                pagination: false,
                sidePagination: 'client',
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.list || [] };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'customer_id', title: '客户ID', width: 80 },
                    { field: 'opportunity_id', title: '商机ID', width: 80 },
                    { field: 'suggestion_type', title: '类型', width: 100 },
                    { field: 'content', title: '建议内容', align: 'left', formatter: function (v) {
                        return v ? (v.length > 80 ? v.substring(0, 80) + '...' : v) : '';
                    }},
                    { field: 'intent_score', title: '意向分', width: 80 },
                    { field: 'create_time', title: '时间', width: 160, formatter: function (v) {
                        return v ? new Date(v * 1000).toLocaleString('zh-CN') : '';
                    }}
                ]
            });
        }
    };
    window.__backendController = Controller;
})();
