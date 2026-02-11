/**
 * 生产数据大屏页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var getDashboardDataUrl = base + '/mes/bi/getDashboardData';
    var orderChart, planChart, trendChart;

    function loadDashboardData() {
        $.get(getDashboardDataUrl, function(r) {
            if (r.code == 1 && r.data) {
                var data = r.data;
                
                $('#today-quantity').text(data.today.quantity || 0);
                $('#today-wage').text('¥' + parseFloat(data.today.wage || 0).toFixed(2));
                $('#active-allocations').text(data.active_allocations || 0);
                $('#pending-reports').text(data.pending_reports || 0);
                
                if (orderChart) orderChart.destroy();
                orderChart = new Chart(document.getElementById('orderChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['待生产', '生产中', '已完成', '已取消'],
                        datasets: [{
                            data: [data.orders[0] || 0, data.orders[1] || 0, data.orders[2] || 0, data.orders[3] || 0],
                            backgroundColor: ['#6c757d', '#007bff', '#28a745', '#dc3545']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
                
                if (planChart) planChart.destroy();
                planChart = new Chart(document.getElementById('planChart'), {
                    type: 'bar',
                    data: {
                        labels: ['待开始', '进行中', '已完成', '已暂停'],
                        datasets: [{
                            label: '数量',
                            data: [data.plans[0] || 0, data.plans[1] || 0, data.plans[2] || 0, data.plans[3] || 0],
                            backgroundColor: ['#6c757d', '#007bff', '#28a745', '#ffc107']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } }
                    }
                });
                
                if (trendChart) trendChart.destroy();
                var trendLabels = data.trend.map(function(item) { return item.date; });
                trendChart = new Chart(document.getElementById('trendChart'), {
                    type: 'line',
                    data: {
                        labels: trendLabels,
                        datasets: [
                            {
                                label: '产量',
                                data: data.trend.map(function(item) { return item.quantity; }),
                                borderColor: '#007bff',
                                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                yAxisID: 'y'
                            },
                            {
                                label: '工资',
                                data: data.trend.map(function(item) { return item.wage; }),
                                borderColor: '#28a745',
                                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { type: 'linear', display: true, position: 'left', beginAtZero: true },
                            y1: { type: 'linear', display: true, position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } }
                        }
                    }
                });
            }
        }, 'json');
    }

    var Controller = {
        dashboard: function () {
            loadDashboardData();
            setInterval(loadDashboardData, 30000);
        },
        productionEfficiency: function () {
            // 生产效率分析
        },
        qualityAnalysis: function () {
            // 质量分析
        },
        costAnalysis: function () {
            // 成本分析
        }
    };

    window.__backendController = Controller;
})();
