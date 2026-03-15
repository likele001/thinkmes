/**
 * 生产进度大屏（与 scanwork 一致：6 卡片 + 4 图表 + 4 Tab 表 + 刷新/暂停/全屏/更新秒数）
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var charts = {};
    var lastLoadTime = 0;
    var updateSec = 0;
    var updateSecTimer = null;
    var refreshTimer = null;
    var paused = false;

    function renderOverallStats(os) {
        if (!os) return;
        var totalPlans = os.total_plans != null ? os.total_plans : 0;
        var totalQty = os.total_quantity != null ? os.total_quantity : 0;
        $('#stat-total-orders').text(os.total_orders != null ? os.total_orders : 0);
        $('#stat-total-plans').text(totalPlans);
        $('#stat-total-allocations').text(os.total_allocations != null ? os.total_allocations : 0);
        $('#stat-total-quantity').text(totalQty);
        $('#stat-completed-quantity').text(os.completed_quantity != null ? os.completed_quantity : 0);
        $('#stat-completion-rate').text((os.completion_rate != null ? os.completion_rate : 0) + '%');
        // 无排产/报工数据时显示操作提示
        var $hint = $('#dashboard-empty-hint');
        if ($hint.length) {
            if (totalPlans === 0 && totalQty === 0) {
                $hint.removeClass('d-none');
            } else {
                $hint.addClass('d-none');
            }
        }
    }

    function renderTableOrder(list) {
        var tbody = document.getElementById('table-order');
        if (!tbody) return;
        list = list || [];
        if (list.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">暂无数据</td></tr>';
            return;
        }
        tbody.innerHTML = list.map(function (r) {
            return '<tr><td>' + (r.order_name || '') + '</td><td>' + (r.order_no || '') + '</td><td>' + (r.total_plans || 0) + '</td><td>' + (r.total_quantity || 0) + '</td><td>' + (r.completed_quantity || 0) + '</td><td>' + (r.completion_rate || 0) + '%</td></tr>';
        }).join('');
    }

    function renderTableProduct(list) {
        var tbody = document.getElementById('table-product');
        if (!tbody) return;
        list = list || [];
        if (list.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">暂无数据</td></tr>';
            return;
        }
        tbody.innerHTML = list.map(function (r) {
            return '<tr><td>' + (r.product_name || '') + '</td><td>' + (r.total_plans || 0) + '</td><td>' + (r.total_quantity || 0) + '</td><td>' + (r.completed_quantity || 0) + '</td><td>' + (r.completion_rate || 0) + '%</td></tr>';
        }).join('');
    }

    function renderTableProcess(list) {
        var tbody = document.getElementById('table-process');
        if (!tbody) return;
        list = list || [];
        if (list.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">暂无数据</td></tr>';
            return;
        }
        tbody.innerHTML = list.map(function (r) {
            return '<tr><td>' + (r.process_name || '') + '</td><td>' + (r.total_allocations || 0) + '</td><td>' + (r.total_quantity || 0) + '</td><td>' + (r.completed_quantity || 0) + '</td><td>' + (r.completion_rate || 0) + '%</td></tr>';
        }).join('');
    }

    function renderTableEmployee(list) {
        var tbody = document.getElementById('table-employee');
        if (!tbody) return;
        list = list || [];
        if (list.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">暂无数据</td></tr>';
            return;
        }
        tbody.innerHTML = list.map(function (r) {
            return '<tr><td>' + (r.user_name || '') + '</td><td>' + (r.total_allocations || 0) + '</td><td>' + (r.total_quantity || 0) + '</td><td>' + (r.completed_quantity || 0) + '</td><td>' + (r.completion_rate || 0) + '%</td></tr>';
        }).join('');
    }

    function renderCharts(data) {
        if (typeof echarts === 'undefined') return;
        var os = data.overall_stats || {};
        var totalQty = parseInt(os.total_quantity, 10) || 0;
        var completed = parseInt(os.completed_quantity, 10) || 0;
        var remaining = Math.max(0, totalQty - completed);

        var elOverall = document.getElementById('chart-overall');
        if (elOverall) {
            if (!charts.overall) charts.overall = echarts.init(elOverall);
            charts.overall.setOption({
                tooltip: { trigger: 'axis' },
                grid: { left: '8%', right: '8%', bottom: '8%', top: '5%', containLabel: true },
                xAxis: { type: 'category', data: ['总数量', '已完成', '剩余'] },
                yAxis: { type: 'value' },
                series: [{ type: 'bar', barWidth: '50%', data: [
                    { value: totalQty, itemStyle: { color: '#0d6efd' } },
                    { value: completed, itemStyle: { color: '#198754' } },
                    { value: remaining, itemStyle: { color: '#fd7e14' } }
                ] }]
            });
        }

        var processList = data.process_stats || [];
        var elProcess = document.getElementById('chart-process');
        if (elProcess && processList.length > 0) {
            if (!charts.process) charts.process = echarts.init(elProcess);
            charts.process.setOption({
                tooltip: { trigger: 'item' },
                legend: { orient: 'vertical', left: 'left', type: 'scroll' },
                series: [{ type: 'pie', radius: '60%', data: processList.map(function (p) { return { value: p.completion_rate || 0, name: p.process_name || '' }; }) }]
            });
        }

        var empList = data.employee_stats || [];
        var elEmp = document.getElementById('chart-employee');
        if (elEmp && empList.length > 0) {
            if (!charts.employee) charts.employee = echarts.init(elEmp);
            charts.employee.setOption({
                tooltip: { trigger: 'axis' },
                grid: { left: '15%', right: '10%', bottom: '10%', containLabel: true },
                xAxis: { type: 'value', max: 100 },
                yAxis: { type: 'category', data: empList.map(function (e) { return e.user_name || ''; }).slice(0, 10) },
                series: [{ type: 'bar', data: empList.map(function (e) { return e.completion_rate || 0; }).slice(0, 10), itemStyle: { color: '#198754' } }]
            });
        }

        var orderList = data.order_stats || [];
        var elOrder = document.getElementById('chart-order');
        if (elOrder && orderList.length > 0) {
            if (!charts.order) charts.order = echarts.init(elOrder);
            charts.order.setOption({
                tooltip: { trigger: 'axis' },
                grid: { left: '15%', right: '10%', bottom: '15%', containLabel: true },
                xAxis: { type: 'category', data: orderList.map(function (o) { return o.order_name || o.order_no || ''; }).slice(0, 8), axisLabel: { interval: 0, rotate: 20 } },
                yAxis: { type: 'value', max: 100 },
                series: [{ type: 'line', smooth: true, data: orderList.map(function (o) { return o.completion_rate || 0; }).slice(0, 8), itemStyle: { color: '#0d6efd' } }]
            });
        }
    }

    function tickUpdateSec() {
        if (lastLoadTime > 0) {
            updateSec = Math.floor((Date.now() / 1000) - lastLoadTime);
            var el = document.getElementById('dashboard-update-sec');
            if (el) el.textContent = updateSec;
        }
    }

    function loadData() {
        if (typeof jQuery === 'undefined') {
            setTimeout(loadData, 100);
            return;
        }
        var $ = jQuery;
        $.get(base + '/mes/bi/getDashboardData', function (r) {
            if (r.code !== 1 || !r.data) return;
            var d = r.data;
            lastLoadTime = Math.floor(Date.now() / 1000);
            updateSec = 0;
            renderOverallStats(d.overall_stats);
            renderTableOrder(d.order_stats);
            renderTableProduct(d.product_stats);
            renderTableProcess(d.process_stats);
            renderTableEmployee(d.employee_stats);
            renderCharts(d);
        }, 'json').fail(function () {
            renderTableOrder([]);
            renderTableProduct([]);
            renderTableProcess([]);
            renderTableEmployee([]);
        });
    }

    function startRefreshTimer() {
        if (refreshTimer) clearInterval(refreshTimer);
        refreshTimer = setInterval(function () {
            if (!paused) loadData();
        }, 30000);
    }

    function init() {
        if (!document.getElementById('stat-total-orders')) return;
        loadData();
        startRefreshTimer();
        if (!updateSecTimer) updateSecTimer = setInterval(tickUpdateSec, 1000);

        var $ = window.jQuery || window.$;
        if ($) {
            $(document).on('click', '#dashboard-btn-refresh', function () { loadData(); });
            $(document).on('click', '#dashboard-btn-pause', function () {
                paused = !paused;
                $(this).find('i').toggleClass('fa-pause fa-play');
                $(this).html(paused ? '<i class="fas fa-play"></i> 继续' : '<i class="fas fa-pause"></i> 暂停');
            });
            $(document).on('click', '#dashboard-btn-fullscreen', function () {
                var el = document.querySelector('.dashboard-screen');
                if (!el) return;
                if (!document.fullscreenElement) {
                    el.requestFullscreen ? el.requestFullscreen() : el.webkitRequestFullScreen && el.webkitRequestFullScreen();
                } else {
                    document.exitFullscreen && document.exitFullscreen();
                }
            });
        }

        window.addEventListener('resize', function () {
            Object.keys(charts).forEach(function (k) { if (charts[k]) charts[k].resize(); });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
