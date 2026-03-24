(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';

    var Controller = {
        index: function () {
            var $table = $('#table');

            function load() {
                var orderIds = $('#order_ids').val();
                var url = base + '/mes/mrp/index';
                if (orderIds) url += '?order_ids=' + encodeURIComponent(orderIds);
                // 优先使用 bootstrapTable
                if (typeof $table.bootstrapTable === 'function') {
                    if ($table.data('bootstrap.table')) {
                        $table.bootstrapTable('destroy');
                    }
                    $table.bootstrapTable({
                        url: url,
                        sortName: 'material_id',
                        sortOrder: 'asc',
                        sidePagination: 'server',
                        pagination: true,
                        pageSize: 50,
                        pageList: [20, 50, 100],
                        responseHandler: function (res) {
                            var d = res && res.data ? res.data : {};
                            return { total: d.total || 0, rows: d.list || [] };
                        },
                        columns: [
                            {field: 'material_id', title: '物料ID', width: 80},
                            {field: 'material_code', title: '编码', width: 120},
                            {field: 'material_name', title: '物料名称', align: 'left'},
                            {field: 'required', title: '需求总量', width: 100, align: 'right'},
                            {field: 'stock', title: '当前库存', width: 100, align: 'right'},
                            {field: 'shortage', title: '缺料量', width: 100, align: 'right', formatter: function (v) {
                                return v > 0 ? '<span class="text-danger font-weight-bold">' + v + '</span>' : v;
                            }},
                            {field: 'unit', title: '单位', width: 80}
                        ]
                    });
                } else {
                    // fallback：纯 HTML 表格渲染
                    $.get(url, function (res) {
                        if (res.code != 1 || !res.data) return;
                        var list = res.data.list || [];
                        var html = '<thead><tr><th>物料ID</th><th>编码</th><th>名称</th><th>需求总量</th><th>当前库存</th><th>缺料量</th><th>单位</th></tr></thead><tbody>';
                        list.forEach(function (row) {
                            html += '<tr><td>' + (row.material_id || '') + '</td><td>' + (row.material_code || '') + '</td><td>' + (row.material_name || '') + '</td><td>' + (row.required || 0) + '</td><td>' + (row.stock || 0) + '</td><td class="text-danger">' + (row.shortage || 0) + '</td><td>' + (row.unit || '') + '</td></tr>';
                        });
                        html += '</tbody>';
                        $table.html(html || '<tbody><tr><td colspan="7" class="text-center">无缺料或未选择订单</td></tr></tbody>');
                    }, 'json');
                }
            }

            $(document).off('click', '#btn-query').on('click', '#btn-query', function () { load(); });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () { load(); });
            load();
        }
    };

    window.__backendController = Controller;
})();
