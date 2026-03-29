/**
 * 员工产能(计件)页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/user_process_capacity/index';
    var addUrl = base + '/mes/user_process_capacity/add';
    var editUrl = base + '/mes/user_process_capacity/edit';
    var delUrl = base + '/mes/user_process_capacity/del';

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
            $table.bootstrapTable({
                url: indexUrl,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                columns: [
                    {checkbox: true},
                    {field: 'id', title: 'ID', width: 80},
                    {field: 'user_name', title: '员工', width: 160},
                    {field: 'process_name', title: '工序', width: 160},
                    {field: 'capacity_per_day', title: '日产能(件)', width: 120},
                    {field: 'status', title: '状态', width: 90, formatter: function (v) {
                        return v == 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-secondary">禁用</span>';
                    }}
                ],
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.list || [] };
                }
            });
            $('#toolbar .btn-refresh').off('click').on('click', function () { $table.bootstrapTable('refresh'); });

            function getSelectedId() {
                var rows = $table.bootstrapTable('getSelections') || [];
                return rows.length ? rows[0].id : null;
            }
            $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
                var id = getSelectedId();
                var enable = !!id;
                $('#toolbar .btn-edit,#toolbar .btn-del').toggleClass('disabled', !enable).toggleClass('btn-disabled', !enable);
            });
            $('#toolbar .btn-edit').off('click').on('click', function () {
                var id = getSelectedId();
                if (!id) return;
                location.href = editUrl + '?ids=' + id;
            });
            $('#toolbar .btn-del').off('click').on('click', function () {
                var id = getSelectedId();
                if (!id) return;
                if (!confirm('确定删除？')) return;
                $.post(delUrl, { ids: id }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已删除' : '失败'));
                    if (r.code == 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                var form = $('form#form-add, form#form-edit');
                if (!form.length) return;
                var action = form.attr('action') || (form.attr('id') === 'form-add' ? addUrl : editUrl);
                form.attr('action', action);
                form.off('submit').on('submit', function (e) {
                    e.preventDefault();
                    var url = $(this).attr('action');
                    if (url.indexOf('?ids=') === -1 && form.attr('id') === 'form-edit') {
                        var id = $('input[name="row[id]"]').val();
                        if (id) url += '?ids=' + id;
                    }
                    $.post(url, $(this).serialize(), function (r) {
                        if (r && r.msg) alert(r.msg);
                        if (r && r.code === 1) location.href = indexUrl;
                    }, 'json').fail(function (xhr) {
                        try {
                            var r = JSON.parse(xhr.responseText);
                            alert(r.msg || '操作失败');
                        } catch (e2) {
                            alert('操作失败');
                        }
                    });
                });
            }
        }
    };
    window.__backendController = Controller;
})();
