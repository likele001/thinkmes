/**
 * 生产计划管理页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/production_plan/index';
    var addUrl = base + '/mes/production_plan/add';
    var editUrl = base + '/mes/production_plan/edit';
    var delUrl = base + '/mes/production_plan/del';
    var allocationsUrl = base + '/mes/production_plan/allocations';
    var progressStatsUrl = base + '/mes/production_plan/progressStats';
    var progressUrl = base + '/mes/production_plan/progress';

    function statusFmt(v) {
        var statusMap = {0: '待开始', 1: '进行中', 2: '已完成', 3: '已暂停'};
        var classMap = {0: 'secondary', 1: 'primary', 2: 'success', 3: 'warning'};
        return '<span class="badge badge-' + (classMap[v] || 'secondary') + '">' + (statusMap[v] || '未知') + '</span>';
    }

    function progressFmt(v) {
        return '<div class="progress" style="height: 20px;"><div class="progress-bar" role="progressbar" style="width: ' + v + '%;">' + v + '%</div></div>';
    }

    function operFmt(value, row) {
        var html = '';
        if (row.order_id && row.model_id) {
            html += '<a href="' + base + '/mes/allocation/batch?order_id=' + row.order_id + '&plan_id=' + row.id + '" class="btn btn-xs btn-success btn-allocate"><i class="fas fa-tasks"></i> 分配工序</a> ';
            html += '<a href="' + allocationsUrl + '?id=' + row.id + '" class="btn btn-xs btn-info btn-allocations"><i class="fas fa-list"></i> 查看分工</a> ';
            html += '<a href="' + progressStatsUrl + '?id=' + row.id + '" class="btn btn-xs btn-warning btn-progress-stats"><i class="fas fa-chart-bar"></i> 进度统计</a> ';
            html += '<a href="' + progressUrl + '?id=' + row.id + '" class="btn btn-xs btn-secondary btn-progress"><i class="fas fa-chart-line"></i> 生产进度</a> ';
        }
        html += '<a href="' + editUrl + '?ids=' + row.id + '" class="btn btn-xs btn-success btn-edit"><i class="fas fa-edit"></i> 编辑</a> ' +
            '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '"><i class="fas fa-trash-alt"></i> 删除</a>';
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
                    {field: 'plan_code', title: '计划编码', align: 'left'},
                    {field: 'plan_name', title: '计划名称', align: 'left'},
                    {field: 'order.order_no', title: '订单号', align: 'left'},
                    {field: 'model.product.name', title: '产品', align: 'left'},
                    {field: 'model.name', title: '产品型号', align: 'left'},
                    {field: 'total_quantity', title: '计划数量', width: 100, align: 'right'},
                    {field: 'completed_quantity', title: '完成数量', width: 100, align: 'right'},
                    {field: 'progress', title: '完成进度', width: 120, formatter: progressFmt},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {field: 'planned_start_time', title: '计划开始', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                    }},
                    {field: 'planned_end_time', title: '计划结束', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                    }},
                    {field: 'operate', title: '操作', width: 150, events: {
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
            
            // 编辑按钮（工具栏，只绑定到工具栏的按钮，避免影响表格行的编辑按钮）
            $(document).off('click', '#toolbar .btn-edit').on('click', '#toolbar .btn-edit', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length != 1) {
                    alert('请选择一条记录');
                    return;
                }
                location.href = editUrl + '?ids=' + rows[0].id;
            });
            
            // 删除按钮（工具栏，只绑定到工具栏的按钮，避免影响表格行的删除按钮）
            $(document).off('click', '#toolbar .btn-del').on('click', '#toolbar .btn-del', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length == 0) {
                    alert('请选择要删除的记录');
                    return;
                }
                if (!confirm('确定要删除选中的 ' + rows.length + ' 条记录吗？')) {
                    return;
                }
                var ids = rows.map(function(r) { return r.id; });
                $.post(delUrl, {ids: ids.join(',')}, function(r) {
                    if (r.code == 1) {
                        $table.bootstrapTable('refresh');
                        alert(r.msg || '删除成功');
                    } else {
                        alert(r.msg || '删除失败');
                    }
                }, 'json');
            });
            
            $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function() {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length > 0) {
                    $('.btn-edit, .btn-del').removeClass('disabled btn-disabled');
                } else {
                    $('.btn-edit, .btn-del').addClass('disabled btn-disabled');
                }
            });
        },
        progress: function () {
            // 确保 ECharts 已加载后再初始化图表
            function initCharts() {
                if (typeof echarts === 'undefined') {
                    var s = document.createElement('script');
                    s.src = '/assets/lib/echarts/echarts.min.js';
                    s.onload = initCharts;
                    document.head.appendChild(s);
                    return;
                }

                var completionDom = document.getElementById('completionChart');
                if (!completionDom) return;

                var completionChart = echarts.init(completionDom);
                var completionRate = parseInt($('#completion-rate').data('rate')) || 0;
                var remainingRate = 100 - completionRate;
                completionChart.setOption({
                    series: [{
                        type: 'pie',
                        radius: ['60%', '80%'],
                        label: { show: false },
                        labelLine: { show: false },
                        data: [
                            {value: completionRate, name: '已完成', itemStyle: {color: '#4CAF50'}},
                            {value: remainingRate, name: '未完成', itemStyle: {color: '#E0E0E0'}}
                        ]
                    }]
                });

                var overallChart = echarts.init(document.getElementById('overallProgressChart'));
                var totalQuantity = parseInt($('#total-quantity').data('value')) || 0;
                var completedQuantity = parseInt($('#completed-quantity').data('value')) || 0;
                var remainingQuantity = totalQuantity - completedQuantity;
                overallChart.setOption({
                    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                    xAxis: [{ type: 'category', data: ['总数量', '已完成', '剩余'] }],
                    yAxis: [{ type: 'value' }],
                    series: [{
                        name: '数量',
                        type: 'bar',
                        barWidth: '60%',
                        data: [
                            {value: totalQuantity, itemStyle: {color: '#2196F3'}},
                            {value: completedQuantity, itemStyle: {color: '#4CAF50'}},
                            {value: remainingQuantity, itemStyle: {color: '#FF9800'}}
                        ]
                    }]
                });

                var processChart = echarts.init(document.getElementById('processPieChart'));
                var processData = [];
                $('.process-data').each(function () {
                    processData.push({value: parseInt($(this).data('rate')) || 0, name: $(this).data('name')});
                });
                processChart.setOption({
                    tooltip: { trigger: 'item', formatter: '{b}: {c}% ({d}%)' },
                    legend: { orient: 'vertical', left: 'left' },
                    series: [{ name: '完成率', type: 'pie', radius: '50%', data: processData }]
                });

                var employeeChart = echarts.init(document.getElementById('employeeRankingChart'));
                var employeeLabels = [], employeeData = [];
                $('.employee-data').each(function () {
                    employeeLabels.push($(this).data('name'));
                    employeeData.push(parseInt($(this).data('rate')) || 0);
                });
                employeeChart.setOption({
                    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                    xAxis: { type: 'value', boundaryGap: [0, 0.01] },
                    yAxis: { type: 'category', data: employeeLabels },
                    series: [{ name: '完成率', type: 'bar', data: employeeData, itemStyle: { color: '#4CAF50' } }]
                });

                var orderChart = echarts.init(document.getElementById('orderProgressChart'));
                var orderLabels = [], orderData = [];
                $('.order-data').each(function () {
                    orderLabels.push($(this).data('name'));
                    orderData.push(parseInt($(this).data('rate')) || 0);
                });
                orderChart.setOption({
                    tooltip: { trigger: 'axis' },
                    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                    xAxis: { type: 'category', boundaryGap: false, data: orderLabels },
                    yAxis: { type: 'value' },
                    series: [{ name: '完成率', type: 'line', smooth: true, data: orderData }]
                });

                // 详情弹窗事件
                var baseUrl = base + '/mes/production_plan';
                $(document).off('click.progress', '[data-action]').on('click.progress', '[data-action]', function (e) {
                    e.preventDefault();
                    var action = $(this).data('action');
                    var titleMap = {
                        showOrderDetails:   '订单详情',
                        showProductDetails: '产品详情',
                        showEmployeeDetails:'员工详情'
                    };
                    var title = titleMap[action] || '详情';
                    if (action === 'showProcessDetails') {
                        title = '工序详情 - ' + $(this).data('process-name');
                    }
                    $('#detailModalTitle').text(title);
                    $('#detailModalBody').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> 加载中...</div>');
                    $('#detailModal').modal('show');

                    var apiMap = {
                        showOrderDetails:   { url: baseUrl + '/getOrderDetails',   param: {order_id: $(this).data('order-id')},     buildHtml: buildOrderHtml },
                        showProductDetails: { url: baseUrl + '/getProductDetails',  param: {product_id: $(this).data('product-id')}, buildHtml: buildProductHtml },
                        showProcessDetails: { url: baseUrl + '/getProcessDetails',  param: {process_name: $(this).data('process-name')}, buildHtml: buildProcessHtml },
                        showEmployeeDetails:{ url: baseUrl + '/getEmployeeDetails', param: {user_id: $(this).data('user-id')},        buildHtml: buildEmployeeHtml }
                    };
                    var cfg = apiMap[action];
                    if (!cfg) return;
                    $.get(cfg.url, cfg.param, function (r) {
                        if (r.code === 1) {
                            $('#detailModalBody').html(cfg.buildHtml(r.data));
                        } else {
                            $('#detailModalBody').html('<div class="alert alert-danger">' + (r.msg || '获取失败') + '</div>');
                        }
                    }, 'json');
                });

                $(window).off('resize.progress').on('resize.progress', function () {
                    completionChart.resize();
                    overallChart.resize();
                    processChart.resize();
                    employeeChart.resize();
                    orderChart.resize();
                });
            }

            function buildOrderHtml(data) {
                var html = '<h5>订单信息</h5><p><strong>订单编号：</strong>' + (data.order.order_no || '') + '</p>';
                html += '<h5>生产计划详情</h5><div class="table-responsive"><table class="table table-bordered table-striped">';
                html += '<thead><tr><th>计划编号</th><th>计划名称</th><th>总数量</th></tr></thead><tbody>';
                $.each(data.plans, function (_, plan) {
                    html += '<tr><td>' + plan.plan_code + '</td><td>' + plan.plan_name + '</td><td>' + plan.total_quantity + '</td></tr>';
                });
                return html + '</tbody></table></div>';
            }

            function buildProductHtml(data) {
                var html = '<h5>产品信息</h5><p><strong>产品名称：</strong>' + (data.product.name || '') + '</p>';
                html += '<h5>型号进度</h5><div class="table-responsive"><table class="table table-bordered table-striped">';
                html += '<thead><tr><th>型号名称</th><th>总数量</th><th>已完成</th><th>完成率</th></tr></thead><tbody>';
                $.each(data.models, function (_, m) {
                    html += '<tr><td>' + m.model_name + '</td><td>' + m.total_quantity + '</td><td>' + m.completed_quantity + '</td><td>' + m.completion_rate + '%</td></tr>';
                });
                return html + '</tbody></table></div>';
            }

            function buildProcessHtml(data) {
                var html = '<h5>工序信息</h5><p><strong>工序名称：</strong>' + (data.process.name || '') + '</p>';
                html += '<h5>分配记录</h5><div class="table-responsive"><table class="table table-bordered table-striped">';
                html += '<thead><tr><th>订单</th><th>产品</th><th>型号</th><th>员工</th><th>分配数量</th><th>已完成</th><th>完成率</th></tr></thead><tbody>';
                $.each(data.allocations, function (_, a) {
                    html += '<tr><td>' + a.order_name + '</td><td>' + a.product_name + '</td><td>' + a.model_name + '</td><td>' + a.employee_name + '</td><td>' + a.allocated_quantity + '</td><td>' + a.completed_quantity + '</td><td>' + a.completion_rate + '%</td></tr>';
                });
                return html + '</tbody></table></div>';
            }

            function buildEmployeeHtml(data) {
                var html = '<h5>员工信息</h5><p><strong>姓名：</strong>' + (data.user.nickname || '') + '</p>';
                html += '<h5>工作分配记录</h5><div class="table-responsive"><table class="table table-bordered table-striped">';
                html += '<thead><tr><th>订单</th><th>产品</th><th>型号</th><th>工序</th><th>分配数量</th><th>已完成</th><th>完成率</th></tr></thead><tbody>';
                $.each(data.allocations, function (_, a) {
                    html += '<tr><td>' + a.order_name + '</td><td>' + a.product_name + '</td><td>' + a.model_name + '</td><td>' + a.process_name + '</td><td>' + a.allocated_quantity + '</td><td>' + a.completed_quantity + '</td><td>' + a.completion_rate + '%</td></tr>';
                });
                return html + '</tbody></table></div>';
            }

            initCharts();
        },
        add: function () {
            Controller.api.initOrderModelSelect();
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.initOrderModelSelect();
            Controller.api.bindevent();
        },
        api: {
            initOrderModelSelect: function () {
                var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
                var $order = $('#order_id');
                var $model = $('#model_id');
                if ($order.length === 0 || $model.length === 0) {
                    return;
                }
                function loadModels(selectedId) {
                    var orderId = $order.val();
                    if (!orderId) {
                        $model.html('<option value="">请先选择订单</option>');
                        return;
                    }
                    $.get(base + '/mes/production_plan/getOrderModels', {order_id: orderId}, function(r) {
                        if (r.code == 1 && r.data) {
                            var html = '<option value="">请选择型号</option>';
                            $.each(r.data, function(i, item) {
                                html += '<option value="' + item.id + '">' + item.name + ' (数量: ' + item.quantity + ')</option>';
                            });
                            $model.html(html);
                            var target = selectedId || $model.data('selected');
                            if (target) {
                                $model.val(String(target));
                            }
                        } else {
                            $model.html('<option value="">该订单暂无型号</option>');
                        }
                    }, 'json');
                }
                $order.off('change').on('change', function() {
                    loadModels('');
                });
                if ($order.val()) {
                    loadModels('');
                }
            },
            bindevent: function () {
                var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
                var form = $('form#form-add, form#form-edit');
                if (form.length) {
                    var action = form.attr('action') || (form.attr('id') === 'form-add' ? (base + '/mes/production_plan/add') : (base + '/mes/production_plan/edit'));
                    form.attr('action', action);
                    form.on('submit', function (e) {
                        e.preventDefault();
                        var url = $(this).attr('action');
                        if (url.indexOf('?ids=') === -1 && form.attr('id') === 'form-edit') {
                            var id = $('input[name="row[id]"]').val();
                            if (id) url += '?ids=' + id;
                        }
                        $.post(url, $(this).serialize(), function (r) {
                            if (r && r.msg) {
                                alert(r.msg);
                            }
                            if (r && r.code === 1) {
                                location.href = base + '/mes/production_plan/index';
                            }
                        }, 'json').fail(function(xhr) {
                            try {
                                var r = JSON.parse(xhr.responseText);
                                alert(r.msg || '操作失败');
                            } catch(e) {
                                alert('操作失败');
                            }
                        });
                    });
                }
            }
        }
    };

    // 小写别名：驼峰方法经 strtolower 后的形式
    Controller.progressstats  = function () { /* 服务端渲染 */ };
    Controller.getordermodels = function () { /* AJAX 接口，无视图 */ };
    Controller.getorderdetails    = function () { /* AJAX 接口 */ };
    Controller.getproductdetails  = function () { /* AJAX 接口 */ };
    Controller.getprocessdetails  = function () { /* AJAX 接口 */ };
    Controller.getemployeedetails = function () { /* AJAX 接口 */ };

    window.__backendController = Controller;
})();
