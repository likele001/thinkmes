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
                // 确保 ECharts 已加载
                function ensureECharts(cb) {
                    if (typeof echarts !== 'undefined') { cb(); return; }
                    var s = document.createElement('script');
                    s.src = '/assets/lib/echarts/echarts.min.js';
                    s.onload = cb;
                    document.head.appendChild(s);
                }

                function loadUsers() {
                    $.get(base + '/mes/wage/getReportUsers', function (res) {
                        if (res.code === 1 && res.data && res.data.list) {
                            var html = '<option value="">全部员工</option>';
                            res.data.list.forEach(function (u) {
                                html += '<option value="' + u.id + '">' + (u.nickname || '-') + '</option>';
                            });
                            $('#user_id').html(html);
                        }
                    }, 'json');
                }

                function loadSummary() {
                    var params = $('#search-form').serialize();
                    $.ajax({
                        url: base + '/mes/wage/getSummary',
                        type: 'POST',
                        data: params,
                        dataType: 'json',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).done(function (res) {
                        if (res && res.code === 1 && res.data) {
                            var t = res.data.total || {};
                            $('#total-users').text(t.user_count || 0);
                            $('#total-quantity').text(t.quantity || 0);
                            $('#total-wage').text('¥' + parseFloat(t.wage || 0).toFixed(2));
                            $('#total-count').text(t.count || 0);
                            var summary = res.data.summary || [];
                            var html = '';
                            if (summary.length) {
                                summary.forEach(function (item) {
                                    var q = parseFloat(item.total_quantity) || 0;
                                    var w = parseFloat(item.total_wage) || 0;
                                    var avg = q > 0 ? (w / q).toFixed(2) : '0.00';
                                    var name = (item.user && item.user.nickname) ? item.user.nickname : '未知';
                                    html += '<tr>';
                                    html += '<td>' + name + '</td>';
                                    html += '<td>' + item.total_quantity + '</td>';
                                    html += '<td>¥' + parseFloat(item.total_wage).toFixed(2) + '</td>';
                                    html += '<td>' + item.report_count + '</td>';
                                    html += '<td>¥' + avg + '</td>';
                                    html += '<td><a href="' + base + '/mes/wage/index?user_id=' + item.user_id + '" class="btn btn-xs btn-info">查看明细</a></td>';
                                    html += '</tr>';
                                });
                            } else {
                                html = '<tr><td colspan="6" class="text-center text-muted">暂无数据</td></tr>';
                            }
                            $('#summary-table').html(html);
                        }
                    }).fail(function (xhr) {
                        if (xhr.responseText && xhr.responseText.indexOf('<!DOCTYPE') === 0) {
                            console.error('getSummary 返回了 HTML 而非 JSON，请检查路由或权限');
                        }
                    });
                }

                function loadChart() {
                    var params = $('#search-form').serialize();
                    $.ajax({
                        url: base + '/mes/wage/getChart',
                        type: 'POST',
                        data: params,
                        dataType: 'json',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).done(function (res) {
                        ensureECharts(function () {
                            if (!res || res.code !== 1 || !res.data) return;
                            var d = res.data;
                            var dates = d.dates || [];
                            var wages = d.wages || [];
                            var quantities = d.quantities || [];
                            var byUser = d.by_user || [];
                            var processNames = d.by_process_names || [];
                            var processWages = d.by_process_wages || [];

                            var el1 = document.getElementById('chart-wage-trend');
                            if (el1) {
                                echarts.init(el1).setOption({
                                    tooltip: { trigger: 'axis', axisPointer: { type: 'cross' } },
                                    xAxis: { type: 'category', data: dates },
                                    yAxis: { type: 'value', name: '工资金额(元)' },
                                    series: [{ name: '工资金额', type: 'line', data: wages, smooth: true, itemStyle: { color: '#5470c6' } }]
                                });
                            }
                            var el2 = document.getElementById('chart-quantity-trend');
                            if (el2) {
                                echarts.init(el2).setOption({
                                    tooltip: { trigger: 'axis' },
                                    xAxis: { type: 'category', data: dates },
                                    yAxis: { type: 'value', name: '报工数量' },
                                    series: [{ name: '报工数量', type: 'bar', data: quantities, itemStyle: { color: '#91cc75' } }]
                                });
                            }
                            var el3 = document.getElementById('chart-user-pie');
                            if (el3) {
                                echarts.init(el3).setOption({
                                    tooltip: { trigger: 'item', formatter: '{b}: ¥{c} ({d}%)' },
                                    legend: { orient: 'vertical', left: 'left', top: 'middle' },
                                    series: [{ name: '工资金额', type: 'pie', radius: ['40%', '70%'], data: byUser, emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0,0,0,0.2)' } } }]
                                });
                            }
                            var el4 = document.getElementById('chart-process-bar');
                            if (el4) {
                                echarts.init(el4).setOption({
                                    tooltip: { trigger: 'axis' },
                                    xAxis: { type: 'category', data: processNames, axisLabel: { rotate: processNames.length > 6 ? 30 : 0 } },
                                    yAxis: { type: 'value', name: '工资金额(元)' },
                                    series: [{ name: '工资金额', type: 'bar', data: processWages, itemStyle: { color: '#fac858' } }]
                                });
                            }
                        });
                    }).fail(function (xhr) {
                        if (xhr.responseText && xhr.responseText.indexOf('<!DOCTYPE') === 0) {
                            console.error('getChart 返回了 HTML 而非 JSON');
                        }
                    });
                }

                loadUsers();
                $('#search-btn').off('click.stats').on('click.stats', function () {
                    loadSummary();
                    loadChart();
                });
                $('#export-btn').off('click.stats').on('click.stats', function () {
                    window.open(base + '/mes/wage/export?' + $('#search-form').serialize(), '_blank');
                });
                loadSummary();
                loadChart();
            }
        }
    };

    window.__backendController = Controller;
})();
