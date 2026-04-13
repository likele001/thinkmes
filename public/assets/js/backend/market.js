(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : (base + '/market/index');
    var detailUrl = base + '/market/detail';
    var installUrl = base + '/market/install';
    var submitUrl = base + '/market/submit';
    var myPluginsUrl = base + '/market/my_plugins';
    var uninstallUrl = base + '/market/uninstall';
    var enableUrl = base + '/market/enable';
    var disableUrl = base + '/market/disable';

    // 处理 action 名称映射
    var actionMapping = {
        'index': 'my_plugins'
    };

    var Controller = {
        index: function () {
            var $list = $('#plugin-list');
            var $pager = $('#pagination');
            var $kw = $('#search-keyword');
            var $cat = $('#search-category');
            var page = 1;
            var limit = 12;

            function esc(s) {
                s = (s == null) ? '' : String(s);
                return s.replace(/[&<>"']/g, function (c) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
                });
            }

            function money(v) {
                v = Number(v || 0);
                if (!isFinite(v) || v <= 0) return '免费';
                return '￥' + v.toFixed(2);
            }

            function renderCard(r) {
                var title = r.title || r.name || '';
                var desc = r.description || '';
                var shot = r.screenshot ? ('<div style="height:140px;overflow:hidden;border-radius:6px;background:#f3f5f7;"><img src="' + esc(r.screenshot) + '" style="width:100%;height:100%;object-fit:cover;" alt=""></div>') : '';
                var badgeCls = (Number(r.price || 0) > 0) ? 'badge-primary' : 'badge-success';
                return ''
                    + '<div class="col-md-4 col-sm-6 mb-3">'
                    + '  <div class="card h-100 shadow-sm">'
                    + (shot ? ('<div class="card-img-top p-2">' + shot + '</div>') : '')
                    + '    <div class="card-body d-flex flex-column">'
                    + '      <div class="d-flex align-items-start justify-content-between gap-2">'
                    + '        <h5 class="card-title mb-0 text-truncate" title="' + esc(title) + '">' + esc(title) + '</h5>'
                    + '        <span class="badge ' + badgeCls + '">' + esc(money(r.price)) + '</span>'
                    + '      </div>'
                    + '      <div class="text-muted small mt-2" style="min-height:38px;overflow:hidden;">' + esc(desc) + '</div>'
                    + '      <div class="small text-muted mt-2">'
                    + '        <span class="me-2"><i class="fas fa-tag me-1"></i>' + esc(r.category || '') + '</span>'
                    + '        <span class="me-2"><i class="fas fa-user me-1"></i>' + esc(r.author || '') + '</span>'
                    + '        <span class="me-2"><i class="fas fa-download me-1"></i>' + esc(r.downloads || 0) + '</span>'
                    + '        <span><i class="fas fa-star me-1"></i>' + esc(r.rating || 0) + '</span>'
                    + '      </div>'
                    + '      <div class="mt-3 d-flex gap-2">'
                    + '        <button type="button" class="btn btn-sm btn-outline-primary flex-fill btn-view" data-id="' + esc(r.id) + '">详情</button>'
                    + '        <button type="button" class="btn btn-sm btn-primary flex-fill btn-install" data-id="' + esc(r.id) + '" data-version="' + esc(r.version || '') + '" data-title="' + esc(title) + '">安装</button>'
                    + '      </div>'
                    + '    </div>'
                    + '  </div>'
                    + '</div>';
            }

            function renderPagination(cur, last) {
                cur = Number(cur || 1);
                last = Number(last || 1);
                if (last <= 1) {
                    $pager.html('');
                    return;
                }
                var html = '<nav><ul class="pagination justify-content-center mb-0">';
                var prev = Math.max(1, cur - 1);
                var next = Math.min(last, cur + 1);
                html += '<li class="page-item ' + (cur <= 1 ? 'disabled' : '') + '"><a class="page-link" href="javascript:;" data-page="' + prev + '">&laquo;</a></li>';
                var start = Math.max(1, cur - 2);
                var end = Math.min(last, cur + 2);
                for (var i = start; i <= end; i++) {
                    html += '<li class="page-item ' + (i === cur ? 'active' : '') + '"><a class="page-link" href="javascript:;" data-page="' + i + '">' + i + '</a></li>';
                }
                html += '<li class="page-item ' + (cur >= last ? 'disabled' : '') + '"><a class="page-link" href="javascript:;" data-page="' + next + '">&raquo;</a></li>';
                html += '</ul></nav>';
                $pager.html(html);
            }

            function load() {
                var keyword = $.trim($kw.val() || '');
                var category = $cat.val() || '';
                $list.html('<div class="col-12"><div class="alert alert-info mb-0">加载中...</div></div>');
                $.get(indexUrl, { keyword: keyword, category: category, page: page, limit: limit }, function (res) {
                    if (!res || res.code !== 0 || !res.data) {
                        $list.html('<div class="col-12"><div class="alert alert-danger mb-0">' + esc((res && res.msg) ? res.msg : '加载失败') + '</div></div>');
                        $pager.html('');
                        return;
                    }
                    var d = res.data || {};
                    var rows = d.data || [];
                    if (!rows.length) {
                        $list.html('<div class="col-12"><div class="alert alert-secondary mb-0">暂无数据</div></div>');
                    } else {
                        $list.html(rows.map(renderCard).join(''));
                    }
                    renderPagination(Number(d.current_page || 1), Number(d.last_page || 1));
                }, 'json').fail(function () {
                    $list.html('<div class="col-12"><div class="alert alert-danger mb-0">接口请求失败或返回非 JSON</div></div>');
                    $pager.html('');
                });
            }

            function installNow(id, version, title) {
                if (!id) return;
                version = $.trim(version || '');
                if (!version) {
                    alert('缺少版本号，无法安装');
                    return;
                }
                if (!confirm('确认安装：' + (title || '该插件') + ' v' + version + ' ?')) return;
                $.post(installUrl + '?id=' + encodeURIComponent(id), { version: version }, function (res) {
                    if (res && res.code === 1) {
                        alert(res.msg || '安装成功');
                        load();
                        return;
                    }
                    alert((res && res.msg) ? res.msg : '安装失败');
                }, 'json').fail(function () {
                    alert('安装请求失败');
                });
            }

            $(document).off('click.market', '.btn-search').on('click.market', '.btn-search', function () {
                page = 1;
                load();
            });
            $(document).off('click.market', '.btn-reset').on('click.market', '.btn-reset', function () {
                $kw.val('');
                $cat.val('');
                page = 1;
                load();
            });
            $(document).off('click.market', '#toolbar .btn-refresh').on('click.market', '#toolbar .btn-refresh', function () {
                load();
            });
            $(document).off('click.market', '#toolbar .btn-my-plugins').on('click.market', '#toolbar .btn-my-plugins', function () {
                window.location.href = myPluginsUrl;
            });
            $(document).off('click.market', '#pagination .page-link').on('click.market', '#pagination .page-link', function () {
                var p = Number($(this).data('page') || 1);
                if (!p || p === page) return;
                page = p;
                load();
            });
            $(document).off('click.market', '.btn-view').on('click.market', '.btn-view', function () {
                var id = $(this).data('id');
                if (!id) return;
                window.location.href = detailUrl + '?id=' + encodeURIComponent(id);
            });
            $(document).off('click.market', '.btn-install').on('click.market', '.btn-install', function () {
                installNow($(this).data('id'), $(this).data('version'), $(this).data('title'));
            });

            load();
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

                $.post(installUrl + '?id=' + encodeURIComponent(id), { version: versionId }, function (res) {
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
            console.log('[DEBUG] my_plugins action called');
            console.log('[DEBUG] Config:', typeof Config !== 'undefined' ? Config : 'Config not defined');
            console.log('[DEBUG] myPluginsUrl:', myPluginsUrl);

            var table = $('#table');
            console.log('[DEBUG] table element:', table.length > 0 ? 'found' : 'not found');

            var marketUrl = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl + '/market/index' : '/admin/market/index';

            table.bootstrapTable({
                url: myPluginsUrl,
                responseHandler: function (res) {
                    console.log('[DEBUG] AJAX response:', res);
                    var d = (res && res.data) ? res.data : null;
                    return {
                        total: d ? (d.total || 0) : 0,
                        rows: d ? (d.data || []) : []
                    };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'plugin_title', title: '插件名称' },
                    { field: 'version', title: '版本' },
                    { field: 'install_time', title: '安装时间', formatter: function(value) {
                        if (!value) return '-';
                        var date = new Date(value * 1000);
                        return date.toLocaleString('zh-CN');
                    }},
                    { field: 'status', title: '状态', formatter: function (value) {
                        if (value === undefined) value = 1;
                        return value === 1 ? '<span class="badge bg-success">启用</span>' : '<span class="badge bg-secondary">禁用</span>';
                    }},
                    {
                        field: 'operate',
                        title: '操作',
                        width: 200,
                        formatter: function (value, row) {
                            var html = '';
                            var status = (row.status === undefined) ? 1 : row.status;
                            if (status === 1) {
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

            console.log('[DEBUG] BootstrapTable initialized');

            // 返回插件市场
            $(document).on('click', '.btn-market', function () {
                window.location.href = marketUrl;
            });

            // 刷新按钮
            $(document).on('click', '.btn-refresh', function () {
                table.bootstrapTable('refresh');
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

    // 添加 action 名称映射，支持多种 action 名称
    Controller.index = Controller.my_plugins;
    Controller.myplugins = Controller.my_plugins;  // 小写驼峰形式
    Controller.myPlugins = Controller.my_plugins;  // 驼峰形式

    window.__backendController = Controller;
})();
