(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var standardUrl = base + '/mes/quality/standard';
    var checkUrl = base + '/mes/quality/check';
    var statisticsUrl = base + '/mes/quality/statistics';

    function statusFmt(v) {
        var statusMap = {
            0: '<span class="badge badge-warning">待质检</span>',
            1: '<span class="badge badge-success">已质检</span>',
            2: '<span class="badge badge-danger">返工</span>'
        };
        return statusMap[v] || '未知';
    }

    var Controller = {
        standard: function () {
            var $table = $('#table');
            $table.bootstrapTable({
                url: standardUrl,
                columns: [
                    {field: 'id', title: 'ID', width: 80},
                    {field: 'name', title: '标准名称'},
                    {field: 'process.name', title: '工序', width: 120},
                    {field: 'model.name', title: '型号', width: 150},
                    {field: 'qualified_rate', title: '合格率要求(%)', width: 150}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
        },
        check: function () {
            var $table = $('#table');
            $table.bootstrapTable({
                url: checkUrl,
                columns: [
                    {field: 'id', title: 'ID', width: 80},
                    {field: 'check_no', title: '质检单号', width: 150},
                    {field: 'report.allocation.order.order_no', title: '订单号', width: 150},
                    {field: 'check_quantity', title: '检验数量', width: 100},
                    {field: 'qualified_quantity', title: '合格数量', width: 100},
                    {field: 'unqualified_quantity', title: '不合格数量', width: 120},
                    {field: 'qualified_rate', title: '合格率(%)', width: 120},
                    {field: 'check_time', title: '质检时间', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                    }}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
        },
        statistics: function () {
            var $table = $('#table');
            $table.bootstrapTable({
                url: statisticsUrl,
                columns: [
                    {field: 'stat_date', title: '日期', width: 120},
                    {field: 'total_count', title: '检验总数', width: 100},
                    {field: 'total_qualified', title: '合格总数', width: 100},
                    {field: 'total_unqualified', title: '不合格总数', width: 120},
                    {field: 'avg_qualified_rate', title: '平均合格率(%)', width: 150}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
        }
    };

    window.__backendController = Controller;
})();
