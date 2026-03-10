(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/material_category/index';
    var addUrl = base + '/mes/material_category/add';
    var editUrl = base + '/mes/material_category/edit';
    var delUrl = base + '/mes/material_category/del';

    function statusFmt(v) { return v == 1 ? '启用' : '禁用'; }

    function fmtTime(v) {
        if (v === null || v === undefined || v === '') return '';
        var n = Number(v);
        if (!isNaN(n) && isFinite(n)) {
            return new Date((n > 1e12 ? n : n * 1000)).toLocaleString('zh-CN');
        }
        var s = String(v).trim();
        var d = new Date(s.replace(' ', 'T'));
        return isNaN(d.getTime()) ? s : d.toLocaleString('zh-CN');
    }
    function operFmt(v) {
        return '<a class="btn btn-xs btn-primary" href="' + editUrl + '?ids=' + v + '">编辑</a> ' +
            '<a href="javascript:;" class="btn btn-xs btn-danger" data-id="' + v + '">删除</a>';
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }
            $table.bootstrapTable({
                url: indexUrl,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'name', title: '分类名称', width: 150 },
                    { field: 'code', title: '分类编码', width: 120 },
                    { field: 'description', title: '描述', width: 200 },
                    { field: 'sort', title: '排序', width: 80 },
                    { field: 'status', title: '状态', width: 100, formatter: statusFmt },
                    { field: 'create_time', title: '创建时间', width: 180, formatter: fmtTime },
                    { field: 'id', title: '操作', width: 150, formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });

            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });

            $(document).off('click', '.btn-edit').on('click', '.btn-edit', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length !== 1) {
                    alert('请选择一条记录');
                    return;
                }
                location.href = editUrl + '?ids=' + rows[0].id;
            });

            $(document).off('click', '.btn-del').on('click', '.btn-del', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length === 0) {
                    alert('请选择要删除的记录');
                    return;
                }
                if (!confirm('确定要删除选中的 ' + rows.length + ' 条记录吗？')) {
                    return;
                }
                var ids = rows.map(function (r) { return r.id; });
                $.post(delUrl, { ids: ids.join(',') }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '删除失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });

            $(document).off('click', '#table .btn-danger').on('click', '#table .btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除该分类？')) return;
                $.post(delUrl, { ids: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '删除失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });

            $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length > 0) {
                    $('.btn-edit, .btn-del').removeClass('disabled btn-disabled');
                } else {
                    $('.btn-edit, .btn-del').addClass('disabled btn-disabled');
                }
            });
        },
        add: function () {
            if (window.__backendController && window.__backendController.api && window.__backendController.api.bindEvent) {
                window.__backendController.api.bindEvent();
            }
        },
        edit: function () {
            if (window.__backendController && window.__backendController.api && window.__backendController.api.bindEvent) {
                window.__backendController.api.bindEvent();
            }
        },
        api: {
            bindEvent: function () {
                $('form#form-add, form#form-edit').off('submit').on('submit', function (e) {
                    e.preventDefault();
                    var form = $(this);
                    var url = form.attr('id') === 'form-add' ? addUrl : editUrl;
                    if (form.attr('id') === 'form-edit') {
                        var ids = (typeof Config !== 'undefined' && Config.ids) ? Config.ids : (window.location.href.match(/[?&]ids=([^&]+)/) || [])[1];
                        if (ids) url += '?ids=' + encodeURIComponent(ids);
                    }
                    $.post(url, form.serialize(), function (r) {
                        if (r && r.msg) alert(r.msg);
                        if (r && r.code === 1) location.href = indexUrl;
                    }, 'json');
                });
            }
        }
    };

    window.__backendController = Controller;
})();
