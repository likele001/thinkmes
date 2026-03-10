/**
 * 工资管理页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/wage/index';
    var statisticsUrl = base + '/mes/wage/statistics';

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }
            function fmtTime(v) {
                if (v == null || v === '' || v === undefined) return '';
                var n = Number(v);
                if (!isNaN(n) && n >= 946684800) return new Date(n * 1000).toLocaleString('zh-CN');
                return '';
            }
            function fmtStatus(v) {
                var map = { 0: ['待确认', 'warning'], 1: ['已确认', 'success'], 2: ['已拒绝', 'danger'] };
                var s = map[v] || ['未知', 'secondary'];
                return '<span class="badge badge-' + s[1] + '">' + s[0] + '</span>';
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
                    { field: 'state', checkbox: true, width: 40 },
                    { field: 'id', title: 'Id', width: 80, sortable: true },
                    { field: 'nickname', title: '员工姓名', align: 'left' },
                    { field: 'order_no', title: '订单号', align: 'left' },
                    { field: 'product_name', title: '产品名称', align: 'left' },
                    { field: 'model_name', title: '型号名称', align: 'left' },
                    { field: 'process_name', title: '工序名称', align: 'left' },
                    { field: 'quantity', title: '报工数量', width: 100, align: 'right' },
                    { field: 'wage', title: '计件工资', width: 120, align: 'right', formatter: function(v) {
                        return '¥' + parseFloat(v || 0).toFixed(2);
                    }},
                    { field: 'status', title: '状态', width: 100, formatter: fmtStatus },
                    { field: 'create_time', title: '报工时间', width: 170, formatter: fmtTime },
                    { field: 'operate', title: '操作', width: 140, formatter: function(val, row) {
                        var h = '<a href="' + (base || '') + '/mes/report/detail?ids=' + row.id + '" class="btn btn-xs btn-info" title="查看">查看</a> ';
                        h += '<a href="' + (base || '') + '/mes/report/edit?ids=' + row.id + '" class="btn btn-xs btn-primary" title="编辑">编辑</a> ';
                        h += '<button type="button" class="btn btn-xs btn-danger btn-wage-del" data-id="' + row.id + '" title="删除">删除</button>';
                        return h;
                    }}
                ],
                queryParams: function (params) {
                    var q = $.extend({}, params);
                    var u = (typeof window !== 'undefined' && window.location && window.location.search) ? window.location.search : '';
                    if (u.indexOf('user_id=') !== -1) {
                        var m = u.match(/user_id=(\d+)/);
                        if (m) q.user_id = m[1];
                    }
                    var s = $('#form-filter select[name="status"]').val();
                    if (s !== '' && s !== undefined) q.status = s;
                    return q;
                },
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $('#btn-filter').on('click', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });
            $(document).off('click', '.btn-wage-del').on('click', '.btn-wage-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除该条记录？')) return;
                $.post((base || '') + '/mes/report/del', { ids: id }, function (r) {
                    if (r && r.code === 1) {
                        $table.bootstrapTable('refresh');
                    }
                    alert(r && r.msg ? r.msg : (r && r.code === 1 ? '删除成功' : '删除失败'));
                }, 'json');
            });
        },
        statistics: function () {
            Controller.api.loadStatistics();
        },
        api: {
            loadStatistics: function () {
                // 统计页面逻辑
            }
        }
    };

    window.__backendController = Controller;
})();
