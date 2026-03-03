(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery, table = $('#table');
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/ai/config/index',
                method: 'get',
                pagination: false,
                sidePagination: 'client',
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return d.list || [];
                },
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'provider', title: '大模型', width: 100, formatter: function (v) {
                        var map = { openai: 'OpenAI', azure: 'Azure', zhipu: '智谱', baidu: '百度文心', aliyun: '阿里通义', tencent: '腾讯混元', xfyun: '讯飞星火' };
                        return map[v] || v;
                    }},
                    { field: 'model', title: '模型', width: 150 },
                    { field: 'rate_limit_per_day', title: '每日限流', width: 100 },
                    { field: 'status', title: '状态', width: 80, formatter: function (v) {
                        return v == 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-secondary">禁用</span>';
                    }},
                    { field: 'operate', title: '操作', width: 160, formatter: function (v, row) {
                        return '<a href="' + base + '/ai/config/edit?id=' + row.id + '" class="btn btn-xs btn-success">编辑</a> ' +
                            '<a href="javascript:;" class="btn btn-xs btn-info btn-test" data-id="' + row.id + '">测试</a> ' +
                            '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
                    }, events: {
                        'click .btn-test': function (e, v, row) {
                            var $btn = $(e.currentTarget);
                            $btn.prop('disabled', true).text('测试中...');
                            $.post(base + '/ai/config/test', { id: row.id }, function (r) {
                                $btn.prop('disabled', false).text('测试');
                                if (r.code == 1) {
                                    alert('测试成功\nAI 回复：' + (r.data && r.data.reply ? r.data.reply : r.msg));
                                } else {
                                    alert('测试失败：' + (r.msg || '未知错误'));
                                }
                            }, 'json').fail(function () {
                                $btn.prop('disabled', false).text('测试');
                                alert('请求失败');
                            });
                        },
                        'click .btn-del': function (e, v, row) {
                            if (confirm('确定删除？')) {
                                $.post(base + '/ai/config/del', { ids: row.id }, function (r) {
                                    alert(r.msg || (r.code == 1 ? '成功' : '失败'));
                                    if (r.code == 1) table.bootstrapTable('refresh');
                                }, 'json');
                            }
                        }
                    }}
                ]
            });
        }
    };
    window.__backendController = Controller;
})();
