(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/order/index';
    var processDetailUrl = base + '/mes/order/processDetail';
    var addUrl = base + '/mes/order/add';
    var editUrl = base + '/mes/order/edit';
    var delUrl = base + '/mes/order/del';
    var materialListUrl = base + '/mes/order/materialList';

    function fmtTime(v) {
        if (!v) return '';
        var n = Number(v);
        if (!isNaN(n) && isFinite(n)) return new Date((n > 1e12 ? n : n * 1000)).toLocaleString('zh-CN');
        var d = new Date(String(v).trim().replace(' ', 'T'));
        return isNaN(d.getTime()) ? v : d.toLocaleString('zh-CN');
    }

    // 型号 select 列表缓存（add/edit 页共用）
    function cacheModelOptions() {
        var opts = '';
        $('#model-list select:first option').each(function () {
            if ($(this).val() !== '') {
                opts += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
            }
        });
        return opts;
    }

    // 动态型号行绑定
    function bindModelRows(modelOptions) {
        $(document).off('click', '#btn-add-model').on('click', '#btn-add-model', function () {
            var idx = $('#model-list .model-item').length;
            var html = '<div class="model-item mb-2">' +
                '<select name="models[' + idx + '][model_id]" class="form-control d-inline-block" style="width:300px;">' +
                '<option value="">请选择型号</option>' + modelOptions + '</select>' +
                '<input type="number" name="models[' + idx + '][quantity]" class="form-control d-inline-block" style="width:150px;margin-left:10px;" placeholder="数量" min="1" value="1">' +
                '<button type="button" class="btn btn-danger btn-sm ml-2 btn-remove-model">删除</button>' +
                '</div>';
            $('#model-list').append(html);
            updateRemoveBtns();
        });
        $(document).off('click', '.btn-remove-model').on('click', '.btn-remove-model', function () {
            $(this).closest('.model-item').remove();
            updateRemoveBtns();
        });
        updateRemoveBtns();
    }

    function updateRemoveBtns() {
        var count = $('.model-item').length;
        $('.btn-remove-model').toggle(count > 1);
    }

    // 收集型号列表
    function collectModels() {
        var models = [];
        $('#model-list .model-item').each(function () {
            var mid = $(this).find('select[name*="[model_id]"]').val();
            var qty = parseInt($(this).find('input[name*="[quantity]"]').val(), 10) || 0;
            if (mid) {
                if (qty <= 0) { qty = 1; $(this).find('input[name*="[quantity]"]').val(qty); }
                models.push({ model_id: mid, quantity: qty });
            }
        });
        return models;
    }

    // 表单提交（add / edit 共用，url 不同）
    function bindFormSubmit(formId, url, backUrl) {
        $(document).off('submit', '#' + formId).on('submit', '#' + formId, function (e) {
            e.preventDefault();
            var models = collectModels();
            if (models.length === 0) { alert('请至少添加一个订单型号'); return; }
            var fd = new FormData();
            $(this).find('[name^="row["]').each(function () {
                var key = $(this).attr('name').match(/row\[(.*?)\]/)[1];
                fd.append('row[' + key + ']', $(this).val());
            });
            models.forEach(function (m, i) {
                fd.append('models[' + i + '][model_id]', m.model_id);
                fd.append('models[' + i + '][quantity]', m.quantity);
            });
            $.ajax({ url: url, type: 'POST', dataType: 'json', data: fd, processData: false, contentType: false })
                .done(function (r) {
                    alert(r.msg || (r.code === 1 ? '操作成功' : '操作失败'));
                    if (r.code === 1) location.href = backUrl;
                })
                .fail(function (xhr) {
                    var msg = '请求失败';
                    try { msg = JSON.parse(xhr.responseText).msg || msg; } catch (e) {}
                    alert(msg);
                });
        });
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
            $table.bootstrapTable({
                url: indexUrl,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                queryParams: function (params) {
                    return {
                        limit: params.limit,
                        offset: params.offset,
                        page: params.page,
                        workflow_status_filter: $('#workflowStatusFilter').val() || ''
                    };
                },
                responseHandler: function (res) {
                    var data = res.data || {};
                    return { total: data.total || 0, rows: data.list || [] };
                },
                columns: [
                    {checkbox: true},
                    {field: 'id', title: 'ID', width: 80, sortable: true},
                    {field: 'order_no', title: '订单号', align: 'left'},
                    {field: 'order_name', title: '订单名称', align: 'left'},
                    {field: 'customer_name', title: '客户名称', align: 'left'},
                    {field: 'total_quantity', title: '总数量', width: 100, align: 'right'},
                    {field: 'workflow_status', title: '流程状态', width: 160, formatter: function (value) {
                        var label = '未启动';
                        var cls = 'secondary';
                        if (value === '进行中') { label = '进行中'; cls = 'warning'; }
                        else if (value === '已完成') { label = '已完成'; cls = 'success'; }
                        else if (value === '已启动') { label = '已启动'; cls = 'info'; }
                        return '<span class="badge badge-' + cls + ' badge-pill" style="font-size:0.96rem;padding:0.5em 0.85em;min-width:72px;display:inline-block;text-align:center;">' + label + '</span>';
                    }},
                    {field: 'status', title: '状态', width: 100, formatter: function (value) {
                        var statusMap = {0: '待生产', 1: '生产中', 2: '已完成', 3: '已取消'};
                        var classMap = {0: 'secondary', 1: 'primary', 2: 'success', 3: 'danger'};
                        return '<span class="badge badge-' + (classMap[value] || 'secondary') + '">' + (statusMap[value] || '未知') + '</span>';
                    }},
                    {field: 'delivery_time', title: '交货时间', width: 180, formatter: fmtTime},
                    {field: 'create_time', title: '创建时间', width: 180, formatter: fmtTime},
                    {field: 'operate', title: '操作', width: 320, events: {
                        'click .btn-edit': function (e, value, row) { location.href = editUrl + '?ids=' + row.id; },
                        'click .btn-material': function (e, value, row) { location.href = materialListUrl + '?ids=' + row.id; },
                        'click .btn-process': function (e, value, row) { location.href = processDetailUrl + '?order_id=' + row.id; },
                        'click .btn-del': function (e, value, row) {
                            if (!confirm('确定要删除吗？')) return;
                            $.post(delUrl, {ids: row.id}, function (r) {
                                if (r.code == 1) { $table.bootstrapTable('refresh'); }
                                alert(r.msg || (r.code == 1 ? '删除成功' : '删除失败'));
                            }, 'json');
                        },
                        'click .btn-workflow': function (e, value, row) {
                            if (row.workflow_instance_id) {
                                location.href = base + '/workflow/instanceDetail?id=' + row.workflow_instance_id;
                            }
                        }
                    }, formatter: function (value, row) {
                        var html = '<a href="' + editUrl + '?ids=' + row.id + '" class="btn btn-xs btn-success btn-edit">编辑</a> ' +
                                   '<a href="' + materialListUrl + '?ids=' + row.id + '" class="btn btn-xs btn-info btn-material">物料</a> ' +
                                   '<a href="' + processDetailUrl + '?order_id=' + row.id + '" class="btn btn-xs btn-primary btn-process">工序进度</a> ';
                        if (row.workflow_instance_id) {
                            html += '<a href="javascript:;" class="btn btn-xs btn-success btn-workflow" title="查看流程详情">流程详情</a> ';
                        } else {
                            html += '<a href="javascript:;" class="btn btn-xs btn-outline-secondary disabled" title="该订单尚未启动工作流">流程详情</a> ';
                        }
                        html += '<a href="javascript:;" class="btn btn-xs btn-danger btn-del">删除</a>';
                        return html;
                    }}
                ]
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('change', '#workflowStatusFilter').on('change', '#workflowStatusFilter', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#toolbar .btn-edit').on('click', '#toolbar .btn-edit', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length !== 1) { alert('请选择一条记录'); return; }
                location.href = editUrl + '?ids=' + rows[0].id;
            });
            $(document).off('click', '#toolbar .btn-del').on('click', '#toolbar .btn-del', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (!rows.length) { alert('请选择要删除的记录'); return; }
                if (!confirm('确定要删除选中的 ' + rows.length + ' 条记录吗？')) return;
                var ids = rows.map(function (r) { return r.id; });
                $.post(delUrl, {ids: ids.join(',')}, function (r) {
                    if (r.code == 1) { $table.bootstrapTable('refresh'); }
                    alert(r.msg || (r.code == 1 ? '删除成功' : '删除失败'));
                }, 'json');
            });
            $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
                var rows = $table.bootstrapTable('getSelections');
                $('.btn-edit, .btn-del').toggleClass('disabled btn-disabled', rows.length === 0);
            });
        },

        add: function () {
            var modelOptions = cacheModelOptions();
            bindModelRows(modelOptions);
            bindFormSubmit('form-add', addUrl, base + '/mes/order/index');
        },

        edit: function () {
            var modelOptions = cacheModelOptions();
            bindModelRows(modelOptions);
            var $form = $('#form-edit');
            var rowId = $form.data('id') || ($form.find('[name="row[id]"]').val() || 0);
            bindFormSubmit('form-edit', editUrl + '?ids=' + rowId, base + '/mes/order/index');
        },

        'import': function () {
            var $form = $('#form-import');
            var backUrl = base + '/mes/order/index';
            $(document).off('submit', '#form-import').on('submit', '#form-import', function (e) {
                e.preventDefault();
                var fd = new FormData(this);
                var xhr = new XMLHttpRequest();
                xhr.open('POST', addUrl.replace('/add', '/import'));
                xhr.onload = function () {
                    var r;
                    try { r = JSON.parse(xhr.responseText); } catch (err) { alert('响应解析失败'); return; }
                    if (r.msg) alert(r.msg);
                    if (r.code === 1 && confirm('导入成功，是否前往订单列表？')) {
                        location.href = backUrl;
                    }
                };
                xhr.send(fd);
            });
        },

        materialList: function () {
            // 静态表格由视图服务端渲染，此处处理 URL toast 提示
            var m = /[?&]toast=([^&]+)/.exec(location.href);
            if (m && m[1]) {
                try {
                    var msg = decodeURIComponent(m[1]);
                    if (msg) {
                        if (typeof layui !== 'undefined' && layui.msg) layui.msg(msg);
                        else alert(msg);
                    }
                } catch (e) {}
                var id = (location.search.match(/[?&]id=(\d+)/) || [])[1] || '';
                history.replaceState(null, '', location.pathname + (id ? '?id=' + id : ''));
            }
        },

        // 小写别名：驼峰方法经 strtolower 后的形式
        materiallist:        function () { Controller.materialList(); },
        orderprogress:       function () { /* 服务端渲染 */ },
        orderprocessdetail:  function () { /* 服务端渲染 */ },
        applypurchase:       function () { /* 服务端渲染 */ },
        applypurchaseone:    function () { /* 服务端渲染 */ },
        downloadtemplate:    function () { /* 触发下载，无 JS 初始化 */ }
    };

    window.__backendController = Controller;
})();
