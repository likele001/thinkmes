(function () {
    var L = (typeof Config !== 'undefined' && Config.lang) ? Config.lang : {};
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/member/index';
    var editUrl = base + '/member/edit';
    var delUrl = base + '/member/del';
    var resetPwdUrl = base + '/member/resetPwd';

    function statusFmt(v) { return v == 1 ? (L.status_normal || '正常') : (L.status_disabled || '禁用'); }
    function operFmt(v) {
        return '<a class="btn btn-xs btn-primary" href="' + editUrl + '?id=' + v + '">' + (L.edit || '编辑') + '</a> ' +
            '<button class="btn btn-xs btn-warning btn-reset" data-id="' + v + '" type="button">' + (L.reset_pwd || '重置密码') + '</button> ' +
            '<button class="btn btn-xs btn-danger" data-id="' + v + '" type="button">' + (L.delete || '删除') + '</button>';
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
                    { field: 'username', title: L.username || '用户名' },
                    { field: 'nickname', title: L.nickname || '昵称' },
                    { field: 'mobile', title: L.mobile || '手机' },
                    { field: 'email', title: L.email || '邮箱' },
                    { field: 'login_time', title: L.last_login || '最后登录' },
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
                if (!id || !confirm(L.confirm_del_user || '确定删除该用户？')) return;
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? (L.delete_success || '删除成功') : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
            $(document).off('click', '#table button.btn-reset').on('click', '#table button.btn-reset', function () {
                var id = $(this).data('id');
                if (!id) return;
                var pwd = prompt(L.prompt_new_pwd || '请输入新密码（6-32位）', '123456');
                if (pwd === null) return;
                if (pwd.length < 6 || pwd.length > 32) { alert(L.pwd_length_hint || '密码长度 6-32'); return; }
                $.post(resetPwdUrl, { id: id, password: pwd }, function (r) {
                    alert(r.msg || (r.code === 1 ? (L.reset_success || '重置成功') : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
