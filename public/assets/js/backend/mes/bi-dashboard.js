(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    var charts = {};
    var paused = false;
    var coreTimer = null;
    var fullTimer = null;
    var tickTimer = null;
    var lastServerTime = 0;
    var lastClientTime = 0;

    function openLink(url) {
        if (!url) return;
        if (url.indexOf('http://') === 0 || url.indexOf('https://') === 0) {
            window.open(url, '_blank');
            return;
        }
        if (url.indexOf('/admin') === 0) {
            window.open(url, '_blank');
            return;
        }
        if (url.indexOf('/mes') === 0) {
            window.open(base + url, '_blank');
            return;
        }
        window.open(url, '_blank');
    }

    function formatNumber(v, unit) {
        if (v == null || v === '') return '0';
        var n = Number(v);
        if (!isFinite(n)) return String(v);
        if (unit === '%' || unit === '星' || unit === '天') return n.toFixed(1).replace(/\.0$/, '');
        return String(Math.round(n));
    }

    function ensureChart(key, el) {
        if (!el || typeof echarts === 'undefined') return null;
        if (charts[key] && !charts[key].isDisposed && charts[key].getDom && charts[key].getDom()) return charts[key];
        charts[key] = echarts.init(el);
        return charts[key];
    }

    function setTimeFromServer(serverTime) {
        if (!serverTime) return;
        lastServerTime = Number(serverTime) || 0;
        lastClientTime = Date.now();
        tickTime();
    }

    function getShift(h) {
        if (h >= 6 && h < 14) return '早班';
        if (h >= 14 && h < 22) return '中班';
        return '晚班';
    }

    function pad2(n) {
        n = Number(n) || 0;
        return n < 10 ? '0' + n : String(n);
    }

    function tickTime() {
        if (!lastServerTime) return;
        var diff = Date.now() - lastClientTime;
        var t = new Date((lastServerTime * 1000) + diff);
        var y = t.getFullYear();
        var m = pad2(t.getMonth() + 1);
        var d = pad2(t.getDate());
        var hh = pad2(t.getHours());
        var mm = pad2(t.getMinutes());
        var ss = pad2(t.getSeconds());
        var dateEl = document.getElementById('mes-date');
        var clockEl = document.getElementById('mes-clock');
        var shiftEl = document.getElementById('mes-shift');
        if (dateEl) dateEl.textContent = y + '-' + m + '-' + d;
        if (clockEl) clockEl.textContent = hh + ':' + mm + ':' + ss;
        if (shiftEl) shiftEl.textContent = getShift(t.getHours());
    }

    function renderKpis(kpis) {
        kpis = kpis || [];
        for (var i = 0; i < kpis.length; i++) {
            var k = kpis[i] || {};
            var key = k.key || '';
            if (!key) continue;
            var el = document.querySelector('.mes-kpi-item[data-kpi="' + key + '"]');
            if (!el) continue;
            el.setAttribute('data-warn', k.warn || 'success');
            el.dataset.link = k.link || '';
            var numEl = el.querySelector('[data-kpi-value]');
            if (numEl) numEl.textContent = formatNumber(k.value, k.unit);
        }
    }

    function renderAlerts(alerts) {
        alerts = alerts || {};
        var badge = document.getElementById('mes-alert-badge');
        if (badge) badge.textContent = String(alerts.total || 0);
        var listEl = document.getElementById('mes-alert-list');
        if (!listEl) return;
        var list = alerts.list || [];
        if (!list.length) {
            listEl.innerHTML = '<div class="mes-item"><div class="mes-item-title">暂无异常</div><div class="mes-item-meta"></div></div>';
            return;
        }
        listEl.innerHTML = list.map(function (it) {
            var level = it.level || 'warning';
            var tagCls = level === 'danger' ? 'mes-tag-danger' : 'mes-tag-warning';
            var title = (it.type || '') + '';
            var cnt = String(it.count || 0);
            var link = it.link || '';
            return '<div class="mes-item" data-link="' + link + '"><div class="mes-item-title">' + title + '</div><div class="mes-item-meta"><span class="mes-tag ' + tagCls + '">' + cnt + '</span></div></div>';
        }).join('');
    }

    function renderWorkorders(list) {
        var tbody = document.getElementById('mes-workorders');
        if (!tbody) return;
        list = list || [];
        if (!list.length) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:rgba(219,231,255,.65);padding:14px 8px;">暂无数据</td></tr>';
            return;
        }
        tbody.innerHTML = list.map(function (r) {
            var id = r.id || '';
            var st = Number(r.status || 0);
            var stCls = st === 1 ? 'mes-tag-s1' : (st === 2 ? 'mes-tag-s2' : 'mes-tag-s0');
            var stTxt = r.status_text || '';
            var order = (r.order_no || '') + (r.model_name ? ('/' + r.model_name) : '');
            var proc = r.process_name || '';
            var user = r.user_name || '';
            var prog = (r.progress != null ? r.progress : 0) + '%';
            var pe = r.planned_end_time_text || '';
            var link = r.link || '';
            return '<tr data-link="' + link + '"><td>' + id + '</td><td><span class="mes-tag ' + stCls + '">' + stTxt + '</span></td><td>' + order + '</td><td>' + proc + '</td><td>' + user + '</td><td style="text-align:right;">' + prog + '</td><td>' + pe + '</td></tr>';
        }).join('');
    }

    function renderFlow(flow) {
        var el = document.getElementById('mes-order-flow');
        if (!el) return;
        flow = flow || [];
        if (!flow.length) {
            el.innerHTML = '';
            return;
        }
        el.innerHTML = flow.map(function (s) {
            var title = s.title || '';
            var value = String(s.value || 0);
            var link = s.link || '';
            return '<div class="mes-flow-item" data-link="' + link + '"><div class="mes-flow-title">' + title + '</div><div class="mes-flow-value">' + value + '</div></div>';
        }).join('');
    }

    function renderCapacity(capacityTop) {
        var chartEl = document.getElementById('mes-chart-capacity');
        var listEl = document.getElementById('mes-capacity-list');
        capacityTop = capacityTop || [];

        if (listEl) {
            if (!capacityTop.length) {
                listEl.innerHTML = '<tr><td colspan="3" style="text-align:center;color:rgba(219,231,255,.65);padding:14px 8px;">暂无数据</td></tr>';
            } else {
                listEl.innerHTML = capacityTop.slice(0, 10).map(function (r) {
                    return '<tr><td>' + (r.user_name || '') + '</td><td style="text-align:right;">' + formatNumber(r.quantity, '') + '</td><td style="text-align:right;">' + formatNumber(r.hours, '天') + '</td></tr>';
                }).join('');
            }
        }

        if (!chartEl) return;
        var c = ensureChart('capacity', chartEl);
        if (!c) return;
        var top5 = capacityTop.slice(0, 5);
        var names = top5.map(function (r) { return r.user_name || ''; }).reverse();
        var vals = top5.map(function (r) { return Number(r.quantity || 0); }).reverse();
        c.setOption({
            grid: { left: 40, right: 16, top: 20, bottom: 20, containLabel: true },
            xAxis: { type: 'value', axisLine: { lineStyle: { color: 'rgba(219,231,255,.25)' } }, splitLine: { lineStyle: { color: 'rgba(219,231,255,.08)' } } },
            yAxis: { type: 'category', data: names, axisLine: { lineStyle: { color: 'rgba(219,231,255,.25)' } }, axisLabel: { color: 'rgba(219,231,255,.85)' } },
            series: [{ type: 'bar', data: vals, barWidth: 10, itemStyle: { color: '#3a7bff' } }]
        }, true);
    }

    function renderRoute(route) {
        route = route || {};
        var chartEl = document.getElementById('mes-chart-route');
        var listEl = document.getElementById('mes-route-missing');
        var total = Number(route.total_models || 0);
        var withRoute = Number(route.with_route || 0);
        var missing = Math.max(0, total - withRoute);

        if (listEl) {
            var miss = route.missing || [];
            if (!miss.length) {
                listEl.innerHTML = '<div class="mes-item"><div class="mes-item-title">暂无工艺缺失</div><div class="mes-item-meta"></div></div>';
            } else {
                listEl.innerHTML = miss.map(function (m) {
                    return '<div class="mes-item" data-link="' + (m.link || '') + '"><div class="mes-item-title">' + (m.label || '') + '</div><div class="mes-item-meta"><span class="mes-tag mes-tag-warning">未配置</span></div></div>';
                }).join('');
            }
        }

        if (!chartEl) return;
        var c = ensureChart('route', chartEl);
        if (!c) return;
        c.setOption({
            tooltip: { trigger: 'item' },
            legend: { bottom: 0, textStyle: { color: 'rgba(219,231,255,.75)' } },
            series: [{
                type: 'pie',
                radius: ['45%', '70%'],
                label: { show: false },
                labelLine: { show: false },
                data: [
                    { name: '已配置', value: withRoute, itemStyle: { color: '#2ec878' } },
                    { name: '未配置', value: missing, itemStyle: { color: '#ffd152' } }
                ]
            }]
        }, true);
    }

    function renderAllocationStatus(stats) {
        var chartEl = document.getElementById('mes-chart-allocation-status');
        var listEl = document.getElementById('mes-allocation-status-list');
        stats = stats || [];
        var total = stats.reduce(function (a, b) { return a + Number(b.value || 0); }, 0);
        if (listEl) {
            if (!stats.length) {
                listEl.innerHTML = '<div class="mes-item"><div class="mes-item-title">暂无数据</div><div class="mes-item-meta"></div></div>';
            } else {
                listEl.innerHTML = stats.map(function (s) {
                    var pct = total > 0 ? ((Number(s.value || 0) / total) * 100).toFixed(1) : '0.0';
                    return '<div class="mes-item"><div class="mes-item-title">' + (s.name || '') + '</div><div class="mes-item-meta">' + (s.value || 0) + ' / ' + pct + '%</div></div>';
                }).join('');
            }
        }
        if (!chartEl) return;
        var c = ensureChart('allocation_status', chartEl);
        if (!c) return;
        c.setOption({
            tooltip: { trigger: 'item' },
            legend: { bottom: 0, textStyle: { color: 'rgba(219,231,255,.75)' } },
            series: [{
                type: 'pie',
                radius: ['45%', '70%'],
                label: { show: false },
                labelLine: { show: false },
                data: stats.map(function (s) {
                    var color = s.name === '已完成' ? '#2ec878' : (s.name === '进行中' ? '#3a7bff' : '#9aa6c7');
                    return { name: s.name, value: s.value, itemStyle: { color: color } };
                })
            }]
        }, true);
    }

    function renderOutputTrend(trend) {
        var chartEl = document.getElementById('mes-chart-output-trend');
        if (!chartEl) return;
        trend = trend || [];
        var c = ensureChart('output_trend', chartEl);
        if (!c) return;
        var x = trend.map(function (r) { return (r.date || '').slice(5); });
        var y = trend.map(function (r) { return Number(r.quantity || 0); });
        c.setOption({
            tooltip: { trigger: 'axis' },
            grid: { left: 40, right: 16, top: 20, bottom: 30, containLabel: true },
            xAxis: { type: 'category', data: x, axisLine: { lineStyle: { color: 'rgba(219,231,255,.25)' } }, axisLabel: { color: 'rgba(219,231,255,.75)' } },
            yAxis: { type: 'value', axisLine: { lineStyle: { color: 'rgba(219,231,255,.25)' } }, splitLine: { lineStyle: { color: 'rgba(219,231,255,.08)' } }, axisLabel: { color: 'rgba(219,231,255,.75)' } },
            series: [
                { type: 'bar', data: y, barWidth: 10, itemStyle: { color: 'rgba(58,123,255,.65)' } },
                { type: 'line', data: y, smooth: true, itemStyle: { color: '#2ec878' }, lineStyle: { width: 2 } }
            ]
        }, true);
    }

    function renderPurchase(purchase) {
        purchase = purchase || {};
        var pendingEl = document.getElementById('mes-purchase-pending');
        var mrpCountEl = document.getElementById('mes-mrp-count');
        var mrpListEl = document.getElementById('mes-mrp-list');
        if (pendingEl) pendingEl.textContent = String(purchase.pending_count || 0);
        if (mrpCountEl) mrpCountEl.textContent = String(purchase.mrp_shortage_count || 0);
        var list = purchase.mrp_shortage_list || [];
        if (mrpListEl) {
            if (!list.length) {
                mrpListEl.innerHTML = '<tr><td colspan="2" style="text-align:center;color:rgba(219,231,255,.65);padding:14px 8px;">暂无缺料</td></tr>';
            } else {
                mrpListEl.innerHTML = list.map(function (r) {
                    var title = (r.material_name || '') + (r.material_code ? ('(' + r.material_code + ')') : '');
                    return '<tr data-link="' + (r.link || '') + '"><td>' + title + '</td><td style="text-align:right;">' + formatNumber(r.shortage, '') + '</td></tr>';
                }).join('');
            }
        }
    }

    function renderStock(stock) {
        stock = stock || {};
        var chartEl = document.getElementById('mes-chart-stock-structure');
        var listEl = document.getElementById('mes-stock-warning');
        var material = Number(stock.material_stock || 0);
        var wip = Number(stock.wip_stock || 0);
        var product = Number(stock.product_stock || 0);

        if (chartEl) {
            var c = ensureChart('stock_structure', chartEl);
            if (c) {
                c.setOption({
                    tooltip: { trigger: 'item' },
                    legend: { orient: 'vertical', right: 0, top: 'center', type: 'scroll', textStyle: { color: 'rgba(219,231,255,.75)' } },
                    series: [{
                        type: 'pie',
                        radius: ['45%', '70%'],
                        label: { show: false },
                        labelLine: { show: false },
                        data: [
                            { name: '原材料', value: material, itemStyle: { color: '#3a7bff' } },
                            { name: '在制', value: wip, itemStyle: { color: '#ffd152' } },
                            { name: '成品', value: product, itemStyle: { color: '#2ec878' } }
                        ]
                    }]
                }, true);
            }
        }

        if (listEl) {
            var list = stock.warning_list || [];
            if (!list.length) {
                listEl.innerHTML = '<div class="mes-item"><div class="mes-item-title">暂无库存预警</div><div class="mes-item-meta"></div></div>';
            } else {
                listEl.innerHTML = list.map(function (r) {
                    var title = (r.name || '') + (r.code ? ('(' + r.code + ')') : '');
                    return '<div class="mes-item" data-link="' + (r.link || '') + '"><div class="mes-item-title">' + title + '</div><div class="mes-item-meta"><span class="mes-tag mes-tag-danger">缺 ' + formatNumber(r.shortage, '') + '</span></div></div>';
                }).join('');
            }
        }
    }

    function renderShipment(shipment) {
        shipment = shipment || {};
        var cntEl = document.getElementById('mes-shipment-overdue');
        var listEl = document.getElementById('mes-shipment-overdue-list');
        if (cntEl) cntEl.textContent = String(shipment.overdue_count || 0);
        var list = shipment.overdue_list || [];
        if (listEl) {
            if (!list.length) {
                listEl.innerHTML = '<div class="mes-item"><div class="mes-item-title">暂无逾期</div><div class="mes-item-meta"></div></div>';
            } else {
                listEl.innerHTML = list.map(function (r) {
                    var title = (r.order_no || '') + (r.customer_name ? (' / ' + r.customer_name) : '');
                    var meta = r.delivery_time_text || '';
                    return '<div class="mes-item" data-link="' + (r.link || '') + '"><div class="mes-item-title">' + title + '</div><div class="mes-item-meta"><span class="mes-tag mes-tag-danger">' + meta + '</span></div></div>';
                }).join('');
            }
        }
    }

    function renderQuality(quality) {
        quality = quality || {};
        var chartEl = document.getElementById('mes-chart-defect-process');
        var listEl = document.getElementById('mes-quality-meta');

        if (listEl) {
            var items = [
                { title: '待审核报工', value: quality.pending_reports || 0, level: 'warning' },
                { title: '今日不良数量', value: quality.today_bad || 0, level: 'danger' },
                { title: '待质检', value: quality.pending_check_count || 0, level: 'warning' }
            ];
            listEl.innerHTML = items.map(function (it) {
                var tagCls = it.level === 'danger' ? 'mes-tag-danger' : 'mes-tag-warning';
                return '<div class="mes-item"><div class="mes-item-title">' + it.title + '</div><div class="mes-item-meta"><span class="mes-tag ' + tagCls + '">' + String(it.value || 0) + '</span></div></div>';
            }).join('');
        }

        if (!chartEl) return;
        var c = ensureChart('defect_process', chartEl);
        if (!c) return;
        var ds = (quality.defect_by_process || []).map(function (d) {
            return { name: d.process_name || '', value: Number(d.bad_quantity || 0) };
        });
        c.setOption({
            tooltip: { trigger: 'item' },
            legend: { orient: 'vertical', right: 0, top: 'center', type: 'scroll', textStyle: { color: 'rgba(219,231,255,.75)' } },
            series: [{ type: 'pie', radius: ['45%', '70%'], label: { show: false }, labelLine: { show: false }, data: ds }]
        }, true);
    }

    function renderCustomer(customer) {
        customer = customer || {};
        var starEl = document.getElementById('mes-customer-star');
        if (starEl) starEl.textContent = formatNumber(customer.satisfaction, '星') + '/5';
        var tbody = document.getElementById('mes-customer-top');
        var list = customer.top_customers || [];
        if (tbody) {
            if (!list.length) {
                tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;color:rgba(219,231,255,.65);padding:14px 8px;">暂无数据</td></tr>';
            } else {
                tbody.innerHTML = list.map(function (r) {
                    return '<tr data-link="' + (r.link || '') + '"><td>' + (r.customer_name || '') + '</td><td style="text-align:right;">' + formatNumber(r.quantity, '') + '</td></tr>';
                }).join('');
            }
        }
    }

    function bindClicks() {
        var root = document.getElementById('mes-screen');
        if (!root) return;

        root.addEventListener('click', function (e) {
            var t = e.target;
            if (!t) return;

            var menuBtn = t.closest && t.closest('#mes-btn-menu');
            if (menuBtn) {
                var dd = menuBtn.parentNode;
                if (dd && dd.classList) dd.classList.toggle('open');
                return;
            }
            if (t.closest && t.closest('.mes-menu-item')) {
                var link = t.closest('.mes-menu-item').getAttribute('data-link') || '';
                openLink(link);
                var dd2 = document.querySelector('.mes-dropdown');
                if (dd2 && dd2.classList) dd2.classList.remove('open');
                return;
            }
            if (t.closest && t.closest('.mes-dropdown')) return;
            var dd = document.querySelector('.mes-dropdown');
            if (dd && dd.classList) dd.classList.remove('open');

            var item = t.closest && t.closest('[data-link]');
            if (item && item.getAttribute) {
                var url = item.getAttribute('data-link') || '';
                if (url) openLink(url);
            }
        });

        var kpis = document.querySelectorAll('.mes-kpi-item');
        for (var i = 0; i < kpis.length; i++) {
            (function (el) {
                el.addEventListener('click', function () {
                    var link = el.dataset.link || '';
                    if (link) openLink(link);
                });
            })(kpis[i]);
        }
    }

    function load(mode, done) {
        var $ = window.jQuery || window.$;
        if (!$) return;
        $.get(base + '/mes/bi/getDashboardData', { mode: mode }, function (r) {
            if (!r || r.code !== 1 || !r.data) return;
            done && done(r.data);
        }, 'json');
    }

    function applyCore(d) {
        setTimeFromServer(d.server_time);
        renderKpis(d.kpis);
        renderAlerts(d.alerts);
        renderWorkorders(d.workorders);
        renderFlow(d.flow);
    }

    function applyFull(d) {
        if (d.production) {
            renderCapacity(d.production.capacity_top);
            renderRoute(d.production.route);
            renderAllocationStatus(d.production.allocation_status);
            renderOutputTrend(d.production.output_trend);
        }
        renderPurchase(d.purchase);
        renderStock(d.stock);
        renderShipment(d.shipment);
        renderQuality(d.quality);
        renderCustomer(d.customer);
    }

    function refreshCore() {
        if (paused) return;
        load('core', function (d) {
            applyCore(d);
        });
    }

    function refreshFull() {
        if (paused) return;
        load('full', function (d) {
            applyCore(d);
            applyFull(d);
        });
    }

    function init() {
        if (!document.getElementById('mes-screen')) return;
        bindClicks();
        refreshFull();

        coreTimer = setInterval(refreshCore, 5000);
        fullTimer = setInterval(refreshFull, 30000);
        tickTimer = setInterval(tickTime, 1000);

        var btnRefresh = document.getElementById('mes-btn-refresh');
        var btnPause = document.getElementById('mes-btn-pause');
        var btnFull = document.getElementById('mes-btn-fullscreen');
        if (btnRefresh) btnRefresh.addEventListener('click', function () { refreshFull(); });
        if (btnPause) btnPause.addEventListener('click', function () {
            paused = !paused;
            btnPause.textContent = paused ? '继续' : '暂停';
        });
        if (btnFull) btnFull.addEventListener('click', function () {
            if (window.self !== window.top) {
                openLink(base + '/mes/bi/dashboard?iframe=1&screen=1');
                return;
            }
            var el = document.documentElement;
            if (!document.fullscreenElement) {
                if (el.requestFullscreen) el.requestFullscreen();
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
            }
        });

        window.addEventListener('resize', function () {
            Object.keys(charts).forEach(function (k) {
                if (charts[k] && charts[k].resize) charts[k].resize();
            });
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
