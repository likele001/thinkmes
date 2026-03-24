/**
 * MES BI 报表页面 JS
 * 大屏（dashboard）由 bi-dashboard.js + ECharts 独立渲染
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';

    // 通用日期范围查询表格
    function initDateRangeTable(apiPath, columns) {
        var $table = $('#table');

        function loadTable() {
            var startDate = $('#start_date').val();
            var endDate   = $('#end_date').val();
            if (typeof $table.bootstrapTable !== 'function') return;
            if ($table.data('bootstrap.table')) $table.bootstrapTable('destroy');
            $table.bootstrapTable({
                url: base + apiPath + '?start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate),
                pk: 'id',
                sidePagination: 'client',
                pagination: true,
                pageSize: 20,
                pageList: [20, 50, 100],
                responseHandler: function (res) {
                    if (res && res.code === 1 && res.data) return res.data.list || [];
                    return [];
                },
                columns: columns
            });
        }

        $(document).off('click', '#btn-query').on('click', '#btn-query', function () { loadTable(); });
        loadTable();
    }

    var Controller = {

        // 大屏，由 bi-dashboard.js + ECharts 渲染
        dashboard: function () {},

        // 成本分析
        costAnalysis: function () {
            initDateRangeTable('/mes/bi/costAnalysis', [
                {field: 'order_no',       title: '订单号',   align: 'left'},
                {field: 'order_name',     title: '订单名称', align: 'left'},
                {field: 'material_cost',  title: '物料成本', width: 120, align: 'right', formatter: function (v) { return '¥' + parseFloat(v || 0).toFixed(2); }},
                {field: 'labor_cost',     title: '人工成本', width: 120, align: 'right', formatter: function (v) { return '¥' + parseFloat(v || 0).toFixed(2); }},
                {field: 'total_cost',     title: '总成本',   width: 150, align: 'right', formatter: function (v) {
                    return '<span class="text-danger font-weight-bold">¥' + parseFloat(v || 0).toFixed(2) + '</span>';
                }}
            ]);
        },

        // 质量分析
        qualityAnalysis: function () {
            initDateRangeTable('/mes/bi/qualityAnalysis', [
                {field: 'stat_date',        title: '日期',     sortable: true},
                {field: 'total_count',      title: '总报工数', width: 120, align: 'right'},
                {field: 'qualified_count',  title: '合格数',   width: 120, align: 'right'},
                {field: 'unqualified_count',title: '不合格数', width: 120, align: 'right'},
                {field: 'qualified_rate',   title: '合格率(%)',width: 120, align: 'right', formatter: function (v) {
                    var color = v >= 95 ? 'success' : (v >= 90 ? 'warning' : 'danger');
                    return '<span class="badge badge-' + color + '">' + parseFloat(v || 0).toFixed(2) + '%</span>';
                }}
            ]);
        },

        // 生产效率
        productionEfficiency: function () {
            initDateRangeTable('/mes/bi/productionEfficiency', [
                {field: 'stat_date',     title: '日期',     sortable: true},
                {field: 'worker_count',  title: '工人数',   width: 100, align: 'right'},
                {field: 'total_quantity',title: '总产量',   width: 120, align: 'right'},
                {field: 'total_hours',   title: '总工时',   width: 120, align: 'right', formatter: function (v) { return parseFloat(v || 0).toFixed(2); }},
                {field: 'total_wage',    title: '总工资',   width: 120, align: 'right', formatter: function (v) { return '¥' + parseFloat(v || 0).toFixed(2); }},
                {field: 'report_count',  title: '报工次数', width: 100, align: 'right'},
                {field: 'efficiency',    title: '人均产量', width: 120, align: 'right', formatter: function (v, row) {
                    return row.worker_count > 0 ? (row.total_quantity / row.worker_count).toFixed(2) : '0.00';
                }}
            ]);
        },

        // 小写别名：驼峰方法经 strtolower 后的形式
        productionefficiency: function () { Controller.productionEfficiency(); },
        qualityanalysis:      function () { Controller.qualityAnalysis(); },
        costanalysis:         function () { Controller.costAnalysis(); },
        getdashboarddata:     function () { /* AJAX 接口，无视图 */ },
        syncprogress:         function () { /* AJAX 接口 */ }
    };

    window.__backendController = Controller;
})();
