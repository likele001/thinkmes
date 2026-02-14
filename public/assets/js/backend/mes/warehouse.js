(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/warehouse/index';
    var addUrl = base + '/mes/warehouse/add';
    var editUrl = base + '/mes/warehouse/edit';
    var delUrl = base + '/mes/warehouse/del';

    function statusFmt(v) {
        return v == 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-danger">禁用</span>';
    }

    function isDefaultFmt(v) {
        return v == 1 ? '<span class="badge badge-info">默认</span>' : '-';
    }

    function operFmt(value, row) {
        return '<a href="' + editUrl + '?ids=' + row.id + '" class="btn btn-xs btn-success btn-edit">编辑</a> ' +
            '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }
            $table.bootstrapTable({
                url: indexUrl,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                columns: [
                    {checkbox: true},
                    {field: 'id', title: 'ID', width: 80, sortable: true},
                    {field: 'name', title: '仓库名称', align: 'left'},
                    {field: 'code', title: '仓库编码', align: 'left'},
                    {field: 'address', title: '仓库地址', align: 'left'},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {field: 'is_default', title: '默认仓库', width: 120, formatter: isDefaultFmt},
                    {field: 'create_time', title: '创建时间', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                    }},
                    {field: 'operate', title: '操作', width: 150, events: {
                        'click .btn-edit': function(e, value, row) {
                            location.href = editUrl + '?ids=' + row.id;
                        },
                        'click .btn-del': function(e, value, row) {
                            if (confirm('确定要删除吗？')) {
                                $.post(delUrl, {ids: row.id}, function(r) {
                                    if (r.code == 1) {
                                        $table.bootstrapTable('refresh');
                                        alert(r.msg || '删除成功');
                                    } else {
                                        alert(r.msg || '删除失败');
                                    }
                                }, 'json');
                            }
                        }
                    }, formatter: operFmt}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });

            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
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
