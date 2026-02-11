/**
 * 工资管理页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/wage/index';
    var statisticsUrl = base + '/mes/wage/statistics';

    var Controller = {
        index: function () {
            var $table = $('#table');
            $table.bootstrapTable({
                url: indexUrl,
                pk: 'id',
                sortName: 'work_date',
                sortOrder: 'desc',
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                columns: [
                    {field: 'id', title: 'ID', width: 80, sortable: true},
                    {field: 'work_date', title: '工作日期', width: 120, sortable: true},
                    {field: 'user_id', title: '员工ID', width: 100},
                    {field: 'work_type', title: '工作类型', width: 100, formatter: function(value) {
                        return value == 'piece' ? '<span class="badge badge-primary">计件</span>' : '<span class="badge badge-info">计时</span>';
                    }},
                    {field: 'quantity', title: '数量', width: 100, align: 'right', formatter: function(value, row) {
                        return row.work_type == 'piece' ? value : '-';
                    }},
                    {field: 'work_hours', title: '工时', width: 100, align: 'right', formatter: function(value, row) {
                        return row.work_type == 'time' ? parseFloat(value).toFixed(2) : '-';
                    }},
                    {field: 'unit_price', title: '单价', width: 100, align: 'right', formatter: function(value) {
                        return parseFloat(value).toFixed(2);
                    }},
                    {field: 'total_wage', title: '总工资', width: 120, align: 'right', formatter: function(value) {
                        return '<span class="text-danger font-weight-bold">¥' + parseFloat(value).toFixed(2) + '</span>';
                    }},
                    {field: 'create_time', title: '创建时间', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                    }}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });
        },
        statistics: function () {
            Controller.api.loadStatistics();
        },
        api: {
            loadStatistics: function () {
                // 统计页面逻辑
            }
        }
    };

    window.__backendController = Controller;
})();
