(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/mes/mrp/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                sortName: 'material_id',
                sortOrder: 'asc',
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.list || [] };
                },
                columns: [
                    { field: 'material_id', title: '物料ID', width: 80 },
                    { field: 'material_name', title: '物料名称', align: 'left' },
                    { field: 'material_code', title: '编码', width: 120 },
                    { field: 'require_qty', title: '需求数量', width: 100 },
                    { field: 'stock_qty', title: '当前库存', width: 100 },
                    { field: 'shortage', title: '缺料数量', width: 100 },
                    { field: 'order_nos', title: '关联订单', align: 'left' }
                ]
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () { table.bootstrapTable('refresh'); });
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
