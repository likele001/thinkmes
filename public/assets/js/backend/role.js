/**
 * 角色管理：链接从 table_index_url 派生，避免多一层路径
 */
(function () {
    var indexUrl = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : '';
    var base = indexUrl ? indexUrl.replace(/\/index\/?(\?.*)?$/, '') : '';
    var editUrl = base ? base + '/edit' : '';
    var delUrl = base ? base + '/del' : '';

    function statusFmt(v) { return v == 1 ? '正常' : '禁用'; }
    function operFmt(v) {
        return '<a class="btn btn-xs btn-primary" href="' + editUrl + '?id=' + v + '">编辑</a> ' +
            '<button class="btn btn-xs btn-danger" data-id="' + v + '" type="button">删除</button>';
    }
    function rulesFmt(v, row) {
        var names = Array.isArray(row.rules_names) ? row.rules_names.slice() : ((v || '').split(','));
        names = names.filter(function(s){ return s && String(s).trim() !== ''; });
        if (names.length === 0) return '-';
        var limit = 6;
        var shown = names.slice(0, limit);
        var html = shown.map(function(s){ return '<span class="badge badge-secondary mr-1">' + s + '</span>'; }).join('');
        if (names.length > limit) {
            var more = names.slice(limit);
            var all = names.join('，');
            html += ' <a href="javascript:;" class="text-primary btn-show-all" title="查看全部" data-all="' + all.replace(/"/g, '&quot;') + '">更多(' + (names.length - limit) + ')</a>';
        }
        return html;
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            $table.bootstrapTable({
                url: indexUrl,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'name', title: '角色名' },
                    { field: 'rules', title: '规则', formatter: rulesFmt },
                    { field: 'status', title: '状态', formatter: statusFmt },
                    { field: 'id', title: '操作', formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除？')) return;
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
            $(document).off('click', '#table a.btn-show-all').on('click', '#table a.btn-show-all', function () {
                var all = $(this).data('all') || '';
                alert(all);
            });
            // 选择行时启用工具栏编辑/删除
            var updateToolbar = function () {
                var sel = $table.bootstrapTable('getSelections') || [];
                if (sel.length > 0) {
                    $('.btn-edit, .btn-del').removeClass('disabled btn-disabled');
                } else {
                    $('.btn-edit, .btn-del').addClass('disabled btn-disabled');
                }
            };
            $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', updateToolbar);
            $(document).off('click', '#toolbar .btn-edit').on('click', '#toolbar .btn-edit', function () {
                var sel = $table.bootstrapTable('getSelections') || [];
                if (sel.length === 0) return;
                var id = sel[0].id;
                if (id) location.href = editUrl + '?id=' + id;
            });
            $(document).off('click', '#toolbar .btn-del').on('click', '#toolbar .btn-del', function () {
                var sel = $table.bootstrapTable('getSelections') || [];
                if (sel.length === 0) return;
                var id = sel[0].id;
                if (!id || !confirm('确定删除？')) return;
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
