(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/supplier/index';
    var addUrl = base + '/mes/supplier/add';
    var editUrl = base + '/mes/supplier/edit';
    var delUrl = base + '/mes/supplier/del';

    function statusFmt(v) { return v == 'active' ? '启用' : '禁用'; }

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
                    { field: 'name', title: '供应商名称', width: 150 },
                    { field: 'code', title: '供应商编码', width: 120 },
                    { field: 'contact_person', title: '联系人', width: 120 },
                    { field: 'contact_phone', title: '联系电话', width: 120 },
                    { field: 'address', title: '地址', width: 150 },
                    { field: 'status', title: '状态', width: 100, formatter: statusFmt },
                    { field: 'create_time', title: '创建时间', width: 180, formatter: fmtTime },
                    { field: 'id', title: '操作', width: 150, formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });

            // 刷新按钮
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });

            // 工具栏编辑按钮
            $(document).off('click', '.btn-edit').on('click', '.btn-edit', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length !== 1) {
                    alert('请选择一条记录');
                    return;
                }
                location.href = editUrl + '?ids=' + rows[0].id;
            });

            // 工具栏删除按钮
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

            // 表格内删除按钮
            $(document).off('click', '#table .btn-danger').on('click', '#table .btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除该供应商？')) return;
                $.post(delUrl, { ids: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '删除失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });

            // 表格行选择事件
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
            Controller.api.bindEvent();
        },
        edit: function () {
            Controller.api.bindEvent();
        },
        api: {
            bindEvent: function () {
                $('form#form-add, form#form-edit').off('submit').on('submit', function (e) {
                    e.preventDefault();
                    var form = $(this);
                    var url = form.attr('id') === 'form-add' ? addUrl : editUrl;
                    if (form.attr('id') === 'form-edit') {
                        var id = $('input[name="row[id]"]').val();
                        url += '?ids=' + id;
                    }
                    $.post(url, form.serialize(), function (r) {
                        if (r && r.msg) {
                            alert(r.msg);
                        }
                        if (r && r.code === 1) {
                            location.href = indexUrl;
                        }
                    }, 'json');
                });
            }
        }
    };

    window.__backendController = Controller;
})();
