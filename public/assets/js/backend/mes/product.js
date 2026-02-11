/**
 * 产品管理页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/product/index';
    var addUrl = base + '/mes/product/add';
    var editUrl = base + '/mes/product/edit';
    var delUrl = base + '/mes/product/del';

    function statusFmt(v) { 
        return v == 1 ? '<span class="badge badge-success">正常</span>' : '<span class="badge badge-danger">禁用</span>'; 
    }

    function operFmt(value, row) {
        return '<a href="' + editUrl + '?ids=' + row.id + '" class="btn btn-xs btn-success btn-edit">编辑</a> ' +
            '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>';
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
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
                    {field: 'name', title: '产品名称', align: 'left'},
                    {field: 'code', title: '产品编码', align: 'left'},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {field: 'create_time', title: '创建时间', width: 180, formatter: function(value) {
                        return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                    }},
                    {field: 'operate', title: '操作', width: 150, events: {
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
            Controller.api.initModelForm();
        },
        edit: function () {
            Controller.api.bindevent();
            Controller.api.initModelForm();
        },
        api: {
            bindevent: function () {
                var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
                var form = $('form#form-add, form#form-edit');
                if (form.length) {
                    var action = form.attr('action') || (form.attr('id') === 'form-add' ? (base + '/mes/product/add') : (base + '/mes/product/edit'));
                    form.attr('action', action);
                    form.on('submit', function (e) {
                        e.preventDefault();
                        var url = $(this).attr('action');
                        if (url.indexOf('?ids=') === -1 && form.attr('id') === 'form-edit') {
                            var id = $('input[name="row[id]"]').val();
                            if (id) url += '?ids=' + id;
                        }
                        $.post(url, $(this).serialize(), function (r) {
                            if (r && r.msg) {
                                alert(r.msg);
                            }
                            if (r && r.code === 1) {
                                location.href = base + '/mes/product/index';
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
            },
            initModelForm: function() {
                // 获取工序数据（从页面中读取）
                var processList = [];
                try {
                    var processData = $('#process-data');
                    if (processData.length && processData.text()) {
                        processList = JSON.parse(processData.text());
                    }
                } catch(e) {
                    console.error('解析工序数据失败:', e);
                }
                
                var modelIndex = $('#model-container .model-item').length;
                
                // 编辑页面：渲染现有型号数据
                if ($('#model-container').attr('data-models')) {
                    try {
                        var modelsData = JSON.parse($('#model-container').attr('data-models'));
                        modelIndex = 0;
                        modelsData.forEach(function(modelData) {
                            var model = modelData.model || {};
                            var prices = modelData.prices || {};
                            Controller.api.renderModel(modelIndex, model, prices, processList);
                            modelIndex++;
                        });
                    } catch(e) {
                        console.error('解析型号数据失败:', e);
                    }
                }
                
                // 如果没有型号，添加一个空型号
                if (modelIndex === 0) {
                    Controller.api.renderModel(0, {}, {}, processList);
                    modelIndex = 1;
                }
                
                // 添加型号按钮
                $(document).off('click', '#add-model').on('click', '#add-model', function() {
                    Controller.api.renderModel(modelIndex, {}, {}, processList);
                    modelIndex++;
                });
                
                // 删除型号按钮
                $(document).off('click', '.remove-model').on('click', '.remove-model', function() {
                    if ($('#model-container .model-item').length > 1) {
                        $(this).closest('.model-item').remove();
                    } else {
                        alert('至少保留一个型号');
                    }
                });
            },
            renderModel: function(index, model, prices, processList) {
                var html = '<div class="model-item" data-index="' + index + '" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">' +
                    '<h5 style="margin-bottom: 15px; color: #333;">型号信息</h5>' +
                    '<div class="row" style="margin-bottom: 15px;">' +
                    '<div class="col-md-4"><label>型号名称:</label><input type="text" class="form-control" name="models[' + index + '][name]" value="' + (model.name || '') + '" placeholder="请输入型号名称"></div>' +
                    '<div class="col-md-3"><label>型号编码:</label><input type="text" class="form-control" name="models[' + index + '][model_code]" value="' + (model.model_code || '') + '" placeholder="请输入型号编码"></div>' +
                    '<div class="col-md-3"><label>型号描述:</label><input type="text" class="form-control" name="models[' + index + '][description]" value="' + (model.description || '') + '" placeholder="请输入型号描述"></div>' +
                    '<div class="col-md-2"><label>&nbsp;</label><br><button type="button" class="btn btn-danger btn-sm remove-model">删除</button></div>' +
                    '</div>' +
                    '<h6 style="margin-bottom: 10px; color: #666;">工序工价设置</h6>' +
                    '<div class="price-table"><table class="table table-bordered table-hover"><thead><tr><th>工序</th><th>计件工价(元/件)</th><th>计时工价(元/小时)</th></tr></thead><tbody>';
                
                processList.forEach(function(process) {
                    var price = prices[process.id] ? (prices[process.id].price || '') : '';
                    var timePrice = prices[process.id] ? (prices[process.id].time_price || '') : '';
                    html += '<tr>' +
                        '<td>' + process.name + '</td>' +
                        '<td><input type="number" class="form-control" name="prices[' + process.id + '][' + index + ']" value="' + price + '" placeholder="0.00" step="0.01" min="0"></td>' +
                        '<td><input type="number" class="form-control" name="prices[' + process.id + '_time][' + index + ']" value="' + timePrice + '" placeholder="0.00" step="0.01" min="0"></td>' +
                        '</tr>';
                });
                
                html += '</tbody></table></div></div>';
                $('#model-container').append(html);
            }
        }
    };

    window.__backendController = Controller;
})();
