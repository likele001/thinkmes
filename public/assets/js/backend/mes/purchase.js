(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var requestUrl   = base + '/mes/purchase/request';
    var inboundUrl   = base + '/mes/purchase/inbound';
    var addInboundUrl = base + '/mes/purchase/addInbound';
    var editInboundUrl = base + '/mes/purchase/editInbound';
    var confirmUrl   = base + '/mes/purchase/confirmInbound';
    var auditUrl     = base + '/mes/purchase/auditRequest';
    var generateUrl  = base + '/mes/purchase/generateFromRequest';

    function reqStatusFmt(v) {
        var m = {
            0: '<span class="badge badge-warning">待审核</span>',
            1: '<span class="badge badge-info">已审核</span>',
            2: '<span class="badge badge-success">已采购</span>',
            3: '<span class="badge badge-danger">已取消</span>'
        };
        return m[v] || '未知';
    }

    function inStatusFmt(v) {
        var m = {
            1: '<span class="badge badge-warning" style="color:#333;">待入库</span>',
            2: '<span class="badge badge-success">已入库</span>',
            3: '<span class="badge badge-secondary">已取消</span>'
        };
        return m[parseInt(v, 10)] || '-';
    }

    function timeFmt(v) {
        if (!v) return '-';
        var n = parseInt(v, 10);
        if (!isNaN(n) && n > 0) return new Date(n > 1e12 ? n : n * 1000).toLocaleString('zh-CN');
        var d = new Date(String(v).replace(/-/g, '/'));
        return isNaN(d.getTime()) ? v : d.toLocaleString('zh-CN');
    }

    // 手动分页（非 bootstrapTable 分页）的通用渲染器
    function renderPagination(selector, current, total, loadFn) {
        var $ul = $(selector);
        $ul.empty();
        if (total <= 0) return;
        if (current > 1)
            $ul.append('<li class="page-item"><a class="page-link" data-page="' + (current - 1) + '">上一页</a></li>');
        var from = Math.max(1, current - 2), to = Math.min(total, current + 2);
        if (from > 1) {
            $ul.append('<li class="page-item"><a class="page-link" data-page="1">1</a></li>');
            if (from > 2) $ul.append('<li class="page-item disabled"><span class="page-link">…</span></li>');
        }
        for (var i = from; i <= to; i++) {
            if (i === current)
                $ul.append('<li class="page-item active"><span class="page-link">' + i + '</span></li>');
            else
                $ul.append('<li class="page-item"><a class="page-link" data-page="' + i + '">' + i + '</a></li>');
        }
        if (to < total) {
            if (to < total - 1) $ul.append('<li class="page-item disabled"><span class="page-link">…</span></li>');
            $ul.append('<li class="page-item"><a class="page-link" data-page="' + total + '">' + total + '</a></li>');
        }
        if (current < total)
            $ul.append('<li class="page-item"><a class="page-link" data-page="' + (current + 1) + '">下一页</a></li>');
        $ul.off('click', '.page-link[data-page]').on('click', '.page-link[data-page]', function (e) {
            e.preventDefault();
            var p = parseInt($(this).data('page'), 10);
            if (p) loadFn(p);
        });
    }

    var Controller = {

        // 采购申请管理
        request: function () {
            function loadData(page) {
                var status = $('#c-status').val();
                $.ajax({
                    url: requestUrl, type: 'GET',
                    data: { page: page, limit: 20, status: status },
                    dataType: 'json',
                    success: function (res) {
                        if (res.code !== 1) { alert(res.msg); return; }
                        var list = res.data.list || [];
                        var html = '';
                        list.forEach(function (item) {
                            var st = parseInt(item.status, 10);
                            var opHtml = '';
                            if (st === 0) {
                                opHtml = '<a href="javascript:;" class="btn btn-xs btn-primary btn-req-audit" data-id="' + item.id + '" data-action="1">通过</a> ' +
                                         '<a href="javascript:;" class="btn btn-xs btn-danger btn-req-audit" data-id="' + item.id + '" data-action="2">驳回</a>';
                            } else if (st === 1) {
                                opHtml = '<span class="text-muted">请到上方「生成入库单」勾选</span>';
                            } else {
                                opHtml = '-';
                            }
                            html += '<tr>' +
                                '<td><input type="checkbox" name="id" value="' + item.id + '" /></td>' +
                                '<td>' + (item.request_no || '') + '</td>' +
                                '<td>' + (item.material ? item.material.name : '') + '</td>' +
                                '<td>' + ((item.supplier && item.supplier.name) ? item.supplier.name : '未指定') + '</td>' +
                                '<td>' + (item.required_quantity != null ? item.required_quantity : (item.quantity || 0)) + '</td>' +
                                '<td>' + timeFmt(item.create_time) + '</td>' +
                                '<td>' + reqStatusFmt(st) + '</td>' +
                                '<td>' + opHtml + '</td></tr>';
                        });
                        $('#table tbody').html(html || '<tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>');
                        var total = res.data.total || 0;
                        renderPagination('#pagination', page, Math.max(1, Math.ceil(total / 20)), loadData);
                    }
                });
            }
            $(document).off('click', '.btn-req-audit').on('click', '.btn-req-audit', function () {
                var id = $(this).data('id'), action = $(this).data('action');
                if (!confirm('确定要' + (action == 1 ? '通过' : '驳回') + '该申请吗？')) return;
                $.post(auditUrl, { ids: id, status: action }, function (r) {
                    alert(r.msg);
                    if (r.code === 1) loadData(1);
                }, 'json');
            });
            $(document).off('click', '#checkall').on('click', '#checkall', function () {
                $('input[name="id"]').prop('checked', $(this).prop('checked'));
            });
            $(document).off('click', '#btn-generate-inbound').on('click', '#btn-generate-inbound', function () {
                location.href = base + '/mes/purchase/generateFromRequest';
            });
            $(document).off('submit', '#form-search').on('submit', '#form-search', function (e) {
                e.preventDefault(); loadData(1);
            });
            loadData(1);
        },

        // 从采购申请生成入库单
        generateFromRequest: function () {
            $(document).off('click', '#checkall').on('click', '#checkall', function () {
                $('input[name="request_ids[]"]').prop('checked', $(this).prop('checked'));
            });
            $(document).off('submit', '#form-generate').on('submit', '#form-generate', function (e) {
                e.preventDefault();
                if (!$('input[name="request_ids[]"]:checked').length) { alert('请至少勾选一条申请'); return; }
                $.post(generateUrl, $(this).serialize(), function (r) {
                    if (r.code == 1) {
                        alert(r.msg || '生成成功');
                        location.href = inboundUrl;
                    } else {
                        alert(r.msg || '生成失败');
                    }
                }, 'json');
            });
        },

        // 采购入库列表
        inbound: function () {
            function loadData(page) {
                var status = $('#c-status').val();
                $.get(inboundUrl, { page: page, limit: 20, status: status }, function (res) {
                    if (res.code !== 1) return;
                    var list = res.data.list || [];
                    var html = '';
                    list.forEach(function (item) {
                        var st = parseInt(item.status, 10);
                        var op = '';
                        if (st === 1) op = '<a href="javascript:;" class="btn btn-xs btn-success btn-confirm" data-id="' + item.id + '">确认入库</a> ';
                        op += '<a href="' + base + '/mes/purchase/viewInboundItems?id=' + item.id + '" class="btn btn-xs btn-info">查看明细</a>';
                        html += '<tr>' +
                            '<td><input type="checkbox" name="ids" value="' + item.id + '" data-status="' + st + '" /></td>' +
                            '<td>' + (item.inbound_no || '-') + '</td>' +
                            '<td>' + ((item.supplier && item.supplier.name) ? item.supplier.name : '-') + '</td>' +
                            '<td>' + (item.item_count || 0) + '</td>' +
                            '<td>' + (item.total_amount != null ? parseFloat(item.total_amount).toFixed(2) : '0.00') + '</td>' +
                            '<td>' + timeFmt(item.inbound_date != null ? item.inbound_date : item.create_time) + '</td>' +
                            '<td>' + inStatusFmt(st) + '</td>' +
                            '<td>' + op + '</td></tr>';
                    });
                    $('#table tbody').html(html || '<tr><td colspan="8" class="text-center text-muted">暂无数据</td></tr>');
                    renderPagination('#pagination', page, Math.max(1, Math.ceil((res.data.total || 0) / 20)), loadData);
                }, 'json');
            }
            $(document).off('click', '#checkall').on('click', '#checkall', function () {
                $('input[name="ids"]').prop('checked', $(this).prop('checked'));
            });
            $(document).off('click', '.btn-confirm').on('click', '.btn-confirm', function () {
                var id = $(this).data('id');
                if (!confirm('确认对该入库单执行入库（增加库存）？')) return;
                $.post(confirmUrl, { ids: id }, function (r) {
                    alert(r.msg || (r.code == 1 ? '成功' : '失败'));
                    if (r.code == 1) loadData(1);
                }, 'json');
            });
            $(document).off('click', '.btn-confirm-batch').on('click', '.btn-confirm-batch', function () {
                var ids = $('input[name="ids"]:checked').filter(function () { return $(this).data('status') == 1; }).map(function () { return this.value; }).get();
                if (!ids.length) { alert('请勾选待入库的单据'); return; }
                if (!confirm('确认对选中的 ' + ids.length + ' 张入库单执行入库？')) return;
                $.post(confirmUrl, { ids: ids.join(',') }, function (r) {
                    alert(r.msg || (r.code == 1 ? '成功' : '失败'));
                    if (r.code == 1) loadData(1);
                }, 'json');
            });
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () { loadData(1); });
            $(document).off('submit', '#form-search').on('submit', '#form-search', function (e) { e.preventDefault(); loadData(1); });
            loadData(1);
        },

        // 添加入库单
        addInbound: function () {
            $(document).off('change', '#c-purchase_request_id').on('change', '#c-purchase_request_id', function () {
                var mid = $(this).find('option:selected').data('material-id');
                if (mid) $('#c-material_id').val(mid);
            });
            // 从 JSON 数据岛读取预选物料
            try {
                var meta = document.getElementById('add-inbound-meta');
                if (meta) {
                    var d = JSON.parse(meta.textContent || '{}');
                    if (d.preselectMaterialId) $('#c-material_id').val(d.preselectMaterialId);
                }
            } catch (e) {}
            $(document).off('submit', '#form-add').on('submit', '#form-add', function (e) {
                e.preventDefault();
                $.post(addInboundUrl, $(this).serialize(), function (r) {
                    if (r.code == 1) {
                        if (typeof Fast !== 'undefined' && Fast.api) Fast.api.close(); else alert(r.msg);
                    } else {
                        alert(r.msg || '提交失败');
                    }
                }, 'json');
            });
        },

        // 编辑入库单
        editInbound: function () {
            var $form = $('#form-edit');
            var ids = $form.data('id') || '';
            $(document).off('submit', '#form-edit').on('submit', '#form-edit', function (e) {
                e.preventDefault();
                var data = $(this).serialize() + '&ids=' + encodeURIComponent(ids);
                $.post(editInboundUrl, data, function (r) {
                    if (r.code == 1) {
                        if (typeof Fast !== 'undefined' && Fast.api) Fast.api.close(); else alert(r.msg);
                    } else {
                        alert(r.msg || '保存失败');
                    }
                }, 'json');
            });
        },

        // 旧兼容别名：requestList → request
        requestList: function () { Controller.request(); },

        // actionname 全小写别名（backend-loader 用 strtolower 后调用）
        requestlist: function () { Controller.request(); },

        // index 别名：URL /mes/purchase/request/index 时 actionname=index
        index: function () { Controller.request(); },

        // 小写别名：驼峰方法经 strtolower 后的形式
        addinbound:           function () { Controller.addInbound(); },
        editinbound:          function () { Controller.editInbound(); },
        generatefromrequest:  function () { Controller.generateFromRequest(); },
        viewinbounditems:     function () { /* 纯服务端渲染，无需 JS 初始化 */ },
        auditrequest:         function () { /* 纯服务端渲染 */ },
        applypurchase:        function () { /* 纯服务端渲染 */ },
        applypurchaseone:     function () { /* 纯服务端渲染 */ }
    };

    window.__backendController = Controller;
})();
