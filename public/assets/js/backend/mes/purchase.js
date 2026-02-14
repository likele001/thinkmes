(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var requestUrl = base + '/admin/mes/purchase/request';
    var inboundUrl = base + '/mes/purchase/inbound';

    function reqStatusFmt(v) {
        var statusMap = {
            0: '<span class="badge badge-warning">待审核</span>',
            1: '<span class="badge badge-info">已审核</span>',
            2: '<span class="badge badge-success">已采购</span>',
            3: '<span class="badge badge-danger">已取消</span>'
        };
        return statusMap[v] || '未知';
    }

    function inStatusFmt(v) {
        var statusMap = {
            0: '<span class="badge badge-warning">待入库</span>',
            1: '<span class="badge badge-success">已入库</span>',
            2: '<span class="badge badge-danger">已退货</span>'
        };
        return statusMap[v] || '未知';
    }

    var Controller = {
        requestList: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }
            $table.bootstrapTable({
                url: requestUrl,
                columns: [
                    {field: 'id', title: 'ID', width: 80},
                    {field: 'request_no', title: '申请单号', width: 150},
                    {field: 'material.name', title: '物料名称'},
                    {field: 'required_quantity', title: '需求数量', width: 100},
                    {field: 'status', title: '状态', width: 100, formatter: reqStatusFmt}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
        },
        inbound: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }
            $table.bootstrapTable({
                url: inboundUrl,
                columns: [
                    {field: 'id', title: 'ID', width: 80},
                    {field: 'in_no', title: '入库单号', width: 150},
                    {field: 'material.name', title: '物料名称'},
                    {field: 'supplier.name', title: '供应商', width: 120},
                    {field: 'in_quantity', title: '入库数量', width: 100},
                    {field: 'total_amount', title: '总金额', width: 120},
                    {field: 'in_time', title: '入库时间', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                    }},
                    {field: 'status', title: '状态', width: 100, formatter: inStatusFmt}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
        }
    };

    window.__backendController = Controller;
})();
