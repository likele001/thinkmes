/**
 * 分工分配管理页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/allocation/index';
    var addUrl = base + '/mes/allocation/add';
    var editUrl = base + '/mes/allocation/edit';
    var delUrl = base + '/mes/allocation/del';
    var generateQrcodeUrl = base + '/mes/allocation/generateQrcode';
    var qrcodeInfoUrl = base + '/mes/allocation/qrcodeInfo';

    function statusFmt(v) {
        var statusMap = {0: '待开始', 1: '进行中', 2: '已完成'};
        var classMap = {0: 'secondary', 1: 'primary', 2: 'success'};
        return '<span class="badge badge-' + (classMap[v] || 'secondary') + '">' + (statusMap[v] || '未知') + '</span>';
    }

    function progressFmt(v) {
        return '<div class="progress" style="height: 20px;"><div class="progress-bar" role="progressbar" style="width: ' + v + '%;">' + v + '%</div></div>';
    }

    function operFmt(value, row) {
        return '<a href="' + editUrl + '?ids=' + row.id + '" class="btn btn-xs btn-success btn-edit">编辑</a> ' +
            '<a href="javascript:;" class="btn btn-xs btn-info btn-qrcode">二维码</a> ' +
            '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
    }

    function modelFmt(value, row) {
        var model = row.model || {};
        var name = model.name || '';
        var code = model.model_code || '';
        if (code) {
            return name + ' (' + code + ')';
        }
        return name;
    }

    function userFmt(value, row) {
        var u = row.user || {};
        return u.nickname || u.username || '-';
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }
            $table.bootstrapTable({
                url: indexUrl,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                columns: [
                    {checkbox: true},
                    {field: 'id', title: 'ID', width: 80, sortable: true},
                    {field: 'allocation_code', title: '分配编码', align: 'left'},
                    {field: 'order.order_no', title: '订单号', align: 'left'},
                    {field: 'model.product.name', title: '产品', align: 'left'},
                    {field: 'model.name', title: '产品型号', align: 'left', formatter: modelFmt},
                    {field: 'process.name', title: '工序', align: 'left'},
                    {field: 'user.nickname', title: '员工', align: 'left', formatter: userFmt},
                    {field: 'quantity', title: '分配数量', width: 100, align: 'right'},
                    {field: 'completed_quantity', title: '完成数量', width: 100, align: 'right'},
                    {field: 'completion_rate', title: '完成率', width: 120, formatter: progressFmt},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {field: 'operate', title: '操作', width: 200, events: {
                        'click .btn-edit': function(e, value, row) {
                            location.href = editUrl + '?ids=' + row.id;
                        },
                        'click .btn-del': function(e, value, row) {
                            if (confirm('确定要删除吗？')) {
                                $.post(delUrl, {ids: row.id}, function(r) {
                                    if (r.code == 1) {
                                        $table.bootstrapTable('refresh');
                                        alert(r.msg || '删除成功');
                                    } else {
                                        alert(r.msg || '删除失败');
                                    }
                                }, 'json');
                            }
                        },
                        'click .btn-qrcode': function(e, value, row) {
                            Controller.api.showQrcodeModal(row.id);
                        }
                    }, formatter: operFmt}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            
            // 刷新按钮
            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });
            // 二维码弹窗：复制链接
            $(document).off('click', '#qrcode-modal-copy').on('click', '#qrcode-modal-copy', function () {
                var $input = $('#qrcode-modal-url');
                $input.select();
                try {
                    document.execCommand('copy');
                    alert('已复制到剪贴板');
                } catch (e) {
                    alert('复制失败，请手动选择复制');
                }
            });
            // 二维码弹窗：重新生成
            $(document).off('click', '#qrcode-modal-regenerate').on('click', '#qrcode-modal-regenerate', function () {
                var id = $('#qrcodeModal').data('allocation-id');
                if (!id) return;
                if (!confirm('确定要重新生成二维码吗？')) return;
                $.post(generateQrcodeUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code == 1 ? '二维码生成成功' : '生成失败'));
                    if (r.code == 1) {
                        $('#qrcodeModal').modal('hide');
                        Controller.api.showQrcodeModal(id);
                    }
                }, 'json');
            });
            
            // 编辑按钮（工具栏，只绑定到工具栏的按钮，避免影响表格行的编辑按钮）
            $(document).off('click', '#toolbar .btn-edit').on('click', '#toolbar .btn-edit', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length != 1) {
                    alert('请选择一条记录');
                    return;
                }
                location.href = editUrl + '?ids=' + rows[0].id;
            });
            
            // 删除按钮（工具栏，只绑定到工具栏的按钮，避免影响表格行的删除按钮）
            $(document).off('click', '#toolbar .btn-del').on('click', '#toolbar .btn-del', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length == 0) {
                    alert('请选择要删除的记录');
                    return;
                }
                if (!confirm('确定要删除选中的 ' + rows.length + ' 条记录吗？')) {
                    return;
                }
                var ids = rows.map(function(r) { return r.id; });
                $.post(delUrl, {ids: ids.join(',')}, function(r) {
                    if (r.code == 1) {
                        $table.bootstrapTable('refresh');
                        alert(r.msg || '删除成功');
                    } else {
                        alert(r.msg || '删除失败');
                    }
                }, 'json');
            });
            
            // 表格行选择
            $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function() {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length > 0) {
                    $('.btn-edit, .btn-del').removeClass('disabled btn-disabled');
                } else {
                    $('.btn-edit, .btn-del').addClass('disabled btn-disabled');
                }
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        batch: function () {
            var allocationIndex = 0;
            var modelList = {};
            var processList = {};
            var userList = {};

            // 从 JSON 数据岛读取工序和员工列表
            try {
                var plEl = document.getElementById('batch-process-list');
                var ulEl = document.getElementById('batch-user-list');
                if (plEl) processList = JSON.parse(plEl.textContent || plEl.innerHTML || '{}');
                if (ulEl) userList = JSON.parse(ulEl.textContent || ulEl.innerHTML || '{}');
            } catch (e) {}

            // 订单选择变化时加载型号
            $('#order_id').on('change', function() {
                var orderId = $(this).val();
                if (!orderId) {
                    $('#allocation-container').hide();
                    return;
                }
                $.get(base + '/mes/allocation/getOrderModels', {order_id: orderId}, function(r) {
                    if (r.code == 1 && r.data) {
                        modelList = {};
                        $.each(r.data, function(i, item) {
                            modelList[item.id] = item.name;
                        });
                        $('#allocation-container').show();
                    } else {
                        alert('该订单暂无型号');
                        $('#allocation-container').hide();
                    }
                }, 'json');
            });

            var initialOrderId = $('#order_id').val();
            if (initialOrderId) { $('#order_id').trigger('change'); }

            function addAllocationRow() {
                var modelOptions = '<option value="">请选择型号</option>';
                $.each(modelList, function(id, name) {
                    modelOptions += '<option value="' + id + '">' + name + '</option>';
                });
                var processOptions = '<option value="">请选择工序</option>';
                $.each(processList, function(id, name) {
                    processOptions += '<option value="' + id + '">' + name + '</option>';
                });
                var userOptions = '<option value="">请选择员工</option>';
                $.each(userList, function(id, name) {
                    userOptions += '<option value="' + id + '">' + name + '</option>';
                });
                var html = '<tr data-index="' + allocationIndex + '">' +
                    '<td><select name="allocations[' + allocationIndex + '][model_id]" class="form-control form-control-sm" required>' + modelOptions + '</select></td>' +
                    '<td><select name="allocations[' + allocationIndex + '][process_id]" class="form-control form-control-sm" required>' + processOptions + '</select></td>' +
                    '<td><select name="allocations[' + allocationIndex + '][user_id]" class="form-control form-control-sm" required>' + userOptions + '</select></td>' +
                    '<td><input type="number" name="allocations[' + allocationIndex + '][quantity]" class="form-control form-control-sm" min="1" required></td>' +
                    '<td><button type="button" class="btn btn-sm btn-danger btn-remove-row">删除</button></td>' +
                    '</tr>';
                $('#allocation-tbody').append(html);
                allocationIndex++;
            }

            $('#btn-add-allocation').on('click', function() { addAllocationRow(); });
            $(document).on('click', '.btn-remove-row', function() { $(this).closest('tr').remove(); });

            $('#btn-submit-batch').off('click').on('click', function () {
                var allocations = [];
                $('#allocation-tbody tr').each(function() {
                    var modelId = $(this).find('select[name*="[model_id]"]').val();
                    var processId = $(this).find('select[name*="[process_id]"]').val();
                    var userId = $(this).find('select[name*="[user_id]"]').val();
                    var quantity = $(this).find('input[name*="[quantity]"]').val();
                    if (modelId && processId && userId && quantity) {
                        allocations.push({model_id: modelId, process_id: processId, user_id: userId, quantity: quantity});
                    }
                });
                if (!allocations.length) { alert('请至少添加一条分配记录'); return; }
                $.post(base + '/mes/allocation/batch', {
                    order_id: $('#order_id').val(),
                    plan_id: $('#plan_id').val(),
                    allocations: allocations
                }, function (r) {
                    if (r && r.msg) alert(r.msg);
                    if (r && r.code === 1) location.href = indexUrl;
                }, 'json').fail(function(xhr) {
                    try { var r = JSON.parse(xhr.responseText); alert(r.msg || '操作失败'); } catch(e) { alert('操作失败'); }
                });
            });
        },
        api: {
            showQrcodeModal: function (allocationId) {
                var $modal = $('#qrcodeModal');
                var $imgWrap = $('#qrcode-modal-img-wrap');
                var $url = $('#qrcode-modal-url');
                $imgWrap.html('<p class="text-muted">加载中...</p>');
                $url.val('');
                $modal.data('allocation-id', allocationId).modal('show');
                $.get(qrcodeInfoUrl, { id: allocationId }, function (r) {
                    if (r.code !== 1 || !r.data || !r.data.url) {
                        $imgWrap.html('<p class="text-danger">' + (r.msg || '获取二维码失败') + '</p>');
                        return;
                    }
                    var url = r.data.url;
                    $url.val(url);
                    var qrImgUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(url);
                    $imgWrap.html('<img src="' + qrImgUrl + '" alt="二维码" style="max-width:200px;height:auto;">');
                }, 'json').fail(function () {
                    $imgWrap.html('<p class="text-danger">请求失败</p>');
                });
            },
            bindevent: function () {
                var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
                var form = $('form#form-add, form#form-edit, form#form-batch');
                if (form.length) {
                    var formId = form.attr('id');
                    var action = form.attr('action');
                    if (!action) {
                        if (formId === 'form-add') {
                            action = base + '/mes/allocation/add';
                        } else if (formId === 'form-edit') {
                            action = base + '/mes/allocation/edit';
                        } else if (formId === 'form-batch') {
                            action = base + '/mes/allocation/batch';
                        }
                    }
                    form.attr('action', action);
                    form.on('submit', function (e) {
                        e.preventDefault();
                        var url = $(this).attr('action');
                        if (formId === 'form-edit' && url.indexOf('?') === -1) {
                            var id = $('input[name="row[id]"]').val();
                            if (id) url += '?ids=' + id;
                        }
                        $.post(url, $(this).serialize(), function (r) {
                            if (r && r.msg) {
                                alert(r.msg);
                            }
                            if (r && r.code === 1) {
                                location.href = base + '/mes/allocation/index';
                            }
                        }, 'json').fail(function(xhr) {
                            try {
                                var r = JSON.parse(xhr.responseText);
                                alert(r.msg || '操作失败');
                            } catch(e) {
                                alert('操作失败');
                            }
                        });
                    });
                }
            }
        }
    };

    // 小写别名：驼峰方法经 strtolower 后的形式
    Controller.getordermodels  = function () { /* AJAX 接口，无视图 */ };
    Controller.generateqrcode  = function () { /* AJAX 接口 */ };
    Controller.qrcodeinfo      = function () { /* 服务端渲染 */ };

    window.__backendController = Controller;
})();
