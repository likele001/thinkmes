(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
        }
    };
    // 销售订单明细行管理（add/edit 共用）
    var SalesOrderItems = {
        products: [],
        rowIndex: 0,
        init: function () {
            var el = document.getElementById('sales-order-products');
            if (el) {
                try { SalesOrderItems.products = JSON.parse(el.textContent || '[]'); } catch (e) { SalesOrderItems.products = []; }
            }
            // 初始化已有明细行（edit 模式下 tbody 中可能已有模板行）
            var $tbody = $('#items-tbody');
            $tbody.find('tr').each(function () {
                SalesOrderItems.rowIndex++;
            });
            $('#btn-add-item').off('click.items').on('click.items', function () {
                SalesOrderItems.addRow();
            });
            // 删除行事件委托
            $('#items-tbody').off('click.items', '.btn-remove-item').on('click.items', '.btn-remove-item', function () {
                $(this).closest('tr').remove();
                SalesOrderItems.calcTotal();
            });
            // 数量/单价变更时重算金额
            $('#items-tbody').off('change.items input.items', 'input[type="number"]').on('change.items input.items', 'input[type="number"]', function () {
                var $tr = $(this).closest('tr');
                var qty   = parseFloat($tr.find('input.item-qty').val()) || 0;
                var price = parseFloat($tr.find('input.item-price').val()) || 0;
                $tr.find('.item-amount').text((qty * price).toFixed(2));
                SalesOrderItems.calcTotal();
            });
        },
        buildProductOptions: function (selectedId) {
            var html = '<option value="">请选择产品</option>';
            SalesOrderItems.products.forEach(function (p) {
                var sel = (selectedId && String(p.id) === String(selectedId)) ? ' selected' : '';
                html += '<option value="' + p.id + '" data-price="' + (p.price || 0) + '"' + sel + '>' + p.name + '</option>';
            });
            return html;
        },
        addRow: function (item) {
            var idx = SalesOrderItems.rowIndex++;
            item = item || {};
            var qty   = item.quantity || 1;
            var price = item.price    || 0;
            var amt   = (parseFloat(qty) * parseFloat(price)).toFixed(2);
            var $tr = $('<tr>' +
                '<td><select class="form-control form-control-sm item-product" name="items[' + idx + '][product_id]">' + SalesOrderItems.buildProductOptions(item.product_id) + '</select></td>' +
                '<td><input type="number" class="form-control form-control-sm item-qty" name="items[' + idx + '][quantity]" value="' + qty + '" min="1" style="width:80px;"></td>' +
                '<td><input type="number" class="form-control form-control-sm item-price" name="items[' + idx + '][price]" value="' + price + '" step="0.01" style="width:100px;"></td>' +
                '<td class="item-amount">' + amt + '</td>' +
                '<td><button type="button" class="btn btn-xs btn-danger btn-remove-item"><i class="fas fa-times"></i></button></td>' +
                '</tr>');
            // 选择产品时自动填入参考价
            $tr.find('.item-product').on('change', function () {
                var price = $(this).find(':selected').data('price') || 0;
                $tr.find('.item-price').val(price).trigger('input.items');
            });
            $('#items-tbody').append($tr);
            SalesOrderItems.calcTotal();
        },
        calcTotal: function () {
            var total = 0;
            $('#items-tbody tr').each(function () {
                var qty   = parseFloat($(this).find('.item-qty').val()) || 0;
                var price = parseFloat($(this).find('.item-price').val()) || 0;
                total += qty * price;
            });
            $('#order-total-amount').text(total.toFixed(2));
        }
    };

    function initSalesOrderForm(submitUrl, backUrl) {
        var $ = jQuery;
        SalesOrderItems.init();
        var form = $('form#form-add, form#form-edit');
        if (!form.length) return;
        form.off('submit.salesorder').on('submit.salesorder', function (e) {
            e.preventDefault();
            $.post(submitUrl, form.serialize(), function (r) {
                if (r.code == 1) {
                    alert(r.msg || '操作成功');
                    location.href = backUrl;
                } else {
                    alert(r.msg || '操作失败');
                }
            }, 'json').fail(function () { alert('操作失败'); });
        });
    }

    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            var mesInstalled = $('#toolbar .btn-to-mes').length > 0;
            var cols = [
                { field: 'id', title: 'ID', width: 80, sortable: true },
                { field: 'order_no', title: '订单编号', align: 'left' },
                { field: 'customer_name', title: '客户', align: 'left' },
                { field: 'total_amount', title: '总金额', width: 120, formatter: function (v) { return v ? parseFloat(v).toLocaleString('zh-CN', { minimumFractionDigits: 2 }) : ''; }},
                { field: 'status', title: '状态', width: 100, formatter: function (v) {
                    var map = { draft: '草稿', confirmed: '已确认', producing: '生产中', completed: '已完成', cancelled: '已取消' };
                    return map[v] || v;
                }},
                { field: 'mes_order_id', title: 'MES', width: 80, formatter: function (v) { return v > 0 ? '<span class="badge badge-success">已转</span>' : '-'; }},
                { field: 'create_time', title: '创建时间', width: 180, formatter: function (v) { return v ? new Date(v * 1000).toLocaleString('zh-CN') : ''; }},
                { field: 'operate', title: '操作', width: 180, formatter: function (v, row) {
                    var html = '<a href="' + base + '/crm/sales_order/edit?id=' + row.id + '" class="btn btn-xs btn-success">编辑</a> ';
                    html += '<a href="javascript:;" class="btn btn-xs btn-danger btn-del-one">删除</a>';
                    if (mesInstalled && row.mes_order_id == 0 && row.status !== 'cancelled') {
                        html += ' <a href="javascript:;" class="btn btn-xs btn-info btn-to-mes-one" data-id="' + row.id + '">转生产</a>';
                    }
                    return html;
                }}
            ];
            table.bootstrapTable({
                url: base + '/crm/sales_order/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                pageList: [10, 20, 50],
                sortName: 'id',
                sortOrder: 'desc',
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                columns: cols
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '#toolbar .btn-edit').on('click', '#toolbar .btn-edit', function () {
                var rows = table.bootstrapTable('getSelections');
                if (rows.length != 1) { alert('请选择一条记录'); return; }
                location.href = base + '/crm/sales_order/edit?id=' + rows[0].id;
            });
            $(document).off('click', '#toolbar .btn-del').on('click', '#toolbar .btn-del', function () {
                var rows = table.bootstrapTable('getSelections');
                if (rows.length == 0) { alert('请选择要删除的记录'); return; }
                if (!confirm('确定要删除选中的 ' + rows.length + ' 条记录吗？')) return;
                $.post(base + '/crm/sales_order/del', { ids: rows.map(function (r) { return r.id; }).join(',') }, function (r) {
                    if (r.code == 1) { table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); } else alert(r.msg || '删除失败');
                }, 'json');
            });
            if (mesInstalled) {
                $(document).off('click', '#toolbar .btn-to-mes').on('click', '#toolbar .btn-to-mes', function () {
                    var rows = table.bootstrapTable('getSelections');
                    if (rows.length != 1) { alert('请选择一条记录'); return; }
                    var row = rows[0];
                    if (row.mes_order_id > 0) { alert('该订单已转生产'); return; }
                    if (!confirm('确定将订单「' + row.order_no + '」转为 MES 生产订单？')) return;
                    $.post(base + '/crm/sales_order/toMes', { id: row.id }, function (r) { if (r.msg) alert(r.msg); if (r.code == 1) table.bootstrapTable('refresh'); }, 'json');
                });
            }
            table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
                var rows = table.bootstrapTable('getSelections');
                $('.btn-edit, .btn-del').toggleClass('disabled btn-disabled', rows.length === 0);
                if (mesInstalled) {
                    var canToMes = rows.length === 1 && rows[0].mes_order_id == 0 && rows[0].status !== 'cancelled';
                    $('.btn-to-mes').toggleClass('disabled btn-disabled', !canToMes);
                }
            });
            table.on('click', '.btn-del-one', function () {
                var row = $(this).closest('tr').data('index');
                var data = table.bootstrapTable('getData');
                var r = data[row];
                if (!r) return;
                if (!confirm('确定要删除订单「' + r.order_no + '」吗？')) return;
                $.post(base + '/crm/sales_order/del', { ids: r.id }, function (res) { if (res.code == 1) { table.bootstrapTable('refresh'); alert(res.msg || '删除成功'); } else alert(res.msg || '删除失败'); }, 'json');
            });
            table.on('click', '.btn-to-mes-one', function () {
                var id = $(this).data('id');
                if (!confirm('确定将此订单转为 MES 生产订单？')) return;
                $.post(base + '/crm/sales_order/toMes', { id: id }, function (r) { if (r.msg) alert(r.msg); if (r.code == 1) table.bootstrapTable('refresh'); }, 'json');
            });
        },
        add: function () {
            initSalesOrderForm(base + '/crm/sales_order/add', base + '/crm/sales_order/index');
        },
        edit: function () {
            var form = $('form#form-edit');
            var id = form.data('id');
            initSalesOrderForm(base + '/crm/sales_order/edit?ids=' + id, base + '/crm/sales_order/index');
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
