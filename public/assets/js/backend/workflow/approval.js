/**
 * 工作流-审批中心
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var pendingUrl = base + '/workflow/approval/pending';
    var doneUrl = base + '/workflow/approval/done';
    var mineUrl = base + '/workflow/approval/mine';
    var detailUrl = base + '/workflow/approval/detail';
    var approveUrl = base + '/workflow/approval/doApprove';
    var rejectUrl = base + '/workflow/approval/doReject';
    var transferUrl = base + '/workflow/approval/doTransfer';
    var withdrawUrl = base + '/workflow/approval/doWithdraw';
    var adminOptionsUrl = base + '/workflow/approval/adminOptions';
    var statsUrl = base + '/workflow/approval/stats';

    function timeText(value) {
        if (!value) return '';
        return new Date(value * 1000).toLocaleString('zh-CN');
    }

    function openDetail(instanceId) {
        if (typeof layer !== 'undefined') {
            layer.open({
                type: 2,
                title: '审批详情',
                area: ['900px', '80%'],
                content: detailUrl + '?id=' + instanceId
            });
        } else {
            location.href = detailUrl + '?id=' + instanceId;
        }
    }

    function loadStats() {
        $.get(statsUrl, function (res) {
            if (!res || res.code !== 1) return;
            var data = res.data || {};
            $('#pending-count').text(data.pending || 0);
            $('#done-count').text(data.done || 0);
            $('#mine-count').text(data.mine || 0);
            $('#avg-time').text(data.avg_time || '-');
        }, 'json');
    }

    function statusBadge(status, type) {
        var mapMine = {
            0: '<span class="badge badge-primary">审批中</span>',
            1: '<span class="badge badge-success">已通过</span>',
            2: '<span class="badge badge-danger">已拒绝</span>',
            3: '<span class="badge badge-secondary">已撤回</span>',
            4: '<span class="badge badge-warning">回写异常</span>'
        };
        var mapDone = {
            0: '<span class="badge badge-primary">待审批</span>',
            1: '<span class="badge badge-success">已通过</span>',
            2: '<span class="badge badge-danger">已拒绝</span>',
            3: '<span class="badge badge-secondary">已取消</span>',
            4: '<span class="badge badge-info">已转交</span>'
        };
        return (type === 'mine' ? mapMine : mapDone)[status] || status;
    }

    var Controller = {
        index: function () {
            var pendingTable = $('#pending-table');
            var doneTable = $('#done-table');
            var mineTable = $('#mine-table');

            if (!pendingTable.length) return;

            loadStats();

            pendingTable.bootstrapTable({
                url: pendingUrl,
                pagination: true,
                sidePagination: 'server',
                responseHandler: function (res) {
                    var data = res.data || {};
                    return { total: data.total || 0, rows: data.list || [] };
                },
                columns: [
                    { field: 'instance_no', title: '实例编号', width: 150 },
                    { field: 'business_title', title: '业务标题' },
                    { field: 'node_sort', title: '节点序号', width: 90 },
                    { field: 'approval_mode', title: '审批方式', width: 100, formatter: function (value) { return value === 'countersign' ? '会签' : '或签'; } },
                    { field: 'initiator_name', title: '发起人', width: 100 },
                    { field: 'create_time', title: '发起时间', width: 170, formatter: timeText },
                    { field: 'operate', title: '操作', width: 230, formatter: function (value, row) {
                        return [
                            '<a href="javascript:;" class="btn btn-xs btn-success btn-approve" data-id="' + row.id + '">通过</a>',
                            '<a href="javascript:;" class="btn btn-xs btn-danger btn-reject" data-id="' + row.id + '">拒绝</a>',
                            '<a href="javascript:;" class="btn btn-xs btn-warning btn-transfer" data-id="' + row.id + '">转交</a>',
                            '<a href="javascript:;" class="btn btn-xs btn-info btn-detail" data-instance="' + row.instance_id + '">详情</a>'
                        ].join(' ');
                    }}
                ]
            });

            doneTable.bootstrapTable({
                url: doneUrl,
                pagination: true,
                sidePagination: 'server',
                responseHandler: function (res) {
                    var data = res.data || {};
                    return { total: data.total || 0, rows: data.list || [] };
                },
                columns: [
                    { field: 'instance_no', title: '实例编号', width: 150 },
                    { field: 'business_title', title: '业务标题' },
                    { field: 'node_sort', title: '节点序号', width: 90 },
                    { field: 'status', title: '状态', width: 100, formatter: function (value) { return statusBadge(value, 'done'); } },
                    { field: 'comment', title: '审批意见' },
                    { field: 'action_time', title: '操作时间', width: 170, formatter: timeText },
                    { field: 'operate', title: '操作', width: 90, formatter: function (value, row) {
                        return '<a href="javascript:;" class="btn btn-xs btn-info btn-detail" data-instance="' + row.instance_id + '">详情</a>';
                    }}
                ]
            });

            mineTable.bootstrapTable({
                url: mineUrl,
                pagination: true,
                sidePagination: 'server',
                responseHandler: function (res) {
                    var data = res.data || {};
                    return { total: data.total || 0, rows: data.list || [] };
                },
                columns: [
                    { field: 'instance_no', title: '实例编号', width: 160 },
                    { field: 'business_title', title: '业务标题' },
                    { field: 'current_sort', title: '当前节点', width: 100 },
                    { field: 'status', title: '状态', width: 100, formatter: function (value) { return statusBadge(value, 'mine'); } },
                    { field: 'initiator_name', title: '发起人', width: 100 },
                    { field: 'start_time', title: '发起时间', width: 170, formatter: timeText },
                    { field: 'operate', title: '操作', width: 150, formatter: function (value, row) {
                        var html = '<a href="javascript:;" class="btn btn-xs btn-info btn-detail" data-instance="' + row.id + '">详情</a>';
                        if (row.status === 0 && row.current_sort === 1) {
                            html += ' <a href="javascript:;" class="btn btn-xs btn-warning btn-withdraw" data-id="' + row.id + '">撤回</a>';
                        }
                        return html;
                    }}
                ]
            });

            $('#approval-toolbar .btn-refresh-all').off('click').on('click', function () {
                pendingTable.bootstrapTable('refresh');
                doneTable.bootstrapTable('refresh');
                mineTable.bootstrapTable('refresh');
                loadStats();
            });

            $('button[data-bs-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function (e) {
                var target = $(e.target).attr('data-bs-target');
                if (target === '#pending-tab') pendingTable.bootstrapTable('resetView');
                if (target === '#done-tab') doneTable.bootstrapTable('resetView');
                if (target === '#mine-tab') mineTable.bootstrapTable('resetView');
            });

            $(document).off('click.workflow.approval');
            $(document).on('click.workflow.approval', '.btn-detail', function () {
                openDetail($(this).data('instance'));
            });
            $(document).on('click.workflow.approval', '.btn-approve', function () {
                var id = $(this).data('id');
                layer.prompt({ title: '请填写审批意见（可留空）', formType: 2 }, function (value, idx) {
                    $.post(approveUrl, { task_id: id, comment: value || '' }, function (res) {
                        if (res.code === 1) {
                            Toastr.success(res.msg || '操作成功');
                            layer.close(idx);
                            pendingTable.bootstrapTable('refresh');
                            doneTable.bootstrapTable('refresh');
                            loadStats();
                        } else {
                            Toastr.error(res.msg || '操作失败');
                        }
                    }, 'json').fail(function () {
                        Toastr.error('操作失败');
                    });
                });
            });
            $(document).on('click.workflow.approval', '.btn-reject', function () {
                var id = $(this).data('id');
                layer.prompt({ title: '请输入拒绝原因（必填）', formType: 2 }, function (value, idx) {
                    if (!value || !String(value).trim()) {
                        Toastr.warning('拒绝原因不能为空');
                        return;
                    }
                    $.post(rejectUrl, { task_id: id, comment: value }, function (res) {
                        if (res.code === 1) {
                            Toastr.success(res.msg || '操作成功');
                            layer.close(idx);
                            pendingTable.bootstrapTable('refresh');
                            doneTable.bootstrapTable('refresh');
                            loadStats();
                        } else {
                            Toastr.error(res.msg || '操作失败');
                        }
                    }, 'json').fail(function () {
                        Toastr.error('操作失败');
                    });
                });
            });
            $(document).on('click.workflow.approval', '.btn-transfer', function () {
                var id = $(this).data('id');
                $.get(adminOptionsUrl, function (res) {
                    var list = (res.data && res.data.list) || [];
                    var options = ['<option value="">请选择管理员</option>'];
                    list.forEach(function (item) {
                        options.push('<option value="' + item.id + '">' + item.name + '</option>');
                    });
                    var html = '<div style="padding:16px 20px;">' +
                        '<div class="form-group"><label>转交给</label><select id="transfer-to-admin" class="form-control">' + options.join('') + '</select></div>' +
                        '<div class="form-group"><label>备注</label><textarea id="transfer-comment" class="form-control" rows="3" placeholder="请填写转交备注（可留空）"></textarea></div>' +
                        '</div>';
                    layer.open({
                        type: 1,
                        title: '转交审批',
                        area: ['520px', '320px'],
                        content: html,
                        btn: ['确定', '取消'],
                        yes: function (idx) {
                            var toAdminId = $('#transfer-to-admin').val();
                            var comment = $('#transfer-comment').val() || '';
                            if (!toAdminId) {
                                Toastr.warning('请选择转交对象');
                                return;
                            }
                            $.post(transferUrl, { task_id: id, to_admin_id: toAdminId, comment: comment }, function (saveRes) {
                                if (saveRes.code === 1) {
                                    Toastr.success(saveRes.msg || '操作成功');
                                    layer.close(idx);
                                    pendingTable.bootstrapTable('refresh');
                                    doneTable.bootstrapTable('refresh');
                                    loadStats();
                                } else {
                                    Toastr.error(saveRes.msg || '操作失败');
                                }
                            }, 'json').fail(function () {
                                Toastr.error('操作失败');
                            });
                        }
                    });
                }, 'json');
            });
            $(document).on('click.workflow.approval', '.btn-withdraw', function () {
                var id = $(this).data('id');
                if (!confirm('确定要撤回此申请吗？')) return;
                $.post(withdrawUrl, { instance_id: id, comment: '主动撤回' }, function (res) {
                    if (res.code === 1) {
                        Toastr.success(res.msg || '撤回成功');
                        mineTable.bootstrapTable('refresh');
                        loadStats();
                    } else {
                        Toastr.error(res.msg || '撤回失败');
                    }
                }, 'json').fail(function () {
                    Toastr.error('撤回失败');
                });
            });
        },
        pending: function () {},
        done: function () {},
        mine: function () {},
        detail: function () {},
        doapprove: function () {},
        doreject: function () {},
        dotransfer: function () {},
        dowithdraw: function () {},
        adminoptions: function () {},
        stats: function () {}
    };

    window.__backendController = Controller;
})();
