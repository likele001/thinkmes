(function () {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? String(Config.moduleurl).replace(/\/$/, '') : '/admin';

    function fmtTime(ts) {
        ts = Number(ts || 0);
        if (!isFinite(ts) || ts <= 0) return '-';
        return new Date(ts * 1000).toLocaleString('zh-CN');
    }

    function badge(text, cls) {
        return '<span class="badge bg-' + cls + '">' + (text || '-') + '</span>';
    }

    function ensureModal(id, title, bodyHtml) {
        var el = document.getElementById(id);
        if (el) return el;
        el = document.createElement('div');
        el.className = 'modal fade';
        el.id = id;
        el.tabIndex = -1;
        el.setAttribute('aria-hidden', 'true');
        el.innerHTML = ''
            + '<div class="modal-dialog modal-dialog-centered modal-lg">'
            + '  <div class="modal-content">'
            + '    <div class="modal-header">'
            + '      <h5 class="modal-title"></h5>'
            + '      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
            + '    </div>'
            + '    <div class="modal-body"></div>'
            + '    <div class="modal-footer">'
            + '      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>'
            + '      <button type="button" class="btn btn-primary" data-role="ok">保存</button>'
            + '    </div>'
            + '  </div>'
            + '</div>';
        document.body.appendChild(el);
        $(el).find('.modal-title').text(title || '');
        $(el).find('.modal-body').html(bodyHtml || '');
        return el;
    }

    function openModal(modalEl, onOk) {
        var m = new bootstrap.Modal(modalEl);
        $(modalEl).off('click', '[data-role="ok"]').on('click', '[data-role="ok"]', function () {
            if (typeof onOk === 'function') onOk(m);
        });
        m.show();
        return m;
    }

    var Controller = {
        sessions: function () {
            var $table = $('#table');
            if (!$table.length || typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
            $table.bootstrapTable({
                url: base + '/customer_service/get_session_list',
                pagination: true,
                sidePagination: 'server',
                pageSize: 15,
                pageList: [15, 30, 50],
                queryParams: function (params) {
                    var page = (params.offset != null) ? (Math.floor(Number(params.offset || 0) / Number(params.limit || 15)) + 1) : 1;
                    return {
                        page: page,
                        limit: params.limit,
                        status: $('#search-status').val() || '',
                        keyword: $('#search-keyword').val() || ''
                    };
                },
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: Number(d.total || 0), rows: d.list || [] };
                },
                columns: [
                    { field: 'session_id', title: '会话ID', width: 220 },
                    { field: 'visitor_name', title: '访客名称', width: 140 },
                    { field: 'visitor_email', title: '访客邮箱', width: 220 },
                    {
                        field: 'status', title: '状态', width: 100, formatter: function (v) {
                            var map = { 0: ['待接待', 'warning'], 1: ['进行中', 'success'], 2: ['已关闭', 'secondary'] };
                            var it = map[v] || ['-', 'secondary'];
                            return badge(it[0], it[1]);
                        }
                    },
                    { field: 'start_time', title: '开始时间', width: 180, formatter: fmtTime },
                    { field: 'last_message_time', title: '最后消息时间', width: 180, formatter: fmtTime },
                    {
                        field: 'operate', title: '操作', width: 120, formatter: function (v, row) {
                            return '<a class="btn btn-xs btn-primary" href="' + base + '/customer_service/session_detail?session_id=' + encodeURIComponent(row.session_id || '') + '">查看</a>';
                        }
                    }
                ]
            });
            $(document).off('submit', '#search-form').on('submit', '#search-form', function (e) {
                e.preventDefault();
                $table.bootstrapTable('refresh', { pageNumber: 1 });
            });
            $(document).off('click', '.btn-reset').on('click', '.btn-reset', function () {
                var f = document.getElementById('search-form');
                if (f) f.reset();
                $table.bootstrapTable('refresh', { pageNumber: 1 });
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });
        },

        knowledge: function () {
            var $table = $('#table');
            if (!$table.length || typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;

            var catMap = {};
            function loadCats(cb) {
                $.get(base + '/customer_service/get_category_list', function (res) {
                    var list = (res && res.data && res.data.list) ? res.data.list : [];
                    catMap = {};
                    for (var i = 0; i < list.length; i++) {
                        var it = list[i] || {};
                        catMap[String(it.id)] = it.name || ('#' + it.id);
                    }
                    var $sel = $('#search-category_id');
                    if ($sel.length) {
                        $sel.empty();
                        $sel.append('<option value="0">全部分类</option>');
                        for (var k in catMap) {
                            if (!Object.prototype.hasOwnProperty.call(catMap, k)) continue;
                            $sel.append('<option value="' + k + '">' + catMap[k] + '</option>');
                        }
                    }
                    if (typeof cb === 'function') cb();
                }, 'json').fail(function () {
                    if (typeof cb === 'function') cb();
                });
            }

            loadCats(function () {
                $table.bootstrapTable({
                    url: base + '/customer_service/get_article_list',
                    pagination: true,
                    sidePagination: 'server',
                    pageSize: 15,
                    pageList: [15, 30, 50],
                    queryParams: function (params) {
                        var page = (params.offset != null) ? (Math.floor(Number(params.offset || 0) / Number(params.limit || 15)) + 1) : 1;
                        return {
                            page: page,
                            limit: params.limit,
                            category_id: $('#search-category_id').val() || '0',
                            status: $('#search-status').val() || '',
                            keyword: $('#search-keyword').val() || ''
                        };
                    },
                    responseHandler: function (res) {
                        var d = res && res.data ? res.data : {};
                        return { total: Number(d.total || 0), rows: d.list || [] };
                    },
                    columns: [
                        { field: 'id', title: 'ID', width: 80 },
                        { field: 'title', title: '标题' },
                        { field: 'category_id', title: '分类', width: 140, formatter: function (v) { return catMap[String(v)] || ('#' + v); } },
                        {
                            field: 'status', title: '状态', width: 100, formatter: function (v) {
                                var map = { 0: ['草稿', 'secondary'], 1: ['已发布', 'success'], 2: ['已下线', 'warning'] };
                                var it = map[v] || ['-', 'secondary'];
                                return badge(it[0], it[1]);
                            }
                        },
                        { field: 'views', title: '浏览', width: 80 },
                        { field: 'likes', title: '点赞', width: 80 },
                        { field: 'create_time', title: '创建时间', width: 180, formatter: fmtTime },
                        {
                            field: 'operate', title: '操作', width: 200, formatter: function (v, row) {
                                var id = row.id || 0;
                                return ''
                                    + '<a class="btn btn-xs btn-primary" href="' + base + '/customer_service/article_edit?id=' + id + '">编辑</a> '
                                    + '<button class="btn btn-xs btn-danger btn-del-article" type="button" data-id="' + id + '">删除</button>';
                            }
                        }
                    ]
                });
            });

            $(document).off('submit', '#search-form').on('submit', '#search-form', function (e) {
                e.preventDefault();
                $table.bootstrapTable('refresh', { pageNumber: 1 });
            });
            $(document).off('click', '.btn-reset').on('click', '.btn-reset', function () {
                var f = document.getElementById('search-form');
                if (f) f.reset();
                $table.bootstrapTable('refresh', { pageNumber: 1 });
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });
            $(document).off('click', '.btn-del-article').on('click', '.btn-del-article', function () {
                var id = Number($(this).data('id') || 0);
                if (!id || !confirm('确定删除该文章？')) return;
                $.post(base + '/customer_service/delete_article', { id: id }, function (res) {
                    if (res && res.code === 1) {
                        if (window.Toast) Toast.success(res.msg || '删除成功');
                        $table.bootstrapTable('refresh');
                    } else {
                        if (window.Toast) Toast.error((res && res.msg) ? res.msg : '删除失败');
                    }
                }, 'json').fail(function () {
                    if (window.Toast) Toast.error('请求失败');
                });
            });
        },

        faq: function () {
            var $table = $('#table');
            if (!$table.length || typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;

            $table.bootstrapTable({
                url: base + '/customer_service/get_faq_list',
                pagination: true,
                sidePagination: 'server',
                pageSize: 15,
                pageList: [15, 30, 50],
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: Number(d.total || 0), rows: d.list || [] };
                },
                queryParams: function (params) {
                    var page = (params.offset != null) ? (Math.floor(Number(params.offset || 0) / Number(params.limit || 15)) + 1) : 1;
                    return { page: page, limit: params.limit };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 80 },
                    { field: 'category', title: '分类', width: 140 },
                    { field: 'question', title: '问题' },
                    { field: 'sort', title: '排序', width: 80 },
                    { field: 'views', title: '浏览', width: 80 },
                    { field: 'helpful', title: '有用', width: 80 },
                    { field: 'status', title: '状态', width: 100, formatter: function (v) { return v == 1 ? badge('启用', 'success') : badge('禁用', 'secondary'); } },
                    {
                        field: 'operate', title: '操作', width: 180, formatter: function (v, row) {
                            return ''
                                + '<button class="btn btn-xs btn-primary btn-edit-faq" type="button" data-id="' + (row.id || 0) + '">编辑</button> '
                                + '<button class="btn btn-xs btn-danger btn-del-faq" type="button" data-id="' + (row.id || 0) + '">删除</button>';
                        }
                    }
                ]
            });

            var modal = ensureModal('modal-faq-edit', 'FAQ', ''
                + '<form id="faq-form">'
                + '  <input type="hidden" name="id" value="">'
                + '  <div class="mb-3"><label class="form-label">分类</label><input class="form-control" name="category" placeholder="例如：常见问题"></div>'
                + '  <div class="mb-3"><label class="form-label">问题</label><input class="form-control" name="question" placeholder="请输入问题"></div>'
                + '  <div class="mb-3"><label class="form-label">答案</label><textarea class="form-control" name="answer" rows="6" placeholder="请输入答案"></textarea></div>'
                + '  <div class="row g-2">'
                + '    <div class="col-6"><label class="form-label">排序</label><input type="number" class="form-control" name="sort" value="0"></div>'
                + '    <div class="col-6"><label class="form-label">状态</label><select class="form-select" name="status"><option value="1">启用</option><option value="0">禁用</option></select></div>'
                + '  </div>'
                + '</form>');

            function fillFaqForm(row) {
                row = row || {};
                var $f = $('#faq-form');
                $f.find('[name="id"]').val(row.id || '');
                $f.find('[name="category"]').val(row.category || '');
                $f.find('[name="question"]').val(row.question || '');
                $f.find('[name="answer"]').val(row.answer || '');
                $f.find('[name="sort"]').val(row.sort != null ? row.sort : 0);
                $f.find('[name="status"]').val(String(row.status != null ? row.status : 1));
            }

            $(document).off('click', '.btn-add').on('click', '.btn-add', function () {
                $(modal).find('.modal-title').text('添加FAQ');
                fillFaqForm({});
                openModal(modal, function (m) {
                    $.post(base + '/customer_service/save_faq', $('#faq-form').serialize(), function (res) {
                        if (res && res.code === 1) {
                            if (window.Toast) Toast.success(res.msg || '保存成功');
                            m.hide();
                            $table.bootstrapTable('refresh');
                        } else {
                            if (window.Toast) Toast.error((res && res.msg) ? res.msg : '保存失败');
                        }
                    }, 'json');
                });
            });

            $(document).off('click', '.btn-edit-faq').on('click', '.btn-edit-faq', function () {
                var id = Number($(this).data('id') || 0);
                var data = $table.bootstrapTable('getData') || [];
                var row = null;
                for (var i = 0; i < data.length; i++) if (Number(data[i].id) === id) { row = data[i]; break; }
                $(modal).find('.modal-title').text('编辑FAQ');
                fillFaqForm(row || { id: id });
                openModal(modal, function (m) {
                    $.post(base + '/customer_service/save_faq', $('#faq-form').serialize(), function (res) {
                        if (res && res.code === 1) {
                            if (window.Toast) Toast.success(res.msg || '保存成功');
                            m.hide();
                            $table.bootstrapTable('refresh');
                        } else {
                            if (window.Toast) Toast.error((res && res.msg) ? res.msg : '保存失败');
                        }
                    }, 'json');
                });
            });

            $(document).off('click', '.btn-del-faq').on('click', '.btn-del-faq', function () {
                var id = Number($(this).data('id') || 0);
                if (!id || !confirm('确定删除该FAQ？')) return;
                $.post(base + '/customer_service/delete_faq', { id: id }, function (res) {
                    if (res && res.code === 1) {
                        if (window.Toast) Toast.success(res.msg || '删除成功');
                        $table.bootstrapTable('refresh');
                    } else {
                        if (window.Toast) Toast.error((res && res.msg) ? res.msg : '删除失败');
                    }
                }, 'json');
            });

            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });
        },

        categories: function () {
            var $table = $('#table');
            if (!$table.length || typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;

            $table.bootstrapTable({
                url: base + '/customer_service/get_category_list',
                pagination: false,
                responseHandler: function (res) {
                    var list = (res && res.data && res.data.list) ? res.data.list : [];
                    return { total: list.length, rows: list };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 80 },
                    { field: 'parent_id', title: '父分类', width: 100, formatter: function (v) { return v ? ('#' + v) : '-'; } },
                    { field: 'name', title: '名称', width: 180 },
                    { field: 'description', title: '描述' },
                    { field: 'icon', title: '图标', width: 140 },
                    { field: 'sort', title: '排序', width: 80 },
                    { field: 'status', title: '状态', width: 100, formatter: function (v) { return v == 1 ? badge('启用', 'success') : badge('禁用', 'secondary'); } },
                    {
                        field: 'operate', title: '操作', width: 180, formatter: function (v, row) {
                            return ''
                                + '<button class="btn btn-xs btn-primary btn-edit-cat" type="button" data-id="' + (row.id || 0) + '">编辑</button> '
                                + '<button class="btn btn-xs btn-danger btn-del-cat" type="button" data-id="' + (row.id || 0) + '">删除</button>';
                        }
                    }
                ]
            });

            var modal = ensureModal('modal-cat-edit', '分类', ''
                + '<form id="cat-form">'
                + '  <input type="hidden" name="id" value="">'
                + '  <div class="row g-2">'
                + '    <div class="col-6"><label class="form-label">父分类ID</label><input type="number" class="form-control" name="parent_id" value="0"></div>'
                + '    <div class="col-6"><label class="form-label">排序</label><input type="number" class="form-control" name="sort" value="0"></div>'
                + '  </div>'
                + '  <div class="mb-3 mt-2"><label class="form-label">名称</label><input class="form-control" name="name" placeholder="请输入分类名称"></div>'
                + '  <div class="mb-3"><label class="form-label">描述</label><input class="form-control" name="description" placeholder="描述（可选）"></div>'
                + '  <div class="mb-3"><label class="form-label">图标</label><input class="form-control" name="icon" placeholder="例如：fa-book"></div>'
                + '  <div class="mb-3"><label class="form-label">状态</label><select class="form-select" name="status"><option value="1">启用</option><option value="0">禁用</option></select></div>'
                + '</form>');

            function fillCatForm(row) {
                row = row || {};
                var $f = $('#cat-form');
                $f.find('[name="id"]').val(row.id || '');
                $f.find('[name="parent_id"]').val(row.parent_id != null ? row.parent_id : 0);
                $f.find('[name="name"]').val(row.name || '');
                $f.find('[name="description"]').val(row.description || '');
                $f.find('[name="icon"]').val(row.icon || '');
                $f.find('[name="sort"]').val(row.sort != null ? row.sort : 0);
                $f.find('[name="status"]').val(String(row.status != null ? row.status : 1));
            }

            $(document).off('click', '.btn-add').on('click', '.btn-add', function () {
                $(modal).find('.modal-title').text('添加分类');
                fillCatForm({});
                openModal(modal, function (m) {
                    $.post(base + '/customer_service/save_category', $('#cat-form').serialize(), function (res) {
                        if (res && res.code === 1) {
                            if (window.Toast) Toast.success(res.msg || '保存成功');
                            m.hide();
                            $table.bootstrapTable('refresh');
                        } else {
                            if (window.Toast) Toast.error((res && res.msg) ? res.msg : '保存失败');
                        }
                    }, 'json');
                });
            });

            $(document).off('click', '.btn-edit-cat').on('click', '.btn-edit-cat', function () {
                var id = Number($(this).data('id') || 0);
                var data = $table.bootstrapTable('getData') || [];
                var row = null;
                for (var i = 0; i < data.length; i++) if (Number(data[i].id) === id) { row = data[i]; break; }
                $(modal).find('.modal-title').text('编辑分类');
                fillCatForm(row || { id: id });
                openModal(modal, function (m) {
                    $.post(base + '/customer_service/save_category', $('#cat-form').serialize(), function (res) {
                        if (res && res.code === 1) {
                            if (window.Toast) Toast.success(res.msg || '保存成功');
                            m.hide();
                            $table.bootstrapTable('refresh');
                        } else {
                            if (window.Toast) Toast.error((res && res.msg) ? res.msg : '保存失败');
                        }
                    }, 'json');
                });
            });

            $(document).off('click', '.btn-del-cat').on('click', '.btn-del-cat', function () {
                var id = Number($(this).data('id') || 0);
                if (!id || !confirm('确定删除该分类？')) return;
                $.post(base + '/customer_service/delete_category', { id: id }, function (res) {
                    if (res && res.code === 1) {
                        if (window.Toast) Toast.success(res.msg || '删除成功');
                        $table.bootstrapTable('refresh');
                    } else {
                        if (window.Toast) Toast.error((res && res.msg) ? res.msg : '删除失败');
                    }
                }, 'json');
            });

            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });
        },

        aihistory: function () {
            var $table = $('#table');
            if (!$table.length || typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;

            function shortText(s) {
                s = (s == null) ? '' : String(s);
                if (s.length <= 80) return s;
                return s.substring(0, 80) + '...';
            }

            $table.bootstrapTable({
                url: base + '/customer_service/get_ai_history_list',
                pagination: true,
                sidePagination: 'server',
                pageSize: 15,
                pageList: [15, 30, 50],
                queryParams: function (params) {
                    var page = (params.offset != null) ? (Math.floor(Number(params.offset || 0) / Number(params.limit || 15)) + 1) : 1;
                    return { page: page, limit: params.limit, keyword: $('#search-keyword').val() || '' };
                },
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: Number(d.total || 0), rows: d.list || [] };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 80 },
                    { field: 'session_id', title: '会话ID', width: 220 },
                    { field: 'tenant_id', title: '租户ID', width: 80 },
                    { field: 'user_id', title: '用户ID', width: 80 },
                    { field: 'user_message', title: '用户问题', formatter: function (v) { return '<span title="' + (v || '') + '">' + shortText(v) + '</span>'; } },
                    { field: 'ai_response', title: 'AI回复', formatter: function (v) { return '<span title="' + (v || '') + '">' + shortText(v) + '</span>'; } },
                    { field: 'model', title: '模型', width: 140 },
                    { field: 'tokens_used', title: 'Token数', width: 90 },
                    { field: 'create_time', title: '创建时间', width: 180, formatter: fmtTime }
                ]
            });

            $(document).off('submit', '#search-form').on('submit', '#search-form', function (e) {
                e.preventDefault();
                $table.bootstrapTable('refresh', { pageNumber: 1 });
            });
            $(document).off('click', '.btn-reset').on('click', '.btn-reset', function () {
                var f = document.getElementById('search-form');
                if (f) f.reset();
                $table.bootstrapTable('refresh', { pageNumber: 1 });
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });
        }
    };

    Controller['ai_history'] = Controller.aihistory;
    Controller['aiHistory'] = Controller.aihistory;
    Controller['aihistory'] = Controller.aihistory;
    window.__backendController = Controller;
})();
