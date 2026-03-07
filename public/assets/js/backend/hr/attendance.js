(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/hr/attendance/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                sortName: 'day',
                sortOrder: 'desc',
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                columns: [
                    { field: 'id', title: 'ID', width: 80 },
                    { field: 'employee_name', title: '员工', width: 100 },
                    { field: 'day', title: '日期', width: 100 },
                    { field: 'clock_in', title: '上班打卡', width: 160, formatter: function (v) { return v ? new Date(v * 1000).toLocaleString('zh-CN') : '-'; }},
                    { field: 'clock_out', title: '下班打卡', width: 160, formatter: function (v) { return v ? new Date(v * 1000).toLocaleString('zh-CN') : '-'; }}
                ]
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () { table.bootstrapTable('refresh'); });
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
