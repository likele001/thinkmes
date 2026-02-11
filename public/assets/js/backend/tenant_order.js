(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/tenant_order/index';
    var payUrl = base + '/tenant_order/pay';
    var cancelUrl = base + '/tenant_order/cancel';

    function statusFmt(v) {
        var map = {0: '<span class="badge badge-warning">待支付</span>', 1: '<span class="badge badge-success">已支付</span>', 2: '<span class="badge badge-secondary">已取消</span>', 3: '<span class="badge badge-danger">已退款</span>'};
        return map[v] || v;
    }
    function operFmt(v, row) {
        var html = '';
        if (row.status == 0) {
            html += '<button class="btn btn-xs btn-success btn-pay" data-id="' + v + '" type="button">确认支付</button> ';
            html += '<button class="btn btn-xs btn-warning btn-cancel" data-id="' + v + '" type="button">取消</button>';
        }
        return html;
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            $table.bootstrapTable({
                url: indexUrl,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'order_no', title: '订单号' },
                    { field: 'tenant_name', title: '租户' },
                    { field: 'package_name', title: '套餐' },
                    { field: 'type_text', title: '类型' },
                    { field: 'amount', title: '金额' },
                    { field: 'status', title: '状态', formatter: statusFmt },
                    { field: 'pay_time_text', title: '支付时间' },
                    { field: 'create_time_text', title: '创建时间' },
                    { field: 'id', title: '操作', formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-pay').on('click', '#table button.btn-pay', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确认该订单已支付？')) return;
                $.post(payUrl, { id: id, pay_method: 'manual' }, function (r) {
                    alert(r.msg || (r.code === 1 ? '操作成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
            $(document).off('click', '#table button.btn-cancel').on('click', '#table button.btn-cancel', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定取消该订单？')) return;
                $.post(cancelUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '操作成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
