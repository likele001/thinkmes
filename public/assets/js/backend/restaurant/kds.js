(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    function statusText(v) {
        var map = { 0: '待确认', 1: '制作中', 2: '待上菜', 3: '已完成', 4: '已结账' };
        return map[v] || String(v);
    }
    function ageFmt(sec) {
        sec = parseInt(sec || 0, 10);
        if (sec < 0) sec = 0;
        var m = Math.floor(sec / 60);
        var s = sec % 60;
        return m + '分' + s + '秒';
    }
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/restaurant/kds/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                sortName: 'id',
                sortOrder: 'desc',
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                detailView: true,
                detailFormatter: function () { return '<div class="kds-detail">加载中...</div>'; },
                onExpandRow: function (index, row, $detail) {
                    var $box = $detail.find('.kds-detail');
                    $.getJSON(base + '/restaurant/kds/items', { id: row.id }, function (r) {
                        if (!r || r.code != 1) { $box.html('<div class="text-danger">加载失败</div>'); return; }
                        var list = (r.data && r.data.list) ? r.data.list : [];
                        if (!list.length) { $box.html('<div class="text-muted">暂无明细</div>'); return; }
                        var html = '<table class="table table-sm table-bordered mb-0"><thead><tr><th>菜品</th><th>规格/套餐</th><th style="width:80px">数量</th></tr></thead><tbody>';
                        for (var i = 0; i < list.length; i++) {
                            var it = list[i];
                            html += '<tr><td>' + (it.name || '') + '</td><td>' + (it.options_text || '') + '</td><td>' + (it.quantity || 0) + '</td></tr>';
                        }
                        html += '</tbody></table>';
                        $box.html(html);
                    });
                },
                columns: [
                    { field: 'id', title: 'ID', width: 70 },
                    { field: 'order_no', title: '订单号', width: 170 },
                    { field: 'table_name', title: '桌台', width: 120 },
                    { field: 'total_amount', title: '金额', width: 90, formatter: function (v) { return v != null ? parseFloat(v).toFixed(2) : ''; } },
                    { field: 'status', title: '状态', width: 90, formatter: function (v) { return '<span class="badge badge-secondary">' + statusText(v) + '</span>'; } },
                    { field: 'age_seconds', title: '已等待', width: 110, formatter: function (v, row) {
                        var t = ageFmt(v);
                        if (parseInt(v || 0, 10) >= 900 && parseInt(row.status || 0, 10) < 3) {
                            return '<span class="text-danger">' + t + '</span>';
                        }
                        return t;
                    }},
                    { field: 'operate', title: '操作', width: 260, formatter: function (v, row) {
                        var btns = '';
                        btns += '<a href="javascript:;" class="btn btn-xs btn-warning btn-call" data-id="' + row.id + '">叫号</a> ';
                        btns += '<a href="javascript:;" class="btn btn-xs btn-info btn-status" data-id="' + row.id + '" data-status="1">制作中</a> ';
                        btns += '<a href="javascript:;" class="btn btn-xs btn-warning btn-status" data-id="' + row.id + '" data-status="2">待上菜</a> ';
                        btns += '<a href="javascript:;" class="btn btn-xs btn-success btn-status" data-id="' + row.id + '" data-status="3">完成</a> ';
                        btns += '<a href="javascript:;" class="btn btn-xs btn-primary btn-status" data-id="' + row.id + '" data-status="4">结账</a>';
                        return btns;
                    } }
                ]
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () { table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-soldout').on('click', '.btn-soldout', function () {
                var itemId = prompt('请输入菜品ID（用于售罄联动）');
                if (itemId === null) return;
                itemId = parseInt(itemId, 10);
                if (!itemId) { alert('ID不正确'); return; }
                var sold = prompt('售罄？输入 1=售罄，0=取消售罄', '1');
                if (sold === null) return;
                sold = parseInt(sold, 10) || 0;
                $.post(base + '/restaurant/kds/setSoldOut', { item_id: itemId, sold_out: sold }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已更新' : '失败'));
                }, 'json');
            });
            $(document).off('click', '.btn-call').on('click', '.btn-call', function () {
                var id = $(this).data('id');
                $.post(base + '/restaurant/kds/call', { id: id }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已叫号' : '失败'));
                }, 'json');
            });
            $(document).off('click', '.btn-status').on('click', '.btn-status', function () {
                var id = $(this).data('id');
                var status = $(this).data('status');
                $.post(base + '/restaurant/order/updateStatus', { id: id, status: status }, function (r) {
                    alert(r.msg || (r.code == 1 ? '成功' : '失败'));
                    if (r.code == 1) table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
