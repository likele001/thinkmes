(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : (base + '/custom_field/index');
    var addUrl = base + '/custom_field/add';
    var editUrl = base + '/custom_field/edit';
    var delUrl = base + '/custom_field/del';
    var groupsUrl = base + '/custom_field/groups';
    var addGroupUrl = base + '/custom_field/addGroup';
    var editGroupUrl = base + '/custom_field/editGroup';
    var delGroupUrl = base + '/custom_field/delGroup';

    var Controller = {
        index: function () {
            var table = $('#table');

            table.bootstrapTable({
                url: indexUrl,
                responseHandler: function (res) {
                    return {
                        total: res.count || 0,
                        rows: res.data || []
                    };
                },
                columns: [
                    { checkbox: true },
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'title', title: '字段标题' },
                    { field: 'name', title: '字段名称' },
                    { field: 'type', title: '字段类型' },
                    { field: 'group_name', title: '所属分组' },
                    { field: 'sort', title: '排序', width: 80 },
                    { field: 'status', title: '状态', formatter: function (value) {
                        return value === 1 ? '<span class="label label-success">启用</span>' : '<span class="label label-default">禁用</span>';
                    }},
                    {
                        field: 'operate',
                        title: '操作',
                        width: 180,
                        formatter: function (value, row) {
                            return '<a href="' + editUrl + '?id=' + row.id + '" class="btn btn-xs btn-primary">编辑</a> ' +
                                   '<button class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</button>';
                        }
                    }
                ]
            });

            $('#toolbar .btn-add').on('click', function () {
                location.href = addUrl;
            });

            $('#toolbar .btn-edit').on('click', function () {
                var ids = getIdSelections();
                if (ids.length === 0) { alert('请先勾选要编辑的字段'); return; }
                if (ids.length > 1) { alert('只能编辑一条'); return; }
                location.href = editUrl + '?id=' + ids[0];
            });

            $('#toolbar .btn-del').on('click', function () {
                var ids = getIdSelections();
                if (ids.length === 0) { alert('请先勾选要删除的字段'); return; }
                if (!confirm('确定删除所选 ' + ids.length + ' 条记录？')) return;

                $.post(delUrl, { ids: ids.join(',') }, function (res) {
                    if (res.code === 1) {
                        table.bootstrapTable('refresh');
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });

            $(document).on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除该字段？')) return;

                $.post(delUrl, { ids: id }, function (res) {
                    if (res.code === 1) {
                        table.bootstrapTable('refresh');
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });

            function getIdSelections() {
                return $.map(table.bootstrapTable('getSelections'), function (row) {
                    return row.id;
                });
            }
        },

        add: function () {
            $('#form-add').on('submit', function (e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.post(addUrl, formData, function (res) {
                    if (res.code === 1) {
                        alert(res.msg);
                        location.href = indexUrl;
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });
        },

        edit: function () {
            $('#form-edit').on('submit', function (e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.post(editUrl, formData, function (res) {
                    if (res.code === 1) {
                        alert(res.msg);
                        location.href = indexUrl;
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });
        },

        groups: function () {
            var table = $('#table');

            table.bootstrapTable({
                url: groupsUrl,
                responseHandler: function (res) {
                    return {
                        total: res.count || 0,
                        rows: res.data || []
                    };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'name', title: '分组名称' },
                    { field: 'table_name', title: '关联表' },
                    { field: 'sort', title: '排序', width: 80 },
                    { field: 'status', title: '状态', formatter: function (value) {
                        return value === 1 ? '<span class="label label-success">启用</span>' : '<span class="label label-default">禁用</span>';
                    }},
                    {
                        field: 'operate',
                        title: '操作',
                        width: 180,
                        formatter: function (value, row) {
                            return '<a href="' + editGroupUrl + '?id=' + row.id + '" class="btn btn-xs btn-primary">编辑</a> ' +
                                   '<button class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</button>';
                        }
                    }
                ]
            });

            $('#toolbar .btn-add-group').on('click', function () {
                location.href = addGroupUrl;
            });

            $('#toolbar .btn-back').on('click', function () {
                location.href = indexUrl;
            });

            $(document).on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除该分组？')) return;

                $.post(delGroupUrl, { ids: id }, function (res) {
                    if (res.code === 1) {
                        table.bootstrapTable('refresh');
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });
        },

        addGroup: function () {
            $('#form-add').on('submit', function (e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.post(addGroupUrl, formData, function (res) {
                    if (res.code === 1) {
                        alert(res.msg);
                        location.href = groupsUrl;
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });
        },

        editGroup: function () {
            $('#form-edit').on('submit', function (e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.post(editGroupUrl, formData, function (res) {
                    if (res.code === 1) {
                        alert(res.msg);
                        location.href = groupsUrl;
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });
        }
    };

    window.__backendController = Controller;
})();
