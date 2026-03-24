(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : (base + '/workflow/index');
    var addUrl = base + '/workflow/add';
    var editUrl = base + '/workflow/edit';
    var delUrl = base + '/workflow/del';
    var instancesUrl = base + '/workflow/instances';
    var startInstanceUrl = base + '/workflow/startInstance';
    var approveUrl = base + '/workflow/approve';
    var rejectUrl = base + '/workflow/reject';
    var withdrawUrl = base + '/workflow/withdraw';
    var instanceDetailUrl = base + '/workflow/instanceDetail';
    var getStatesUrl = base + '/workflow/getStates';
    var getTransitionsUrl = base + '/workflow/getTransitions';

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
                    { field: 'name', title: '工作流名称' },
                    { field: 'table_name', title: '关联表' },
                    { field: 'description', title: '描述' },
                    { field: 'created_time', title: '创建时间' },
                    { field: 'status', title: '状态', formatter: function (value) {
                        return value === 1 ? '<span class="label label-success">启用</span>' : '<span class="label label-default">禁用</span>';
                    }},
                    {
                        field: 'operate',
                        title: '操作',
                        width: 200,
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
                if (ids.length === 0) { alert('请先勾选要编辑的工作流'); return; }
                if (ids.length > 1) { alert('只能编辑一条'); return; }
                location.href = editUrl + '?id=' + ids[0];
            });

            $('#toolbar .btn-del').on('click', function () {
                var ids = getIdSelections();
                if (ids.length === 0) { alert('请先勾选要删除的工作流'); return; }
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
                if (!confirm('确定删除该工作流？')) return;

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

            $('#add-state').on('click', function () {
                var html = $('#state-template').html();
                $('#states-container').append(html);
            });

            $('#add-transition').on('click', function () {
                var html = $('#transition-template').html();
                $('#transitions-container').append(html);
            });

            $(document).on('click', '.btn-remove-state', function () {
                $(this).closest('.state-item').remove();
            });

            $(document).on('click', '.btn-remove-transition', function () {
                $(this).closest('.transition-item').remove();
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

        instances: function () {
            var table = $('#table');

            table.bootstrapTable({
                url: instancesUrl,
                responseHandler: function (res) {
                    return {
                        total: res.count || 0,
                        rows: res.data || []
                    };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'workflow_name', title: '工作流' },
                    { field: 'title', title: '标题' },
                    { field: 'current_state_name', title: '当前状态' },
                    { field: 'created_time', title: '创建时间' },
                    { field: 'status', title: '状态', formatter: function (value) {
                        var statusMap = { 'running': '运行中', 'completed': '已完成', 'rejected': '已驳回', 'withdrawn': '已撤回' };
                        return statusMap[value] || value;
                    }},
                    {
                        field: 'operate',
                        title: '操作',
                        width: 200,
                        formatter: function (value, row) {
                            var html = '<a href="' + instanceDetailUrl + '?id=' + row.id + '" class="btn btn-xs btn-success">详情</a> ';
                            if (row.status === 'running') {
                                html += '<button class="btn btn-xs btn-primary btn-approve" data-id="' + row.id + '">审批</button> ';
                            }
                            return html;
                        }
                    }
                ]
            });

            $(document).on('click', '.btn-approve', function () {
                var instanceId = $(this).data('id');
                location.href = instanceDetailUrl + '?id=' + instanceId;
            });
        },

        instanceDetail: function () {
            $('#btn-approve').on('click', function () {
                var instanceId = $(this).data('instance-id');
                var transitionId = $('select[name="transition_id"]').val();
                var comment = $('textarea[name="comment"]').val();

                if (!transitionId) {
                    alert('请选择审批动作');
                    return;
                }

                $.post(approveUrl, { instance_id: instanceId, transition_id: transitionId, comment: comment }, function (res) {
                    if (res.code === 1) {
                        alert(res.msg);
                        location.href = instancesUrl;
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });

            $('#btn-reject').on('click', function () {
                var instanceId = $(this).data('instance-id');
                var comment = $('textarea[name="comment"]').val();

                if (!comment) {
                    alert('请填写驳回原因');
                    return;
                }

                $.post(rejectUrl, { instance_id: instanceId, comment: comment }, function (res) {
                    if (res.code === 1) {
                        alert(res.msg);
                        location.href = instancesUrl;
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });

            $('#btn-withdraw').on('click', function () {
                var instanceId = $(this).data('instance-id');

                if (!confirm('确定撤回该审批？')) return;

                $.post(withdrawUrl, { instance_id: instanceId }, function (res) {
                    if (res.code === 1) {
                        alert(res.msg);
                        location.href = instancesUrl;
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });
        }
    };

    window.__backendController = Controller;
})();
