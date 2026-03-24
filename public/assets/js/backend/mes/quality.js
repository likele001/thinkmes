(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var standardUrl    = base + '/mes/quality/standard';
    var checkUrl       = base + '/mes/quality/check';
    var addStandardUrl = base + '/mes/quality/addStandard';
    var addCheckUrl    = base + '/mes/quality/addCheck';
    var templatesUrl   = base + '/mes/quality/getTemplates';
    var copyUrl        = base + '/mes/quality/copyTemplate';

    function timeFmt(v) {
        if (!v) return '-';
        var n = parseInt(v, 10);
        return isNaN(n) || n <= 0 ? v : new Date(n > 1e12 ? n : n * 1000).toLocaleString('zh-CN');
    }

    var Controller = {

        // 质检标准列表
        standard: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
            $table.bootstrapTable({
                url: standardUrl,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                columns: [
                    {checkbox: true},
                    {field: 'code', title: '标准编号', align: 'center'},
                    {field: 'name', title: '标准名称'},
                    {field: 'process_id', title: '适用工序', align: 'center', formatter: function (v, row) { return row.process ? row.process.name : '-'; }},
                    {field: 'model_id', title: '适用型号', align: 'center', formatter: function (v, row) { return row.model ? row.model.name : '-'; }},
                    {field: 'check_items', title: '检查项数量', align: 'center', formatter: function (v) {
                        try { var a = JSON.parse(v); return Array.isArray(a) ? a.length : 0; } catch (e) { return 0; }
                    }},
                    {field: 'status', title: '状态', align: 'center', formatter: function (v) {
                        return v === 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-danger">禁用</span>';
                    }},
                    {field: 'create_time', title: '创建时间', align: 'center', formatter: timeFmt},
                    {field: 'id', title: '操作', align: 'center', formatter: function (v) {
                        return '<a href="' + addStandardUrl + '?id=' + v + '" class="btn btn-xs btn-primary"><i class="fas fa-edit"></i></a>';
                    }}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) || 0, rows: (res.data && res.data.list) || [] };
                }
            });
            // 行业模板列表
            $.get(templatesUrl, function (res) {
                if (res.code === 1 && res.data && res.data.list && res.data.list.length) {
                    var rows = res.data.list.map(function (row) {
                        var cnt = 0;
                        try { cnt = JSON.parse(row.check_items || '[]').length; } catch (e) {}
                        return '<tr><td>' + (row.name || '-') + '</td><td>' + cnt + '</td><td>' + (row.qualified_rate || 100) + '%</td>' +
                               '<td><button type="button" class="btn btn-xs btn-success btn-copy-template" data-id="' + row.id + '"><i class="fas fa-copy"></i> 复制</button></td></tr>';
                    });
                    $('#table-templates tbody').html(rows.join(''));
                }
            }, 'json');
            $(document).off('click', '.btn-copy-template').on('click', '.btn-copy-template', function () {
                var id = $(this).data('id'), $btn = $(this).prop('disabled', true);
                $.post(copyUrl, { template_id: id }, function (res) {
                    if (res.code === 1 && res.data && res.data.id) {
                        location.href = addStandardUrl + '?id=' + res.data.id;
                    } else {
                        $btn.prop('disabled', false);
                        alert(res.msg || '复制失败');
                    }
                }, 'json').fail(function () { $btn.prop('disabled', false); alert('请求失败'); });
            });
        },

        // 添加/编辑质检标准（同一页面）
        addStandard: function () {
            $(document).off('click', '#add-check-item').on('click', '#add-check-item', function () {
                var html = '<div class="check-item row mb-2">' +
                    '<div class="col-md-5"><input type="text" class="form-control" name="row[check_items][][name]" placeholder="检查项名称"></div>' +
                    '<div class="col-md-5"><input type="text" class="form-control" name="row[check_items][][standard]" placeholder="检查标准"></div>' +
                    '<div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-check-item"><i class="fas fa-minus"></i></button></div>' +
                    '</div>';
                $('#check-items-container').append(html);
            });
            $(document).off('click', '.remove-check-item').on('click', '.remove-check-item', function () {
                $(this).closest('.check-item').remove();
            });
            $(document).off('submit', '#form-standard').on('submit', '#form-standard', function (e) {
                var items = $('input[name="row[check_items][][name]"]');
                if (!items.length) { alert('请至少添加一个检查项'); e.preventDefault(); return false; }
                var valid = true;
                items.each(function () { if (!$(this).val().trim()) { valid = false; return false; } });
                if (!valid) { alert('请填写所有检查项名称'); e.preventDefault(); return false; }
                return true;
            });
        },

        // 质检记录列表
        check: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
            $table.bootstrapTable({
                url: checkUrl,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                columns: [
                    {checkbox: true},
                    {field: 'check_no', title: '质检编号', align: 'center'},
                    {field: 'report_id', title: '报工记录', align: 'center', formatter: function (v, row) { return row.report ? row.report.report_no : '-'; }},
                    {field: 'standard_id', title: '质检标准', align: 'center', formatter: function (v, row) { return row.standard ? row.standard.name : '-'; }},
                    {field: 'check_quantity', title: '质检数量', align: 'center'},
                    {field: 'qualified_quantity', title: '合格数量', align: 'center'},
                    {field: 'unqualified_quantity', title: '不合格数量', align: 'center'},
                    {field: 'qualified_rate', title: '合格率', align: 'center', formatter: function (v) { return v + '%'; }},
                    {field: 'check_time', title: '质检日期', align: 'center', formatter: timeFmt},
                    {field: 'status', title: '状态', align: 'center', formatter: function (v) {
                        var m = {1: '<span class="badge badge-success">合格</span>', 2: '<span class="badge badge-danger">不合格</span>'};
                        return m[v] || '<span class="badge badge-secondary">待质检</span>';
                    }},
                    {field: 'id', title: '操作', align: 'center', formatter: function (v, row) {
                        return '<a href="' + addCheckUrl + '?report_id=' + row.report_id + '" class="btn btn-xs btn-primary" title="详情"><i class="fas fa-eye"></i></a>';
                    }}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) || 0, rows: (res.data && res.data.list) || [] };
                }
            });
        },

        // 创建质检单
        addCheck: function () {
            var meta = document.getElementById('add-check-meta');
            var reportId = '';
            try { if (meta) reportId = JSON.parse(meta.textContent || '{}').reportId || ''; } catch (e) {}
            $(document).off('submit', '#form-add').on('submit', '#form-add', function (e) {
                e.preventDefault();
                $.post(addCheckUrl + '?report_id=' + reportId, $(this).serialize(), function (r) {
                    if (r.code == 1) {
                        if (typeof Fast !== 'undefined' && Fast.api) Fast.api.close(); else alert(r.msg);
                    } else {
                        alert(r.msg || '提交失败');
                    }
                }, 'json');
            });
        },

        // 质检统计（服务端渲染，仅绑定表单提交）
        statistics: function () {
            // 统计页面由服务端渲染表格，JS 只做简单表单增强
            $(document).off('submit', '#form-search').on('submit', '#form-search', function () {
                return true;
            });
        },

        // 小写别名：驼峰方法经 strtolower 后的形式
        addstandard: function () { Controller.addStandard(); },
        addcheck:    function () { Controller.addCheck(); }
    };

    window.__backendController = Controller;
})();
