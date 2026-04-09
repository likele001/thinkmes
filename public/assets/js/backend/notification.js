/**
 * 消息通知
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/notification/index';
    var readUrl = base + '/notification/read';
    var delUrl = base + '/notification/del';
    var testUrl = base + '/notification/pushTest';

    function tableUrl() {
        var url = indexUrl;
        var params = [];
        var isRead = $('#filter-read').val();
        var level = $('#filter-level').val();
        var kw = $('#filter-kw').val();
        if (isRead !== '') params.push('is_read=' + encodeURIComponent(isRead));
        if (level) params.push('level=' + encodeURIComponent(level));
        if (kw) params.push('kw=' + encodeURIComponent(kw));
        if (params.length) url += (url.indexOf('?') === -1 ? '?' : '&') + params.join('&');
        return url;
    }

    function levelFmt(v) {
        if (!v) return '-';
        if (v === 'error') return '<span class="badge badge-danger">error</span>';
        if (v === 'warning') return '<span class="badge badge-warning">warning</span>';
        return '<span class="badge badge-info">info</span>';
    }

    function readFmt(v) {
        return String(v) === '1' ? '<span class="badge badge-success">已读</span>' : '<span class="badge badge-secondary">未读</span>';
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
            $table.bootstrapTable({
                url: tableUrl(),
                toolbar: '#toolbar',
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                queryParams: function (params) {
                    params.is_read = $('#filter-read').val();
                    params.level = $('#filter-level').val();
                    params.kw = $('#filter-kw').val();
                    return params;
                },
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                },
                columns: [
                    { checkbox: true },
                    { field: 'id', title: 'ID', width: 80 },
                    { field: 'level', title: '级别', width: 100, formatter: levelFmt },
                    { field: 'title', title: '标题', minWidth: 260 },
                    { field: 'is_read', title: '状态', width: 100, formatter: readFmt },
                    { field: 'create_time_text', title: '创建时间', width: 180 },
                    { field: 'read_time_text', title: '已读时间', width: 180 }
                ]
            });

            $('#btn-refresh').off('click').on('click', function () { $table.bootstrapTable('refresh', { url: tableUrl() }); });
            $('#btn-search').off('click').on('click', function () { $table.bootstrapTable('refresh', { url: tableUrl() }); });

            $('#btn-read').off('click').on('click', function () {
                var rows = $table.bootstrapTable('getSelections') || [];
                if (!rows.length) return alert('请选择记录');
                var ids = rows.map(function (r) { return r.id; });
                $.post(readUrl, { ids: ids }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已标记' : '失败'));
                    if (r.code == 1) $table.bootstrapTable('refresh', { url: tableUrl() });
                }, 'json');
            });

            $('#btn-del').off('click').on('click', function () {
                var rows = $table.bootstrapTable('getSelections') || [];
                if (!rows.length) return alert('请选择记录');
                if (!confirm('确定删除选中通知？')) return;
                var ids = rows.map(function (r) { return r.id; });
                $.post(delUrl, { ids: ids }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已删除' : '失败'));
                    if (r.code == 1) $table.bootstrapTable('refresh', { url: tableUrl() });
                }, 'json');
            });

            $('#btn-test').off('click').on('click', function () {
                var title = prompt('标题', '测试通知');
                if (title === null) return;
                var content = prompt('内容', '这是一条测试通知');
                if (content === null) content = '';
                $.post(testUrl, { title: title, content: content, level: 'info' }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已发送' : '失败'));
                    if (r.code == 1) $table.bootstrapTable('refresh', { url: tableUrl() });
                }, 'json');
            });
        }
    };

    window.__backendController = Controller;
})();

