(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/shipment/index';
    var addUrl = base + '/mes/shipment/add';
    var editUrl = base + '/mes/shipment/edit';
    var delUrl = base + '/mes/shipment/del';
    var trackUrl = base + '/mes/shipment/track';

    function statusFmt(v) {
        var statusMap = {
            0: '<span class="badge badge-warning">待发货</span>',
            1: '<span class="badge badge-info">已发货</span>',
            2: '<span class="badge badge-success">已签收</span>',
            3: '<span class="badge badge-danger">已退回</span>'
        };
        return statusMap[v] || '未知';
    }

    function operFmt(value, row) {
        var html = '<a href="' + editUrl + '?ids=' + row.id + '" class="btn btn-xs btn-success btn-edit">编辑</a> ';
        html += '<a href="' + trackUrl + '?id=' + row.id + '" class="btn btn-xs btn-info">跟踪</a> ';
        html += '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
        return html;
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }
            $table.bootstrapTable({
                url: indexUrl,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                columns: [
                    {checkbox: true},
                    {field: 'id', title: 'ID', width: 80, sortable: true},
                    {field: 'shipment_no', title: '发货单号', width: 150},
                    {field: 'order.order_no', title: '订单号', width: 150},
                    {field: 'customer.customer_name', title: '客户名称', width: 120},
                    {field: 'shipment_quantity', title: '发货数量', width: 100},
                    {field: 'logistics_company', title: '物流公司', width: 120},
                    {field: 'logistics_no', title: '物流单号', width: 150},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {field: 'shipment_time', title: '发货时间', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                    }},
                    {field: 'operate', title: '操作', width: 250, events: {
                        'click .btn-edit': function(e, value, row) {
                            location.href = editUrl + '?ids=' + row.id;
                        },
                        'click .btn-del': function(e, value, row) {
                            if (confirm('确定要删除吗？')) {
                                $.post(delUrl, {ids: row.id}, function(r) {
                                    if (r.code == 1) {
                                        $table.bootstrapTable('refresh');
                                        alert(r.msg || '删除成功');
                                    } else {
                                        alert(r.msg || '删除失败');
                                    }
                                }, 'json');
                            }
                        }
                    }, formatter: operFmt}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });

            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });
        }
    };

    window.__backendController = Controller;
})();
