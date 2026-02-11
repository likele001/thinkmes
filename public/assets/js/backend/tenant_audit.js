(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/tenant_audit/index';
    var approveUrl = base + '/tenant_audit/approve';
    var rejectUrl = base + '/tenant_audit/reject';
    var delUrl = base + '/tenant_audit/del';
    var queryUrl = base + '/tenant_register/query';

    function statusFmt(v) {
        var statusMap = {
            0: '<span class="badge badge-warning">待审核</span>',
            1: '<span class="badge badge-success">已通过</span>',
            2: '<span class="badge badge-danger">已拒绝</span>'
        };
        return statusMap[v] || '未知';
    }

    function operFmt(value, row) {
        var html = '';
        if (row.status === 0) {
            html += '<button class="btn btn-xs btn-success btn-approve" data-id="' + row.id + '" data-company="' + row.company_name + '">通过</button> ';
            html += '<button class="btn btn-xs btn-danger btn-reject" data-id="' + row.id + '">拒绝</button> ';
        }
        html += '<button class="btn btn-xs btn-default btn-query" data-no="' + row.register_no + '">查询</button> ';
        html += '<button class="btn btn-xs btn-info btn-view" data-id="' + row.id + '">查看</button> ';
        html += '<button class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</button>';
        return html;
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
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
                    {field: 'register_no', title: '注册单号', width: 150},
                    {field: 'company_name', title: '企业名称'},
                    {field: 'contact_name', title: '联系人', width: 100},
                    {field: 'contact_phone', title: '联系电话', width: 120},
                    {field: 'contact_email', title: '联系邮箱', width: 180},
                    {field: 'domain', title: '绑定域名', width: 150},
                    {field: 'package_name', title: '套餐', width: 100},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {field: 'create_time', title: '申请时间', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                    }},
                    {field: 'operate', title: '操作', width: 300, events: {}, formatter: operFmt}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });

            // 状态筛选
            $('#status-filter').change(function () {
                var status = $(this).val();
                $table.bootstrapTable('refresh', {query: {status: status}});
            });

            // 审核通过
            $(document).off('click', '.btn-approve').on('click', '.btn-approve', function () {
                var id = $(this).data('id');
                var company = $(this).data('company');
                if (!confirm('确定通过 [' + company + '] 的注册申请？\n通过后会自动创建租户和管理员账号')) {
                    return;
                }
                $.post(approveUrl, {id: id}, function(r) {
                    if (r.code === 1) {
                        $table.bootstrapTable('refresh');
                        alert('审核通过，已创建租户和管理员账号');
                    } else {
                        alert(r.msg || '操作失败');
                    }
                }, 'json');
            });

            // 审核拒绝
            $(document).off('click', '.btn-reject').on('click', '.btn-reject', function () {
                var id = $(this).data('id');
                var remark = prompt('请输入拒绝原因：');
                if (remark === null) return;
                $.post(rejectUrl, {id: id, remark: remark}, function(r) {
                    if (r.code === 1) {
                        $table.bootstrapTable('refresh');
                        alert('已拒绝该申请');
                    } else {
                        alert(r.msg || '操作失败');
                    }
                }, 'json');
            });

            // 查询状态
            $(document).off('click', '.btn-query').on('click', '.btn-query', function () {
                var no = $(this).data('no');
                $.get(queryUrl, {register_no: no}, function(r) {
                    if (r.code === 1) {
                        var data = r.data;
                        var msg = '注册单号：' + data.register_no + '\n' +
                                  '企业名称：' + data.company_name + '\n' +
                                  '联系人：' + data.contact_name + '\n' +
                                  '联系电话：' + data.contact_phone + '\n' +
                                  '联系邮箱：' + data.contact_email + '\n' +
                                  '期望域名：' + data.domain + '\n' +
                                  '状态：' + data.status_text + '\n' +
                                  '申请时间：' + new Date(data.create_time * 1000).toLocaleString('zh-CN');
                        alert(msg);
                    } else {
                        alert(r.msg || '查询失败');
                    }
                }, 'json');
            });

            // 查看详情
            $(document).off('click', '.btn-view').on('click', '.btn-view', function () {
                var id = $(this).data('id');
                $.get(indexUrl, {id: id}, function(r) {
                    if (r.code === 1) {
                        var data = r.data;
                        var msg = '=== 申请详情 ===\n' +
                                  '注册单号：' + data.register_no + '\n' +
                                  '企业名称：' + data.company_name + '\n' +
                                  '联系人：' + data.contact_name + '\n' +
                                  '联系电话：' + data.contact_phone + '\n' +
                                  '联系邮箱：' + data.contact_email + '\n' +
                                  '期望域名：' + data.domain + '\n' +
                                  '套餐ID：' + data.package_id + '\n' +
                                  '套餐名称：' + data.package_name + '\n' +
                                  '申请说明：' + (data.remark || '-') + '\n' +
                                  '状态：' + data.status_text + '\n' +
                                  '申请时间：' + new Date(data.create_time * 1000).toLocaleString('zh-CN');
                        alert(msg);
                    } else {
                        alert(r.msg || '查询失败');
                    }
                }, 'json');
            });

            // 删除
            $(document).off('click', '.btn-del').on('click', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除该注册申请？')) {
                    return;
                }
                $.post(delUrl, {id: id}, function(r) {
                    if (r.code === 1) {
                        $table.bootstrapTable('refresh');
                        alert('删除成功');
                    } else {
                        alert(r.msg || '删除失败');
                    }
                }, 'json');
            });
        }
    };

    window.__backendController = Controller;
})();
