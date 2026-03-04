/**
 * 支付订单列表（页面逻辑也可在视图内联，此文件满足 backend-loader 按 jsname 加载）
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var $table = $('#table');

    var Controller = {
        index: function () {
            if (!$table.length || typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
            $table.bootstrapTable({
                url: base + '/payment/order/index',
                method: 'get',
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.list || [] };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 70 },
                    { field: 'order_no', title: '订单号', width: 180 },
                    { field: 'title', title: '标题' },
                    { field: 'amount', title: '金额', width: 100 },
                    { field: 'gateway_name', title: '网关', width: 120 },
                    { field: 'status_text', title: '状态', width: 90 },
                    { field: 'third_order_id', title: '第三方单号', width: 140 },
                    { field: 'pay_time_text', title: '支付时间', width: 160 },
                    { field: 'create_time_text', title: '创建时间', width: 160 }
                ]
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });
        }
    };
    window.__backendController = Controller;
})();
