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

        // 新流程：1=待入库 2=已入库 3=已取消（强制深色文字避免与背景重叠）
    function inStatusFmt(v) {
        var statusMap = {
            1: '<span class="badge badge-warning" style="color:#333;background-color:#f0ad4e;">待入库</span>',
            2: '<span class="badge badge-success" style="color:#fff;background-color:#5cb85c;">已入库</span>',
            3: '<span class="badge badge-secondary" style="color:#fff;background-color:#777;">已取消</span>'
        };
        return statusMap[parseInt(v, 10)] || (v ? '<span class="badge" style="color:#333;background-color:#eee;">未知</span>' : '-');
    }

    function inboundTimeFmt(value, row) {
        var ts = row.inbound_date != null ? row.inbound_date : row.create_time;
        if (ts == null || ts === '') return '';
        var n = parseInt(ts, 10);
        if (isNaN(n) || n <= 0) return '';
        var ms = n > 1e12 ? n : n * 1000;
        try {
            return new Date(ms).toLocaleString('zh-CN');
        } catch (e) {
            return '';
        }
    }

    function inboundOpFmt(value, row) {
        var st = parseInt(row.status, 10);
        var html = '';
        if (st === 1) {
            html += '<a href="javascript:;" class="btn btn-xs btn-success btn-inbound-confirm" data-id="' + row.id + '">确认入库</a> ';
        }
        html += '<a href="' + base + '/mes/purchase/viewInboundItems?id=' + row.id + '" class="btn btn-xs btn-info">查看明细</a>';
        return html;
    }

    var Controller = {
        requestList: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }
            $table.bootstrapTable({
                url: requestUrl,
                pagination: true,
                sidePagination: 'server',
                pageList: [20, 50, 100],
                pageSize: 20,
                queryParams: function (p) {
                    return {
                        page: (p.offset !== undefined && p.limit) ? Math.floor(p.offset / p.limit) + 1 : (p.page || 1),
                        limit: p.limit || 20,
                        status: ($('#c-status').length ? $('#c-status').val() : '') || ''
                    };
                },
                columns: [
                    {field: 'id', title: 'ID', width: 80},
                    {field: 'request_no', title: '申请单号', width: 150},
                    {field: 'material.name', title: '物料名称'},
                    {field: 'supplier_id', title: '供应商', formatter: function(v, row) {
                        return (row.supplier && row.supplier.name) ? row.supplier.name : (v ? '' : '未指定');
                    }},
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
                pagination: true,
                sidePagination: 'server',
                queryParams: function (p) {
                    return {
                        page: (p.offset !== undefined && p.limit) ? Math.floor(p.offset / p.limit) + 1 : 1,
                        limit: p.limit || 20,
                        status: (p.search && p.search.status) ? p.search.status : ''
                    };
                },
                columns: [
                    {field: 'id', title: 'ID', width: 60},
                    {field: 'inbound_no', title: '入库单号', width: 180},
                    {field: 'supplier.name', title: '供应商', width: 120},
                    {field: 'item_count', title: '明细数', width: 80},
                    {field: 'total_amount', title: '总金额', width: 100, formatter: function(v) {
                        return v != null ? parseFloat(v).toFixed(2) : '0.00';
                    }},
                    {field: 'inbound_date', title: '入库时间', width: 160, formatter: inboundTimeFmt},
                    {field: 'status', title: '状态', width: 90, formatter: inStatusFmt},
                    {field: 'operate', title: '操作', width: 160, formatter: inboundOpFmt}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '.btn-inbound-confirm').on('click', '.btn-inbound-confirm', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确认对该入库单执行入库（增加库存）？')) return;
                $.post(base + '/mes/purchase/confirmInbound', { ids: id }, function (r) {
                    if (r && r.code === 1) {
                        $table.bootstrapTable('refresh');
                    }
                    alert(r && r.msg ? r.msg : (r && r.code === 1 ? '成功' : '失败'));
                }, 'json');
            });
        }
    };

    window.__backendController = Controller;
})();
