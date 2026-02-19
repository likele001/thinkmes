(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/addon/index';
    var enableUrl = base + '/addon/enable';
    var disableUrl = base + '/addon/disable';
    var installUrl = base + '/addon/install';
    var uninstallUrl = base + '/addon/uninstall';
    var detailUrl = base + '/addon/detail';
    var configUrl = base + '/addon/config';

    function postAction(url, name, onSuccess) {
        $.post(url, {name: name}, function (ret) {
            var ok = ret && ret.code === 1;
            alert((ret && ret.msg) || (ok ? '操作成功' : '操作失败'));
            if (ok && typeof onSuccess === 'function') {
                onSuccess();
            }
        }, 'json').fail(function () {
            alert('请求失败');
        });
    }

    var Controller = {
        index: function () {
            var $table = $('#table-addon');
            if (!$table.length) {
                return;
            }
            $table.bootstrapTable({
                url: indexUrl,
                method: 'get',
                pagination: false,
                sidePagination: 'client',
                dataField: 'list',
                responseHandler: function (res) {
                    if (res && res.data) {
                        return res.data;
                    }
                    return {total: 0, list: []};
                },
                columns: [
                    {field: 'name', title: '标识'},
                    {field: 'title', title: '名称'},
                    {field: 'version', title: '版本'},
                    {
                        field: 'enabled',
                        title: '状态',
                        formatter: function (value, row) {
                            var installed = parseInt(row.installed, 10) === 1;
                            if (!installed) {
                                return '<span class="badge badge-secondary">未安装</span>';
                            }
                            if (parseInt(value, 10) === 1) {
                                return '<span class="badge badge-success">已启用</span>';
                            }
                            return '<span class="badge badge-warning">已安装未启用</span>';
                        }
                    },
                    {
                        field: 'operate',
                        title: '操作',
                        formatter: function (value, row) {
                            var html = [];
                            var installed = parseInt(row.installed, 10) === 1;
                            html.push('<a class="btn btn-xs btn-info" href="' + detailUrl + '?name=' + encodeURIComponent(row.name) + '&addtabs=1">详情</a>');
                            html.push('<a class="btn btn-xs btn-secondary" href="' + configUrl + '?name=' + encodeURIComponent(row.name) + '&addtabs=1">配置</a>');
                            if (installed) {
                                if (parseInt(row.enabled, 10) === 1) {
                                    html.push('<button type="button" class="btn btn-xs btn-warning btn-addon-disable" data-name="' + row.name + '">禁用</button>');
                                } else {
                                    html.push('<button type="button" class="btn btn-xs btn-success btn-addon-enable" data-name="' + row.name + '">启用</button>');
                                }
                                html.push('<button type="button" class="btn btn-xs btn-danger btn-addon-uninstall" data-name="' + row.name + '">卸载</button>');
                            } else {
                                html.push('<button type="button" class="btn btn-xs btn-primary btn-addon-install" data-name="' + row.name + '">安装</button>');
                            }
                            return html.join(' ');
                        },
                        events: {
                            'click .btn-addon-enable': function (e, value, row) {
                                e.preventDefault();
                                postAction(enableUrl, row.name, function () {
                                    $table.bootstrapTable('refresh');
                                });
                            },
                            'click .btn-addon-disable': function (e, value, row) {
                                e.preventDefault();
                                postAction(disableUrl, row.name, function () {
                                    $table.bootstrapTable('refresh');
                                });
                            },
                            'click .btn-addon-install': function (e, value, row) {
                                e.preventDefault();
                                postAction(installUrl, row.name, function () {
                                    $table.bootstrapTable('refresh');
                                });
                            },
                            'click .btn-addon-uninstall': function (e, value, row) {
                                e.preventDefault();
                                postAction(uninstallUrl, row.name, function () {
                                    $table.bootstrapTable('refresh');
                                });
                            }
                        }
                    }
                ]
            });
        },
        config: function () {}
    };

    window.__backendController = Controller;
})();
