/**
 * 工艺路线管理页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/process_route/index';
    var addUrl = base + '/mes/process_route/add';
    var editUrl = base + '/mes/process_route/edit';
    var delUrl = base + '/mes/process_route/del';

    function statusFmt(value) {
        var map = {
            0: {text: '草稿', cls: 'badge-secondary'},
            1: {text: '审核中', cls: 'badge-info'},
            2: {text: '已发布', cls: 'badge-success'},
            3: {text: '已归档', cls: 'badge-dark'}
        };
        var item = map[value] || {text: value, cls: 'badge-secondary'};
        return '<span class="badge ' + item.cls + '">' + item.text + '</span>';
    }

    function yesNoFmt(value) {
        return value ? '<span class="badge badge-success">是</span>' : '<span class="badge badge-secondary">否</span>';
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }

            var $form = $('#route-search-form');

            $table.bootstrapTable({
                url: indexUrl,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                search: false,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                queryParams: function (params) {
                    var formData = $form.serializeArray();
                    formData.forEach(function (item) {
                        params[item.name] = item.value;
                    });
                    return params;
                },
                toolbar: '#toolbar',
                columns: [
                    {checkbox: true},
                    {field: 'id', title: 'ID', sortable: true, width: 80},
                    {field: 'route_name', title: '路线名称', align: 'left'},
                    {field: 'route_code', title: '路线编码', align: 'left', width: 140},
                    {field: 'model.full_name', title: '产品型号', align: 'left'},
                    {field: 'route_type', title: '路线类型', width: 100},
                    {field: 'is_default', title: '默认', width: 80, formatter: yesNoFmt},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {
                        field: 'create_time',
                        title: '创建时间',
                        width: 180,
                        formatter: function (value) {
                            return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                        }
                    },
                    {
                        field: 'operate',
                        title: '操作',
                        width: 150,
                        formatter: function () {
                            return [
                                '<a href="javascript:;" class="btn btn-xs btn-success btn-edit-single"><i class="fa fa-edit"></i> 编辑</a>'
                            ].join(' ');
                        },
                        events: {
                            'click .btn-edit-single': function (e, value, row) {
                                location.href = editUrl + '?ids=' + row.id;
                            }
                        }
                    }
                ]
            });

            $form.on('submit', function (e) {
                e.preventDefault();
                $table.bootstrapTable('refresh', {pageNumber: 1});
            });

            $('.btn-refresh').on('click', function () {
                $table.bootstrapTable('refresh');
            });

            $('.btn-add').on('click', function () {
                location.href = addUrl;
            });

            $('.btn-edit').on('click', function () {
                var selections = $table.bootstrapTable('getSelections') || [];
                if (!selections.length) {
                    return;
                }
                var row = selections[0];
                location.href = editUrl + '?ids=' + row.id;
            });

            $('.btn-del').on('click', function () {
                var selections = $table.bootstrapTable('getSelections') || [];
                if (!selections.length) {
                    return;
                }
                if (!confirm('确定要删除选中的工艺路线吗？')) {
                    return;
                }
                var ids = selections.map(function (row) {
                    return row.id;
                });
                $.post(delUrl, {ids: ids.join(',')}, function (res) {
                    if (res && res.code === 1) {
                        $table.bootstrapTable('refresh');
                    } else if (res && res.msg) {
                        alert(res.msg);
                    } else {
                        alert('删除失败');
                    }
                }, 'json');
            });
        },
        add: function () {
            $('#add-form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                $.post('', $form.serialize(), function (res) {
                    if (res && res.code === 1) {
                        if (typeof Fast !== 'undefined' && Fast.api && Fast.api.close) {
                            Fast.api.close();
                        } else {
                            history.back();
                        }
                    } else if (res && res.msg) {
                        alert(res.msg);
                    } else {
                        alert('保存失败');
                    }
                }, 'json');
            });
        },
        edit: function () {
            $('#edit-form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                $.post('', $form.serialize(), function (res) {
                    if (res && res.code === 1) {
                        if (typeof Fast !== 'undefined' && Fast.api && Fast.api.close) {
                            Fast.api.close();
                        } else {
                            history.back();
                        }
                    } else if (res && res.msg) {
                        alert(res.msg);
                    } else {
                        alert('保存失败');
                    }
                }, 'json');
            });
        }
    };

    window.__backendController = Controller;
})();

