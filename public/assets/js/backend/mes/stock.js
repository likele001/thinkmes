(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';

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

    // 库存列表页面
    function initStockTable() {
        var $table = $('#table');
        if ($table.length === 0 || typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
        
        $table.bootstrapTable({
            url: base + '/mes/stock/index',
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
    }

    // 库存流水页面
    function initLogTable() {
        var $table = $('#table');
        if ($table.length === 0 || typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
        
        var typeMap = {
            'purchase_in': '采购入库',
            'production_out': '生产领料',
            'check_in': '盘点入库',
            'check_out': '盘点出库',
            'other_in': '其他入库',
            'other_out': '其他出库'
        };
        
        $table.bootstrapTable({
            url: base + '/mes/stock/log',
            pk: 'id',
            sortName: 'id',
            sortOrder: 'desc',
            pagination: true,
            sidePagination: 'server',
            pageSize: 20,
            columns: [
                {field: 'id', title: '流水编号', width: 80},
                {field: 'material_id', title: '物料名称', formatter: function(v, row) {
                    return row.material ? row.material.name : '-';
                }},
                {field: 'quantity', title: '变动数量', formatter: function(v) {
                    return v > 0 ? '<span class="text-success">+' + v + '</span>' : '<span class="text-danger">' + v + '</span>';
                }},
                {field: 'business_type', title: '业务类型', formatter: function(v) {
                    return typeMap[v] || v;
                }},
                {field: 'operator_id', title: '操作人'},
                {field: 'create_time', title: '操作时间'},
                {field: 'remark', title: '备注'}
            ],
            responseHandler: function (res) {
                return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
            }
        });
    }

    // 生产领料页面
    function initOutboundTable() {
        var $table = $('#table');
        if ($table.length === 0 || typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
        
        $table.bootstrapTable({
            url: base + '/mes/stock/outbound',
            pk: 'id',
            sortName: 'id',
            sortOrder: 'desc',
            pagination: true,
            sidePagination: 'server',
            pageSize: 20,
            columns: [
                {checkbox: true},
                {field: 'out_no', title: '领料编号'},
                {field: 'order_id', title: '订单编号', formatter: function(v, row) {
                    return row.order ? row.order.order_no : '-';
                }},
                {field: 'material_id', title: '物料名称', formatter: function(v, row) {
                    return row.material ? row.material.name : '-';
                }},
                {field: 'out_quantity', title: '领料数量'},
                {field: 'operator_id', title: '操作人'},
                {field: 'out_time', title: '操作时间'},
                {field: 'status', title: '状态', formatter: function(v) {
                    return v === 1 ? '<span class="badge badge-success">已出库</span>' : '<span class="badge badge-warning">待出库</span>';
                }}
            ],
            responseHandler: function (res) {
                return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
            }
        });
    }

    // 库存盘点页面
    function initCheckTable() {
        var $table = $('#table');
        if ($table.length === 0 || typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
        
        $table.bootstrapTable({
            url: base + '/mes/stock/index',
            pk: 'id',
            sortName: 'id',
            sortOrder: 'desc',
            pagination: true,
            sidePagination: 'server',
            pageSize: 20,
            columns: [
                {checkbox: true},
                {field: 'code', title: '物料编码'},
                {field: 'name', title: '物料名称'},
                {field: 'spec', title: '规格型号'},
                {field: 'unit', title: '单位'},
                {field: 'stock', title: '当前库存', formatter: stockFmt},
                {field: 'min_stock', title: '安全库存'},
                {field: 'warehouse_id', title: '仓库'},
                {field: 'id', title: '操作', formatter: function(v, row) {
                    return '<a href="javascript:;" class="btn btn-xs btn-primary check-stock" data-id="' + v + '" data-name="' + row.name + '" data-stock="' + row.stock + '"><i class="fas fa-balance-scale"></i> 盘点</a>';
                }}
            ],
            responseHandler: function (res) {
                return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
            }
        });

        // 盘点操作
        $(document).off('click', '.check-stock').on('click', '.check-stock', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var currentStock = $(this).data('stock');
            
            var actualQuantity = prompt('请输入物料 "' + name + '" 的实际库存数量（当前系统库存：' + currentStock + '）:', currentStock);
            if (actualQuantity === null) return;
            
            actualQuantity = parseFloat(actualQuantity);
            if (isNaN(actualQuantity)) {
                alert('请输入有效的数字');
                return;
            }
            
            var remark = prompt('请输入盘点备注（可选）:');
            
            $.ajax({
                url: base + '/mes/stock/check',
                type: 'post',
                data: {
                    material_id: id,
                    actual_quantity: actualQuantity,
                    remark: remark || ''
                },
                dataType: 'json',
                success: function(res) {
                    if (res.code === 1) {
                        alert(res.msg);
                        $table.bootstrapTable('refresh');
                    } else {
                        alert(res.msg || '盘点失败');
                    }
                },
                error: function() {
                    alert('网络错误，请重试');
                }
            });
        });
    }

    var Controller = {
        // 库存列表
        index: function () {
            initStockTable();
        },
        
        // 库存流水
        log: function () {
            initLogTable();
        },
        
        // 生产领料
        outbound: function () {
            initOutboundTable();
        },
        
        // 库存盘点
        check: function () {
            initCheckTable();
        }
    };

    // 根据当前action自动调用对应方法
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) {
        Controller[action]();
    }

    // 刷新按钮事件
    $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
        $('#table').bootstrapTable('refresh');
    });

    window.__backendController = Controller;
})();
