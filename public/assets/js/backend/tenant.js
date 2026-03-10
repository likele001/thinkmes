(function () {
    var L = (typeof Config !== 'undefined' && Config.lang) ? Config.lang : {};
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/tenant/index';
    var editUrl = base + '/tenant/edit';
    var delUrl = base + '/tenant/del';
    var orderAddUrl = base + '/tenant_order/add';
    var frontBase = window.location.origin || '';
    function statusFmt(v) { return v == 1 ? '<span class="badge badge-success">' + (L.status_normal || '正常') + '</span>' : '<span class="badge badge-danger">' + (L.status_disabled || '禁用') + '</span>'; }
    function operFmt(v, row) {
        var html = '<a class="btn btn-xs btn-primary" href="' + editUrl + '?id=' + v + '">' + (L.edit || '编辑') + '</a> ';
        html += '<a class="btn btn-xs btn-info" href="' + orderAddUrl + '?tenant_id=' + v + '" title="' + (L.create_order || '创建订单') + '"><i class="fas fa-shopping-cart"></i> ' + (L.order || '订单') + '</a> ';
        html += '<button class="btn btn-xs btn-danger" data-id="' + v + '" type="button">' + (L.delete || '删除') + '</button>';
        return html;
    }

    function portalFmt(v, row) {
        var id = v || row.id || 0;
        if (!id) return '-';
        var loginUrl = frontBase + '/index/user/login?tenant_id=' + id;
        var registerUrl = frontBase + '/index/user/login?tenant_id=' + id + '&tab=register';
        var html = '<div class="text-left" style="min-width:200px;font-size:12px;line-height:1.6;">';
        html += '<div>' + (L.login_label || '登录') + '：<a href="' + loginUrl + '" target="_blank">' + loginUrl + '</a></div>';
        html += '<div>' + (L.register_label || '注册') + '：<a href="' + registerUrl + '" target="_blank">' + registerUrl + '</a></div>';
        html += '</div>';
        return html;
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            $table.bootstrapTable({
                url: indexUrl,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'name', title: L.tenant_name || '租户名称' },
                    { field: 'domain', title: L.domain || '绑定域名' },
                    { field: 'package_name', title: L.package || '套餐' },
                    { field: 'expire_time_text', title: L.expire || '到期' },
                    { field: 'admin_names', title: L.admin_names || '管理员' },
                    { field: 'id', title: L.portal_entry || '前端报工入口', formatter: portalFmt },
                    { field: 'status', title: L.status || '状态', formatter: statusFmt },
                    { field: 'id', title: L.operation || '操作', formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm(L.confirm_del_tenant || '确定删除该租户？')) return;
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? (L.delete_success || '删除成功') : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
