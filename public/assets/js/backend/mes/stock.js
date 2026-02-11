(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/stock/index';
    var outboundUrl = base + '/mes/stock/outbound';

    function stockFmt(v, row) {
        var quantity = parseFloat(v);
        var minStock = parseFloat(row.min_stock);
        if (quantity < minStock) {
            return '<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> ' + v + '</span>';
        } else if (quantity <= minStock * 1.2) {
            return '<span class="text-warning"><i class="fa fa-warning"></i> ' + v + '</span>';
        } else {
            return '<span class="text-success">' + v + '</span>';
        }
    }

    var Controller = {
        stock: function () {
            var $table = $('#table');
            $table.bootstrapTable({
                url: indexUrl,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                columns: [
                    {field: 'id', title: 'ID', width: 80, sortable: true},
                    {field: 'name', title: '物料名称', align: 'left'},
                    {field: 'code', title: '物料编码', width: 120},
                    {field: 'stock', title: '当前库存', width: 120, formatter: stockFmt},
                    {field: 'min_stock', title: '最低库存', width: 120},
                    {field: 'current_price', title: '当前价格', width: 120}
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
