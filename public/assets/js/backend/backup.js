/**
 * 数据备份
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/backup/index';
    var createUrl = base + '/backup/create';
    var delUrl = base + '/backup/del';
    var downloadUrl = base + '/backup/download';

    function tableUrl() {
        return indexUrl;
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
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                },
                columns: [
                    { checkbox: true },
                    { field: 'name', title: '文件名', minWidth: 320, formatter: function (v) {
                        return '<a href="' + downloadUrl + '?name=' + encodeURIComponent(v) + '">' + v + '</a>';
                    }},
                    { field: 'size_text', title: '大小', width: 120 },
                    { field: 'mtime_text', title: '生成时间', width: 180 }
                ]
            });

            $('#btn-refresh').off('click').on('click', function () {
                $table.bootstrapTable('refresh', { url: tableUrl() });
            });

            $('#btn-create').off('click').on('click', function () {
                if (!confirm('生成数据库备份？')) return;
                $.post(createUrl, {}, function (r) {
                    alert(r.msg || (r.code == 1 ? '成功' : '失败'));
                    if (r.code == 1) $table.bootstrapTable('refresh', { url: tableUrl() });
                }, 'json');
            });

            $('#btn-del').off('click').on('click', function () {
                var rows = $table.bootstrapTable('getSelections') || [];
                if (!rows.length) return alert('请选择记录');
                if (rows.length !== 1) return alert('一次只能删除一个文件');
                if (!confirm('确定删除该备份文件？')) return;
                $.post(delUrl, { name: rows[0].name }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已删除' : '失败'));
                    if (r.code == 1) $table.bootstrapTable('refresh', { url: tableUrl() });
                }, 'json');
            });
        }
    };

    window.__backendController = Controller;
})();

