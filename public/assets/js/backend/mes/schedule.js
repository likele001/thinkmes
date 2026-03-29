/**
 * 智能排产(计件)页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/schedule/index';
    var genUrl = base + '/mes/schedule/generate';
    var pubUrl = base + '/mes/schedule/publish';

    var currentBatch = '';

    function statusFmt(v) {
        var map = {0: ['待下发', 'secondary'], 1: ['已下发', 'success'], 2: ['已完成', 'primary'], 3: ['已撤销', 'danger']};
        var it = map[v] || ['未知', 'secondary'];
        return '<span class="badge badge-' + it[1] + '">' + it[0] + '</span>';
    }

    function tableUrl() {
        var url = indexUrl;
        if (currentBatch) url += (url.indexOf('?') === -1 ? '?' : '&') + 'batch_id=' + encodeURIComponent(currentBatch);
        return url;
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
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
                    {field: 'plan_code', title: '计划', width: 140},
                    {field: 'model_name', title: '型号', width: 160},
                    {field: 'process_name', title: '工序', width: 140},
                    {field: 'user_name', title: '员工', width: 140},
                    {field: 'quantity', title: '数量(件)', width: 110},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {field: 'batch_id', title: '批次', width: 170}
                ]
            });

            $('#btn-generate').off('click').on('click', function () {
                var startDate = $('#start-date').val() || '';
                var days = parseInt($('#days').val() || '7', 10);
                var reset = $('#reset').is(':checked') ? 1 : 0;
                $.post(genUrl, { start_date: startDate, days: days, reset: reset }, function (r) {
                    if (r.code != 1) {
                        alert(r.msg || '生成失败');
                        return;
                    }
                    var d = r.data || {};
                    currentBatch = d.batch_id || '';
                    $('#batch-tip').text(currentBatch ? ('当前批次：' + currentBatch + '，任务数：' + (d.tasks || 0)) : '');
                    $table.bootstrapTable('refresh', { url: tableUrl() });
                    if (d.unscheduled && d.unscheduled.length) {
                        alert('生成完成，但存在未排完的工序/计划，请完善员工产能或增加排产天数。');
                    } else {
                        alert('生成成功');
                    }
                }, 'json');
            });

            $('#btn-publish').off('click').on('click', function () {
                if (!currentBatch) {
                    alert('请先生成排产');
                    return;
                }
                if (!confirm('确定一键下发为分工任务？')) return;
                $.post(pubUrl, { batch_id: currentBatch }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已下发' : '失败'));
                    if (r.code == 1) $table.bootstrapTable('refresh', { url: tableUrl() });
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
