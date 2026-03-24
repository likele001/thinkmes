(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : (base + '/market/index');
    var detailUrl = base + '/market/detail';
    var installUrl = base + '/market/install';
    var submitUrl = base + '/market/submit';
    var myPluginsUrl = base + '/market/my_plugins';
    var installActionUrl = base + '/market/do_install';
    var uninstallUrl = base + '/market/uninstall';
    var enableUrl = base + '/market/enable';
    var disableUrl = base + '/market/disable';

    var Controller = {
        index: function () {
            var table = $('#table');
            var searchForm = $('#toolbar-form');

            function loadData() {
                var keyword = searchForm.find('input[name="keyword"]').val();
                var category = searchForm.find('select[name="category"]').val();

                table.bootstrapTable({
                    url: indexUrl,
                    queryParams: function (params) {
                        return {
                            page: params.pageNumber,
                            limit: params.pageSize,
                            search: keyword,
                            category: category
                        };
                    },
                    responseHandler: function (res) {
                        return {
                            total: res.count || 0,
                            rows: res.data || []
                        };
                    },
                    columns: [
                        { checkbox: true },
                        { field: 'id', title: 'ID', width: 60 },
                        { field: 'name', title: '插件名称' },
                        { field: 'author', title: '作者' },
                        { field: 'category', title: '分类' },
                        { field: 'downloads', title: '下载量', width: 80 },
                        { field: 'rating', title: '评分', width: 80 },
                        { field: 'price', title: '价格', width: 80 },
                        {
                            field: 'operate',
                            title: '操作',
                            width: 200,
                            formatter: function (value, row) {
                                var html = '<a href="' + detailUrl + '?id=' + row.id + '" class="btn btn-xs btn-success">详情</a> ';
                                html += '<a href="' + installUrl + '?id=' + row.id + '" class="btn btn-xs btn-primary">安装</a>';
                                return html;
                            }
                        }
                    ]
                });
            }

            searchForm.on('submit', function (e) {
                e.preventDefault();
                table.bootstrapTable('refresh');
            });

            $('#toolbar .btn-refresh').on('click', function () {
                table.bootstrapTable('refresh');
            });

            loadData();
        },

        detail: function () {
            $('#btn-install').on('click', function () {
                var id = $(this).data('id');
                location.href = installUrl + '?id=' + id;
            });
        },

        install: function () {
            $('#btn-do-install').on('click', function () {
                var id = $(this).data('id');
                var versionId = $('select[name="version_id"]').val();

                if (!versionId) {
                    alert('请选择版本');
                    return;
                }

                $.post(installActionUrl, { id: id, version_id: versionId }, function (res) {
                    if (res.code === 1) {
                        alert(res.msg);
                        location.href = myPluginsUrl;
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });
        },

        submit: function () {
            $('#form-submit').on('submit', function (e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: submitUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.code === 1) {
                            alert(res.msg);
                            location.href = myPluginsUrl;
                        } else {
                            alert(res.msg);
                        }
                    },
                    error: function () {
                        alert('提交失败');
                    }
                });
            });
        },

        my_plugins: function () {
            var table = $('#table');

            table.bootstrapTable({
                url: myPluginsUrl,
                responseHandler: function (res) {
                    return {
                        total: res.count || 0,
                        rows: res.data || []
                    };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'plugin_name', title: '插件名称' },
                    { field: 'version', title: '版本' },
                    { field: 'install_time', title: '安装时间' },
                    { field: 'status', title: '状态', formatter: function (value) {
                        return value === 1 ? '<span class="label label-success">启用</span>' : '<span class="label label-default">禁用</span>';
                    }},
                    {
                        field: 'operate',
                        title: '操作',
                        width: 200,
                        formatter: function (value, row) {
                            var html = '';
                            if (row.status === 1) {
                                html += '<button class="btn btn-xs btn-warning btn-disable" data-id="' + row.id + '">禁用</button> ';
                            } else {
                                html += '<button class="btn btn-xs btn-success btn-enable" data-id="' + row.id + '">启用</button> ';
                            }
                            html += '<button class="btn btn-xs btn-danger btn-uninstall" data-id="' + row.id + '">卸载</button>';
                            return html;
                        }
                    }
                ]
            });

            $(document).on('click', '.btn-enable', function () {
                var id = $(this).data('id');
                $.post(enableUrl, { id: id }, function (res) {
                    if (res.code === 1) {
                        table.bootstrapTable('refresh');
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });

            $(document).on('click', '.btn-disable', function () {
                var id = $(this).data('id');
                $.post(disableUrl, { id: id }, function (res) {
                    if (res.code === 1) {
                        table.bootstrapTable('refresh');
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });

            $(document).on('click', '.btn-uninstall', function () {
                var id = $(this).data('id');
                if (!confirm('确定卸载该插件？')) return;

                $.post(uninstallUrl, { id: id }, function (res) {
                    if (res.code === 1) {
                        table.bootstrapTable('refresh');
                    } else {
                        alert(res.msg);
                    }
                }, 'json');
            });
        }
    };

    window.__backendController = Controller;
})();
