(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : (base + '/auth_rule/index');
    var treeUrl = base + '/auth_rule/tree';
    var addUrl = base + '/auth_rule/add';
    var editUrl = base + '/auth_rule/edit';
    var delUrl = base + '/auth_rule/del';

    function row(items, lev) {
        var h = '';
        (items || []).forEach(function (it) {
            var typeText = ['', '菜单', '按钮', '接口'][it.type] || '';
            var menuText = (it.ismenu == 1) ? '是' : '否';
            var statusText = it.status == 1 ? '<span class="text-success"><i class="fas fa-circle fa-xs"></i> 正常</span>' : '<span class="text-muted">禁用</span>';
            var iconHtml = (it.icon && it.icon.trim()) ? '<i class="' + it.icon.trim() + '"></i>' : '-';
            var pad = lev * 24;
            var prefix = lev > 0 ? '<span class="text-muted">└ </span>' : '';
            h += '<tr data-id="' + it.id + '">';
            h += '<td><input type="checkbox" class="row-check" value="' + it.id + '"></td>';
            h += '<td>' + it.id + '</td>';
            h += '<td style="padding-left:' + pad + 'px">' + prefix + (it.title || '-') + '</td>';
            h += '<td>' + iconHtml + '</td>';
            h += '<td><code>' + (it.name || '') + '</code></td>';
            h += '<td>' + (it.sort !== undefined ? it.sort : 0) + '</td>';
            h += '<td>' + statusText + '</td>';
            h += '<td>' + menuText + '</td>';
            h += '<td>';
            h += '<a class="btn btn-xs btn-success" href="' + addUrl + '?pid=' + it.id + '" title="添加子项"><i class="fas fa-plus"></i></a> ';
            h += '<a class="btn btn-xs btn-primary" href="' + editUrl + '?id=' + it.id + '" title="编辑"><i class="fas fa-edit"></i></a> ';
            h += '<button type="button" class="btn btn-xs btn-danger btn-row-del" data-id="' + it.id + '" title="删除"><i class="fas fa-trash-alt"></i></button>';
            h += '</td></tr>';
            if (it.children && it.children.length) h += row(it.children, lev + 1);
        });
        return h;
    }

    function loadTable() {
        $.get(treeUrl, function (res) {
            if (res.code === 1 && res.data) {
                if (res.data.length === 0) {
                    $('#tree-table tbody').html('<tr><td colspan="9" class="text-center text-muted">暂无数据，请先执行数据库初始化SQL：<code>database/init_auth_rules_complete.sql</code></td></tr>');
                } else {
                    $('#tree-table tbody').html(row(res.data, 0));
                }
            } else {
                console.error('加载权限规则失败:', res);
                $('#tree-table tbody').html('<tr><td colspan="9" class="text-center text-danger">加载失败：' + (res.msg || '未知错误') + '</td></tr>');
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('请求失败:', status, error);
            $('#tree-table tbody').html('<tr><td colspan="9" class="text-center text-danger">请求失败，请检查网络连接</td></tr>');
        });
    }

    var Controller = {
        index: function () {
            loadTable();

            $('#check-all').on('change', function () {
                $('#tree-table .row-check').prop('checked', $(this).prop('checked'));
            });

            $('#toolbar .btn-refresh').on('click', function () {
                loadTable();
            });

            $('#toolbar .btn-edit').on('click', function () {
                var ids = [];
                $('#tree-table .row-check:checked').each(function () { ids.push($(this).val()); });
                if (ids.length === 0) { alert('请先勾选要编辑的规则'); return; }
                if (ids.length > 1) { alert('只能编辑一条'); return; }
                location.href = editUrl + '?id=' + ids[0];
            });

            $('#toolbar .btn-del').on('click', function () {
                var ids = [];
                $('#tree-table .row-check:checked').each(function () { ids.push($(this).val()); });
                if (ids.length === 0) { alert('请先勾选要删除的规则'); return; }
                if (!confirm('确定删除所选 ' + ids.length + ' 条规则？')) return;
                (function next(i) {
                    if (i >= ids.length) { loadTable(); return; }
                    $.post(delUrl, { id: ids[i] }, function (r) {
                        if (r.code !== 1) alert(r.msg);
                        next(i + 1);
                    }, 'json').fail(function () { next(i + 1); });
                })(0);
            });

            $(document).on('click', '#tree-table .btn-row-del', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除该规则？')) return;
                var $tr = $(this).closest('tr');
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) loadTable();
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
