/**
 * 智能排产(计件)页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/schedule/index';
    var ganttUrl = base + '/mes/schedule/ganttData';
    var genUrl = base + '/mes/schedule/generate';
    var pubUrl = base + '/mes/schedule/publish';
    var revokeUrl = base + '/mes/schedule/revoke';
    var planEditUrl = base + '/mes/production_plan/edit';

    var currentBatch = '';
    var currentView = 'list';
    var lastGantt = null;
    var lastGanttMode = 'user';

    function statusFmt(v) {
        var map = {0: ['待下发', 'secondary'], 1: ['已下发', 'success'], 2: ['已完成', 'primary'], 3: ['已撤销', 'danger']};
        var it = map[v] || ['未知', 'secondary'];
        return '<span class="badge badge-' + it[1] + '">' + it[0] + '</span>';
    }

    function tableUrl() {
        var url = indexUrl;
        if (currentBatch) url += (url.indexOf('?') === -1 ? '?' : '&') + 'batch_id=' + encodeURIComponent(currentBatch);
        if (!currentBatch) {
            var startDate = $('#start-date').val() || '';
            var days = parseInt($('#days').val() || '7', 10);
            var filterDate = $('#filter-date').is(':checked') ? 1 : 0;
            if (filterDate && startDate) url += (url.indexOf('?') === -1 ? '?' : '&') + 'start_date=' + encodeURIComponent(startDate);
            if (filterDate && days) url += (url.indexOf('?') === -1 ? '?' : '&') + 'days=' + encodeURIComponent(String(days));
            if (filterDate) url += (url.indexOf('?') === -1 ? '?' : '&') + 'filter_date=1';
        }
        return url;
    }

    function setBatch(batchId, $table) {
        currentBatch = batchId || '';
        if (currentBatch) {
            $('#batch-tip').text('当前批次：' + currentBatch);
            try { localStorage.setItem('mes_schedule_current_batch', currentBatch); } catch (e) {}
        } else {
            $('#batch-tip').text('');
            try { localStorage.removeItem('mes_schedule_current_batch'); } catch (e) {}
        }
        if ($table) $table.bootstrapTable('refresh', { url: tableUrl() });
    }

    function getBatchFromSelection($table) {
        var rows = $table.bootstrapTable('getSelections') || [];
        if (!rows.length) return { ok: false, msg: '请先勾选一条排产记录（同一批次）' };
        var batches = {};
        var pending = 0;
        for (var i = 0; i < rows.length; i++) {
            var b = rows[i] && rows[i].batch_id ? rows[i].batch_id : '';
            if (b) batches[b] = 1;
            if (parseInt(rows[i].status, 10) === 0) pending++;
        }
        var keys = Object.keys(batches);
        if (keys.length !== 1) return { ok: false, msg: '请选择同一批次的记录（当前选择包含 ' + keys.length + ' 个批次）' };
        return { ok: true, batch: keys[0], pending: pending };
    }

    function showView(mode) {
        currentView = mode;
        if (mode === 'list') {
            $('#gantt-wrap').hide();
            $('#table').show();
            return;
        }
        $('#table').hide();
        $('#gantt-wrap').show();
    }

    function formatCellItems(items, mode) {
        if (!items || !items.length) return '';
        var lines = [];
        for (var i = 0; i < items.length; i++) {
            var it = items[i] || {};
            var plan = it.plan_code || ('计划#' + (it.plan_id || 0));
            var ord = it.order_no || ('订单#' + (it.order_id || 0));
            var model = it.model_name || ('型号#' + (it.model_id || 0));
            var proc = it.process_name || ('工序#' + (it.process_id || 0));
            var user = it.user_name || ('员工#' + (it.user_id || 0));
            var qty = it.quantity || 0;
            if (mode === 'user') lines.push(proc + ' ' + qty + '（' + plan + ' ' + ord + ' ' + model + '）');
            else lines.push(user + ' ' + qty + '（' + plan + ' ' + ord + ' ' + model + '）');
        }
        return lines.join('\n');
    }

    function renderGantt(data, mode) {
        lastGantt = data;
        lastGanttMode = mode;
        var dates = (data && data.dates) ? data.dates : [];
        var rows = mode === 'process' ? (data.by_process || []) : (data.by_user || []);
        var label = mode === 'process' ? '工序' : '员工';

        var html = '';
        html += '<thead><tr>';
        html += '<th class="gantt-rowhead">' + label + '</th>';
        for (var i = 0; i < dates.length; i++) {
            var d = dates[i];
            html += '<th>' + d + '</th>';
        }
        html += '</tr></thead><tbody>';
        for (var r = 0; r < rows.length; r++) {
            var row = rows[r] || {};
            html += '<tr>';
            html += '<td class="gantt-rowhead">' + (row.name || ('#' + (row.id || ''))) + '</td>';
            for (var j = 0; j < dates.length; j++) {
                var day = dates[j];
                var cell = (row.cells && row.cells[day]) ? row.cells[day] : null;
                var total = cell ? (cell.total || 0) : 0;
                var title = cell ? formatCellItems(cell.items || [], mode) : '';
                html += '<td class="gantt-cell" title="' + $('<div/>').text(title).html() + '">';
                html += total > 0 ? ('<span class="gantt-total">' + total + '</span>') : '';
                html += '</td>';
            }
            html += '</tr>';
        }
        html += '</tbody>';
        $('#gantt-table').html(html);
    }

    function loadGantt(mode) {
        if (!currentBatch) {
            alert('请先生成排产');
            return;
        }
        var startDate = $('#start-date').val() || '';
        var days = parseInt($('#days').val() || '7', 10);
        $.get(ganttUrl, { batch_id: currentBatch, start_date: startDate, days: days }, function (r) {
            if (!r || r.code != 1) {
                alert((r && r.msg) ? r.msg : '读取失败');
                return;
            }
            renderGantt(r.data || {}, mode);
            showView('gantt');
        }, 'json');
    }

    function exportCsv() {
        if (!lastGantt || !lastGantt.dates) {
            alert('请先切换到按员工/按工序视图');
            return;
        }
        var mode = lastGanttMode;
        var dates = lastGantt.dates || [];
        var rows = mode === 'process' ? (lastGantt.by_process || []) : (lastGantt.by_user || []);
        var head = ['mode', 'name', 'date', 'total', 'details'];
        var lines = [head.join(',')];
        for (var r = 0; r < rows.length; r++) {
            var row = rows[r] || {};
            var name = row.name || '';
            for (var i = 0; i < dates.length; i++) {
                var d = dates[i];
                var cell = (row.cells && row.cells[d]) ? row.cells[d] : null;
                var total = cell ? (cell.total || 0) : 0;
                if (!cell || total <= 0) continue;
                var details = formatCellItems(cell.items || [], mode).replace(/\r?\n/g, ' | ');
                var vals = [mode, name, d, String(total), details].map(function (v) {
                    var s = String(v || '');
                    if (s.indexOf('"') >= 0) s = s.replace(/"/g, '""');
                    if (/[,\n"]/.test(s)) s = '"' + s + '"';
                    return s;
                });
                lines.push(vals.join(','));
            }
        }
        var csv = '\ufeff' + lines.join('\n');
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = (currentBatch ? currentBatch : 'schedule') + '-' + mode + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
            try {
                var saved = localStorage.getItem('mes_schedule_current_batch') || '';
                if (saved) {
                    currentBatch = saved;
                    $('#batch-tip').text('当前批次：' + currentBatch);
                }
            } catch (e) {}
            $table.bootstrapTable({
                url: tableUrl(),
                pk: 'id',
                sortName: 'work_date',
                sortOrder: 'asc',
                pagination: true,
                sidePagination: 'server',
                pageSize: 50,
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.list || [] };
                },
                columns: [
                    {checkbox: true},
                    {field: 'work_date', title: '日期', width: 120},
                    {field: 'order_no', title: '订单号', width: 140},
                    {field: 'plan_code', title: '计划', width: 140, formatter: function (v, row) {
                        var pid = row && row.plan_id ? row.plan_id : 0;
                        if (pid) return '<a href="' + planEditUrl + '?ids=' + pid + '">' + (v || ('计划#' + pid)) + '</a>';
                        return v || '-';
                    }},
                    {field: 'model_name', title: '型号', width: 160},
                    {field: 'process_name', title: '工序', width: 140},
                    {field: 'user_name', title: '员工', width: 140},
                    {field: 'quantity', title: '数量(件)', width: 110},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {field: 'batch_id', title: '批次', width: 180, formatter: function (v) {
                        if (!v) return '-';
                        return '<a href="javascript:;" class="js-batch" data-batch="' + v + '">' + v + '</a>';
                    }, events: {
                        'click .js-batch': function (e, value, row) {
                            e.preventDefault();
                            setBatch(row && row.batch_id ? row.batch_id : '', $table);
                        }
                    }}
                ]
            });

            $('#btn-select-plans').off('click').on('click', function () {
                var $planTable = $('#plan-select-table');
                if (!$planTable.data('bootstrap.table')) {
                    $planTable.bootstrapTable({
                        url: base + '/mes/production_plan/index',
                        pk: 'id',
                        pagination: true,
                        sidePagination: 'server',
                        pageSize: 20,
                        pageList: [10, 20, 50, 100],
                        queryParams: function (params) {
                            return Object.assign({}, params, { status: [0,1,3].join(',') });
                        },
                        columns: [
                            { checkbox: true },
                            { field: 'id', title: 'ID', width: 80, sortable: true },
                            { field: 'plan_code', title: '计划编码', align: 'left' },
                            { field: 'plan_name', title: '计划名称', align: 'left' },
                            { field: 'order.order_no', title: '订单号', align: 'left' },
                            { field: 'total_quantity', title: '计划量', width: 100, align: 'right' },
                            { field: 'completed_quantity', title: '已完成', width: 100, align: 'right' },
                            { field: 'status', title: '状态', width: 100 }
                        ],
                        responseHandler: function (res) {
                            return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                        }
                    });
                } else {
                    $planTable.bootstrapTable('refresh');
                }
                $('#plan-select-modal').modal('show');
            });

            $('#btn-plan-select-ok').off('click').on('click', function () {
                var rows = $('#plan-select-table').bootstrapTable('getSelections') || [];
                if (!rows.length) {
                    alert('请先选择要排产的计划');
                    return;
                }
                var ids = rows.map(function (item) { return item.id || 0; }).filter(function (id) { return id > 0; });
                $('#plan-ids').val(ids.join(','));
                $('#plan-select-modal').modal('hide');
            });

            $('#btn-generate').off('click').on('click', function () {
                var startDate = $('#start-date').val() || '';
                var days = parseInt($('#days').val() || '7', 10);
                var reset = $('#reset').is(':checked') ? 1 : 0;
                var enforceUpstream = $('#enforce-upstream').is(':checked') ? 1 : 0;
                var planIds = $('#plan-ids').val() || '';
                $.post(genUrl, { start_date: startDate, days: days, reset: reset, enforce_upstream: enforceUpstream, plan_ids: planIds }, function (r) {
                    if (r.code != 1) {
                        alert(r.msg || '生成失败');
                        return;
                    }
                    var d = r.data || {};
                    setBatch(d.batch_id || '', null);
                    var tip = currentBatch ? ('当前批次：' + currentBatch + '，任务数：' + (d.tasks || 0)) : '';
                    if (d.plan_summary && d.plan_summary.length) {
                        var plans = [];
                        for (var i = 0; i < d.plan_summary.length; i++) {
                            var it = d.plan_summary[i] || {};
                            plans.push((it.plan_code || ('计划#' + (it.plan_id || 0))) + '(' + (it.quantity || 0) + ')');
                        }
                        tip += '，涉及计划：' + plans.join('、');
                    }
                    $('#batch-tip').text(tip);
                    $table.bootstrapTable('refresh', { url: tableUrl() });
                    showView('list');
                    if (d.unscheduled && d.unscheduled.length) {
                        var reasonCnt = {};
                        for (var i = 0; i < d.unscheduled.length; i++) {
                            var rs = (d.unscheduled[i] && d.unscheduled[i].reason) ? d.unscheduled[i].reason : 'unknown';
                            reasonCnt[rs] = (reasonCnt[rs] || 0) + 1;
                        }
                        var parts = [];
                        for (var k in reasonCnt) parts.push(k + ':' + reasonCnt[k]);
                        var sample = d.unscheduled.slice(0, 5).map(function (x) {
                            var pc = x.plan_code || ('计划#' + (x.plan_id || 0));
                            return pc + ' ' + (x.reason || 'unknown') + ' remain=' + (x.remain || 0);
                        });
                        alert('生成完成，但存在未排完的工序/计划（' + parts.join('，') + '）。\n示例：\n' + sample.join('\n') + '\n\n可完善工艺路线/员工产能或增加排产天数。');
                    } else {
                        alert('生成成功');
                    }
                }, 'json');
            });

            $('#btn-publish').off('click').on('click', function () {
                var batch = currentBatch;
                var pending = 0;
                if (!batch) {
                    var sel = getBatchFromSelection($table);
                    if (!sel.ok) return alert(sel.msg);
                    batch = sel.batch;
                    pending = sel.pending || 0;
                }
                if (pending === 0) {
                    var sel2 = getBatchFromSelection($table);
                    if (sel2.ok) pending = sel2.pending || 0;
                }
                if (pending === 0) return alert('该批次没有待下发任务');
                if (!confirm('确定下发批次 ' + batch + ' 的待下发任务为分工？')) return;
                $.post(pubUrl, { batch_id: batch }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已下发' : '失败'));
                    if (r.code == 1) {
                        setBatch(batch, $table);
                    }
                }, 'json');
            });

            $('#btn-revoke').off('click').on('click', function () {
                var batch = currentBatch;
                if (!batch) {
                    var sel = getBatchFromSelection($table);
                    if (!sel.ok) return alert(sel.msg);
                    batch = sel.batch;
                }
                if (!confirm('确定撤销批次 ' + batch + ' 的下发？\n仅允许撤销“未开始且无报工”的分工。')) return;
                $.post(revokeUrl, { batch_id: batch }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已撤销' : '失败'));
                    if (r.code == 1) setBatch(batch, $table);
                }, 'json');
            });

            $('#btn-view-list').off('click').on('click', function () {
                showView('list');
            });
            $('#btn-view-user').off('click').on('click', function () {
                loadGantt('user');
            });
            $('#btn-view-process').off('click').on('click', function () {
                loadGantt('process');
            });
            $('#btn-export-csv').off('click').on('click', function () {
                exportCsv();
            });

            $('#btn-clear-batch').off('click').on('click', function () {
                setBatch('', $table);
            });
        }
    };
    window.__backendController = Controller;
})();
