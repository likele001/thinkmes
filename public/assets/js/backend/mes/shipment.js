(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl    = base + '/mes/shipment/index';
    var addUrl      = base + '/mes/shipment/add';
    var editUrl     = base + '/mes/shipment/edit';
    var delUrl      = base + '/mes/shipment/del';
    var trackUrl    = base + '/mes/shipment/track';

    function statusFmt(v) {
        var m = {
            0: '<span class="badge badge-warning">待发货</span>',
            1: '<span class="badge badge-info">已发货</span>',
            2: '<span class="badge badge-success">已签收</span>',
            3: '<span class="badge badge-danger">已退回</span>'
        };
        return m[v] || '未知';
    }

    function operFmt(value, row) {
        return '<a href="' + editUrl + '?ids=' + row.id + '" class="btn btn-xs btn-success btn-edit">编辑</a> ' +
               '<a href="' + base + '/print_template/preview?ref_type=shipment&ref_id=' + row.id + '" target="_blank" class="btn btn-xs btn-default" title="按模板打印">打印</a> ' +
               '<a href="' + trackUrl + '?id=' + row.id + '" class="btn btn-xs btn-info">跟踪</a> ' +
               '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
    }

    var Controller = {

        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
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
                    {field: 'shipment_time', title: '发货时间', width: 180, formatter: function (v) {
                        return v ? new Date(v * 1000).toLocaleString('zh-CN') : '';
                    }},
                    {field: 'operate', title: '操作', width: 250, events: {
                        'click .btn-edit': function (e, v, row) { location.href = editUrl + '?ids=' + row.id; },
                        'click .btn-del': function (e, v, row) {
                            if (!confirm('确定要删除吗？')) return;
                            $.post(delUrl, {ids: row.id}, function (r) {
                                if (r.code == 1) $table.bootstrapTable('refresh');
                                alert(r.msg || (r.code == 1 ? '删除成功' : '删除失败'));
                            }, 'json');
                        }
                    }, formatter: operFmt}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) || 0, rows: (res.data && res.data.list) || [] };
                }
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () { $table.bootstrapTable('refresh'); });
        },

        // 添加发货单
        add: function () {
            var modelList = [];
            try {
                var el = document.getElementById('shipment-model-list');
                if (el) modelList = JSON.parse(el.textContent || '[]');
            } catch (e) {}

            var itemIndex = 0;

            function addItem() {
                var optHtml = '';
                modelList.forEach(function (v) {
                    optHtml += '<option value="' + v.id + '">' + (v.name || v.id) + '</option>';
                });
                var html = '<tr>' +
                    '<td><select class="form-control" name="items[' + itemIndex + '][model_id]">' + optHtml + '</select></td>' +
                    '<td><input type="number" class="form-control" name="items[' + itemIndex + '][quantity]" value="1" min="1"></td>' +
                    '<td><input type="text" class="form-control" name="items[' + itemIndex + '][batch_no]" placeholder="批次号"></td>' +
                    '<td><input type="text" class="form-control" name="items[' + itemIndex + '][trace_code]" placeholder="追溯码"></td>' +
                    '<td><button type="button" class="btn btn-xs btn-danger remove-item">删除</button></td>' +
                    '</tr>';
                $('#items-container').append(html);
                itemIndex++;
            }

            $(document).off('click', '#add-item').on('click', '#add-item', function () { addItem(); });
            $(document).off('click', '.remove-item').on('click', '.remove-item', function () {
                $(this).closest('tr').remove();
            });
        },

        // track 别名：URL /mes/shipment/track/index 时 actionname=track
        // 无 id 参数时服务端渲染 select_shipment，有 id 时渲染详情（纯静态，无需 JS 初始化）
        track: function () {
            Controller.selectShipment();
        },

        // 选择发货单（select_shipment 页面）
        selectShipment: function () {
            $(document).off('click', '.btn-track').on('click', '.btn-track', function () {
                var id = $(this).data('id');
                location.href = trackUrl + '?id=' + id;
            });
            $(document).off('click', '#btn-search').on('click', '#btn-search', function () {
                var keyword = $('#search-shipment').val().trim();
                if (keyword) {
                    $('#table tbody tr').each(function () {
                        $(this).toggle($(this).text().indexOf(keyword) !== -1);
                    });
                } else {
                    $('#table tbody tr').show();
                }
            });
        }
    };

    window.__backendController = Controller;
})();
