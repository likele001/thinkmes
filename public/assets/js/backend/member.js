(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/member/index';
    var editUrl = base + '/member/edit';
    var delUrl = base + '/member/del';
    var resetPwdUrl = base + '/member/resetPwd';

    function statusFmt(v) { return v == 1 ? '正常' : '禁用'; }
    function operFmt(v) {
        return '<a class="btn btn-xs btn-primary" href="' + editUrl + '?id=' + v + '">编辑</a> ' +
            '<button class="btn btn-xs btn-warning btn-reset" data-id="' + v + '" type="button">重置密码</button> ' +
            '<button class="btn btn-xs btn-danger" data-id="' + v + '" type="button">删除</button>';
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
                    { field: 'username', title: '用户名' },
                    { field: 'nickname', title: '昵称' },
                    { field: 'mobile', title: '手机' },
                    { field: 'email', title: '邮箱' },
                    { field: 'login_time', title: '最后登录' },
                    { field: 'status', title: '状态', formatter: statusFmt },
                    { field: 'id', title: '操作', formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除该用户？')) return;
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
            $(document).off('click', '#table button.btn-reset').on('click', '#table button.btn-reset', function () {
                var id = $(this).data('id');
                if (!id) return;
                var pwd = prompt('请输入新密码（6-32位）', '123456');
                if (pwd === null) return;
                if (pwd.length < 6 || pwd.length > 32) { alert('密码长度 6-32'); return; }
                $.post(resetPwdUrl, { id: id, password: pwd }, function (r) {
                    alert(r.msg || (r.code === 1 ? '重置成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
