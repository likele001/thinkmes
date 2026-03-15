(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            var today = new Date();
            var dateStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
            $('#report-date').val(dateStr);

            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/ai/daily_report/index',
                method: 'get',
                pagination: false,
                sidePagination: 'client',
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.list || [] };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'report_type', title: '类型', width: 80, formatter: function (v) {
                        return v === 'weekly' ? '周报' : '日报';
                    }},
                    { field: 'report_date', title: '日期', width: 120 },
                    { field: 'summary', title: '摘要', align: 'left' },
                    { field: 'create_time', title: '生成时间', width: 160, formatter: function (v) {
                        return v ? new Date((v > 1e10 ? v : v * 1000)).toLocaleString('zh-CN') : '';
                    }},
                    { field: 'id', title: '操作', width: 90, formatter: function (v) {
                        return '<button type="button" class="btn btn-xs btn-info btn-view-report" data-id="' + v + '">查看全文</button>';
                    }}
                ]
            });

            $(document).off('click', '#btn-gen-daily').on('click', '#btn-gen-daily', function () {
                var date = $('#report-date').val();
                if (!date) { alert('请选择日期'); return; }
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> 生成中…');
                $.post(base + '/ai/daily_report/generate', { type: 'daily', date: date }, function (r) {
                    $('#btn-gen-daily').prop('disabled', false).html('<i class="fas fa-calendar-day"></i> 生成日报');
                    if (r && r.code === 1) {
                        table.bootstrapTable('refresh');
                        if (r.data && r.data.content) {
                            $('#content-modal-body').text(r.data.content);
                            $('#content-modal').modal('show');
                        } else {
                            alert(r.msg || '生成成功');
                        }
                    } else {
                        alert(r && r.msg ? r.msg : '生成失败');
                    }
                }, 'json').fail(function () {
                    $('#btn-gen-daily').prop('disabled', false).html('<i class="fas fa-calendar-day"></i> 生成日报');
                    alert('请求失败');
                });
            });

            $(document).off('click', '#btn-gen-weekly').on('click', '#btn-gen-weekly', function () {
                var date = $('#report-date').val();
                if (!date) { alert('请选择日期'); return; }
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> 生成中…');
                $.post(base + '/ai/daily_report/generate', { type: 'weekly', date: date }, function (r) {
                    $('#btn-gen-weekly').prop('disabled', false).html('<i class="fas fa-calendar-week"></i> 生成周报');
                    if (r && r.code === 1) {
                        table.bootstrapTable('refresh');
                        if (r.data && r.data.content) {
                            $('#content-modal-body').text(r.data.content);
                            $('#content-modal').modal('show');
                        } else {
                            alert(r.msg || '生成成功');
                        }
                    } else {
                        alert(r && r.msg ? r.msg : '生成失败');
                    }
                }, 'json').fail(function () {
                    $('#btn-gen-weekly').prop('disabled', false).html('<i class="fas fa-calendar-week"></i> 生成周报');
                    alert('请求失败');
                });
            });

            $(document).off('click', '.btn-view-report').on('click', '.btn-view-report', function () {
                var id = $(this).data('id');
                if (!id) return;
                $.get(base + '/ai/daily_report/getReport', { id: id }, function (r) {
                    if (r && r.code === 1 && r.data) {
                        $('#content-modal-body').text(r.data.content || '');
                        $('#content-modal').modal('show');
                    } else {
                        alert(r && r.msg ? r.msg : '获取失败');
                    }
                }, 'json');
            });

            // 显式关闭弹窗，避免在 iframe 内 data-dismiss 无效导致关不掉
            $(document).off('click', '#content-modal-btn-close-x, #content-modal-btn-close').on('click', '#content-modal-btn-close-x, #content-modal-btn-close', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $('#content-modal').modal('hide');
            });
        }
    };
    window.__backendController = Controller;
})();
