/**
 * 工作流-流程定义
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/workflow/definition/index';
    var addUrl = base + '/workflow/definition/add';
    var editUrl = base + '/workflow/definition/edit';
    var designerUrl = base + '/workflow/definition/designer';
    var adminOptionsUrl = base + '/workflow/approval/adminOptions';
    var saveNodesUrl = base + '/workflow/definition/saveNodes';
    var delUrl = base + '/workflow/definition/del';
    var toggleUrl = base + '/workflow/definition/toggle';

    function statusBadge(value) {
        return value == 1
            ? '<span class="badge badge-success">启用</span>'
            : '<span class="badge badge-secondary">禁用</span>';
    }

    function getSelectedIds(table) {
        return $.map(table.bootstrapTable('getSelections'), function (row) {
            return row.id;
        });
    }

    function parseJsonArray(value) {
        if (Array.isArray(value)) return value;
        if (typeof value !== 'string' || value === '') return [];
        try {
            var parsed = JSON.parse(value);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    var Controller = {
        index: function () {
            var table = $('#table');
            if (!table.length) return;

            table.bootstrapTable({
                url: indexUrl,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                queryParams: function (params) {
                    return {
                        limit: params.limit,
                        offset: params.offset,
                        page: params.page,
                        keyword: $('#keyword').val() || '',
                        module_code: $('#moduleCode').val() || ''
                    };
                },
                responseHandler: function (res) {
                    var data = res.data || {};
                    return {
                        total: data.total || 0,
                        rows: data.list || []
                    };
                },
                columns: [
                    { checkbox: true },
                    { field: 'id', title: 'ID', width: 70 },
                    { field: 'name', title: '流程名称' },
                    { field: 'module_name', title: '所属模块', width: 160 },
                    { field: 'status', title: '状态', width: 100, formatter: statusBadge },
                    { field: 'remark', title: '备注' },
                    { field: 'create_time', title: '创建时间', width: 170, formatter: function (value) {
                        if (!value) return '';
                        return new Date(value * 1000).toLocaleString('zh-CN');
                    }},
                    {
                        field: 'operate',
                        title: '操作',
                        width: 260,
                        formatter: function (value, row) {
                            var toggleText = row.status == 1 ? '禁用' : '启用';
                            return [
                                '<a href="' + editUrl + '?id=' + row.id + '" class="btn btn-xs btn-primary">编辑</a>',
                                '<a href="' + designerUrl + '?id=' + row.id + '" class="btn btn-xs btn-info">节点配置</a>',
                                '<a href="javascript:;" class="btn btn-xs btn-warning btn-toggle" data-id="' + row.id + '" data-status="' + (row.status == 1 ? 0 : 1) + '">' + toggleText + '</a>',
                                '<a href="javascript:;" class="btn btn-xs btn-danger btn-del" data-id="' + row.id + '">删除</a>'
                            ].join(' ');
                        }
                    }
                ]
            });

            $('#toolbar .btn-search').off('click').on('click', function () {
                table.bootstrapTable('refresh', {pageNumber: 1});
            });

            $('#toolbar .btn-reset').off('click').on('click', function () {
                $('#keyword').val('');
                $('#moduleCode').val('');
                table.bootstrapTable('refresh', {pageNumber: 1});
            });

            $('#toolbar .btn-refresh').off('click').on('click', function () {
                table.bootstrapTable('refresh');
            });

            $('#toolbar .btn-add').off('click').on('click', function () {
                location.href = addUrl;
            });

            $('#toolbar .btn-edit').off('click').on('click', function () {
                var ids = getSelectedIds(table);
                if (ids.length !== 1) {
                    Toastr.warning('请选择一条记录进行编辑');
                    return;
                }
                location.href = editUrl + '?id=' + ids[0];
            });

            $('#toolbar .btn-del').off('click').on('click', function () {
                var ids = getSelectedIds(table);
                if (ids.length !== 1) {
                    Toastr.warning('请选择一条记录进行删除');
                    return;
                }
                if (!confirm('确定删除该流程定义吗？')) return;
                $.post(delUrl, { id: ids[0] }, function (res) {
                    if (res.code === 1) {
                        Toastr.success(res.msg || '删除成功');
                        table.bootstrapTable('refresh');
                    } else {
                        Toastr.error(res.msg || '删除失败');
                    }
                }, 'json');
            });

            table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
                var rows = table.bootstrapTable('getSelections');
                $('.btn-edit, .btn-del').toggleClass('disabled btn-disabled', rows.length === 0);
            });

            $(document).off('click.workflow.definition');
            $(document).on('click.workflow.definition', '.btn-del', function () {
                var id = $(this).data('id');
                if (!confirm('确定删除此流程？')) return;
                $.post(delUrl, { id: id }, function (res) {
                    if (res.code === 1) {
                        Toastr.success(res.msg || '删除成功');
                        table.bootstrapTable('refresh');
                    } else {
                        Toastr.error(res.msg || '删除失败');
                    }
                }, 'json');
            });
            $(document).on('click.workflow.definition', '.btn-toggle', function () {
                var id = $(this).data('id');
                var status = $(this).data('status');
                $.post(toggleUrl, { id: id, status: status }, function (res) {
                    if (res.code === 1) {
                        Toastr.success(res.msg || '操作成功');
                        table.bootstrapTable('refresh');
                    } else {
                        Toastr.error(res.msg || '操作失败');
                    }
                }, 'json');
            });
        },
        add: function () {
            var form = $('#form-add');
            if (!form.length) return;
            form.off('submit').on('submit', function (e) {
                e.preventDefault();
                $.post(addUrl, form.serialize(), function (res) {
                    if (res.code === 1) {
                        Toastr.success(res.msg || '保存成功');
                        setTimeout(function () {
                            location.href = indexUrl;
                        }, 600);
                    } else {
                        Toastr.error(res.msg || '保存失败');
                    }
                }, 'json').fail(function () {
                    Toastr.error('保存失败');
                });
            });
        },
        edit: function () {
            var form = $('#form-edit');
            if (!form.length) return;
            form.off('submit').on('submit', function (e) {
                e.preventDefault();
                $.post(editUrl, form.serialize(), function (res) {
                    if (res.code === 1) {
                        Toastr.success(res.msg || '保存成功');
                        setTimeout(function () {
                            location.href = indexUrl;
                        }, 600);
                    } else {
                        Toastr.error(res.msg || '保存失败');
                    }
                }, 'json').fail(function () {
                    Toastr.error('保存失败');
                });
            });

            $('.btn-designer').off('click').on('click', function () {
                var id = parseInt(form.find('[name="id"]').val(), 10) || 0;
                if (id <= 0) {
                    Toastr.error('流程ID无效，无法进入节点配置');
                    return;
                }
                location.href = designerUrl + '?id=' + id;
            });
        },
        designer: function () {
            var definitionId = parseInt($('#definitionId').val(), 10) || 0;
            if (definitionId <= 0) return;

            var nodes = [];
            var admins = [];

            function getAdminName(id) {
                for (var i = 0; i < admins.length; i++) {
                    if (parseInt(admins[i].id, 10) === parseInt(id, 10)) {
                        return admins[i].name || ('ID:' + id);
                    }
                }
                return 'ID:' + id;
            }

            function renderNodes() {
                var html = '';
                for (var i = 0; i < nodes.length; i++) {
                    var node = nodes[i] || {};
                    var approverTypeMap = {
                        admin: '管理员指定',
                        role: '角色指定',
                        dept_manager: '部门负责人',
                        initiator_select: '发起人自选'
                    };
                    var approvalModeText = node.approval_mode === 'countersign' ? '会签（需全部通过）' : '或签（任一通过）';
                    var approverIds = parseJsonArray(node.approver_ids);
                    var approverNames = [];
                    for (var j = 0; j < approverIds.length; j++) {
                        approverNames.push(getAdminName(approverIds[j]));
                    }
                    var conditionItems = parseJsonArray(node.condition_items);
                    var conditionText = '';
                    if (conditionItems.length) {
                        var parts = [];
                        for (var k = 0; k < conditionItems.length; k++) {
                            var item = conditionItems[k] || {};
                            parts.push((item.field || '') + ' ' + (item.operator || '') + ' ' + (item.value || ''));
                        }
                        conditionText = parts.join(' ' + (node.condition_logic || 'AND') + ' ');
                    }

                    html += '<div class="node-item" data-index="' + i + '" draggable="true">';
                    html += '<div class="node-header">';
                    html += '<span class="node-sort">节点' + (i + 1) + '</span>';
                    html += '<div>';
                    html += '<button class="btn btn-xs btn-primary btn-edit-node"><i class="layui-icon layui-icon-edit"></i> 编辑</button> ';
                    html += '<button class="btn btn-xs btn-danger btn-del-node"><i class="layui-icon layui-icon-delete"></i> 删除</button>';
                    html += '</div></div>';
                    html += '<div><div><strong>节点名称：</strong>' + escapeHtml(node.name || '') + '</div>';
                    html += '<div><strong>审批人类型：</strong>' + escapeHtml(approverTypeMap[node.approver_type] || node.approver_type || '') + '</div>';
                    if (node.approver_type === 'admin' && approverNames.length) {
                        html += '<div><strong>指定审批人：</strong>' + escapeHtml(approverNames.join('、')) + '</div>';
                    }
                    html += '<div><strong>审批方式：</strong>' + escapeHtml(approvalModeText) + '</div>';
                    html += '<div><strong>触发条件：</strong>' + escapeHtml(conditionText || '无条件（始终触发）') + '</div>';
                    html += '</div></div>';
                }
                $('#nodeList').html(html);
                bindNodeEvents();
            }

            function normalizeNodeSort() {
                for (var i = 0; i < nodes.length; i++) {
                    nodes[i].sort = i + 1;
                }
            }

            function getConditionHtml(item) {
                item = item || {};
                return '<div class="condition-item">' +
                    '<div class="d-flex align-items-center" style="gap:8px;">' +
                    '<select class="form-control form-control-sm" name="condition_field[]" style="width:150px;">' +
                    '<option value="">字段名</option>' +
                    '<option value="amount" ' + (item.field === 'amount' ? 'selected' : '') + '>金额</option>' +
                    '<option value="days" ' + (item.field === 'days' ? 'selected' : '') + '>天数</option>' +
                    '<option value="level" ' + (item.field === 'level' ? 'selected' : '') + '>级别</option>' +
                    '</select>' +
                    '<select class="form-control form-control-sm" name="condition_operator[]" style="width:120px;">' +
                    '<option value="gt" ' + (item.operator === 'gt' ? 'selected' : '') + '>大于</option>' +
                    '<option value="lt" ' + (item.operator === 'lt' ? 'selected' : '') + '>小于</option>' +
                    '<option value="eq" ' + (item.operator === 'eq' ? 'selected' : '') + '>等于</option>' +
                    '<option value="neq" ' + (item.operator === 'neq' ? 'selected' : '') + '>不等于</option>' +
                    '<option value="contains" ' + (item.operator === 'contains' ? 'selected' : '') + '>包含</option>' +
                    '<option value="not_contains" ' + (item.operator === 'not_contains' ? 'selected' : '') + '>不包含</option>' +
                    '</select>' +
                    '<input type="text" class="form-control form-control-sm" name="condition_value[]" value="' + escapeHtml(item.value || '') + '" placeholder="比较值" style="width:120px;">' +
                    '<button type="button" class="btn btn-sm btn-danger remove-condition"><i class="layui-icon layui-icon-close"></i></button>' +
                    '</div></div>';
            }

            function showNodeDialog(node, index) {
                var approverIds = parseJsonArray(node.approver_ids);
                var conditionItems = parseJsonArray(node.condition_items);
                var adminOptionsHtml = '';
                for (var i = 0; i < admins.length; i++) {
                    var adminId = parseInt(admins[i].id, 10);
                    var checked = approverIds.indexOf(adminId) > -1 ? 'checked' : '';
                    adminOptionsHtml += '<div class="form-check"><input class="form-check-input" type="checkbox" name="admin_ids[]" value="' + adminId + '" ' + checked + '> <label class="form-check-label">' + escapeHtml(admins[i].name || ('ID:' + adminId)) + '</label></div>';
                }
                var conditionHtml = '';
                for (var j = 0; j < conditionItems.length; j++) {
                    conditionHtml += getConditionHtml(conditionItems[j]);
                }

                var dialogHtml = '' +
                    '<div style="padding:20px;max-height:520px;overflow-y:auto;">' +
                    '<form id="node-form">' +
                    '<div class="form-group"><label>节点名称 <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" value="' + escapeHtml(node.name || '') + '" required></div>' +
                    '<div class="form-group"><label>审批人类型</label>' +
                    '<div>' +
                    '<label class="mr-2"><input type="radio" class="approver-type" name="approver_type" value="admin" ' + (node.approver_type === 'admin' ? 'checked' : '') + '> 管理员指定</label>' +
                    '<label class="mr-2"><input type="radio" class="approver-type" name="approver_type" value="role" ' + (node.approver_type === 'role' ? 'checked' : '') + '> 角色指定</label>' +
                    '<label class="mr-2"><input type="radio" class="approver-type" name="approver_type" value="dept_manager" ' + (node.approver_type === 'dept_manager' ? 'checked' : '') + '> 部门负责人</label>' +
                    '<label><input type="radio" class="approver-type" name="approver_type" value="initiator_select" ' + (node.approver_type === 'initiator_select' ? 'checked' : '') + '> 发起人自选</label>' +
                    '</div></div>' +
                    '<div class="form-group admin-selector" style="display:' + (node.approver_type === 'admin' ? 'block' : 'none') + '"><label>选择审批人</label><div>' + adminOptionsHtml + '</div></div>' +
                    '<div class="form-group"><label>审批方式</label>' +
                    '<label class="mr-2"><input type="radio" name="approval_mode" value="any_sign" ' + (node.approval_mode !== 'countersign' ? 'checked' : '') + '> 或签</label>' +
                    '<label><input type="radio" name="approval_mode" value="countersign" ' + (node.approval_mode === 'countersign' ? 'checked' : '') + '> 会签</label></div>' +
                    '<div class="form-group"><label>条件逻辑</label>' +
                    '<label class="mr-2"><input type="radio" name="condition_logic" value="AND" ' + (node.condition_logic !== 'OR' ? 'checked' : '') + '> AND</label>' +
                    '<label><input type="radio" name="condition_logic" value="OR" ' + (node.condition_logic === 'OR' ? 'checked' : '') + '> OR</label></div>' +
                    '<div class="form-group"><label>触发条件</label> <button type="button" class="btn btn-xs btn-outline-primary" id="btnAddCondition"><i class="layui-icon layui-icon-addition"></i> 添加条件</button></div>' +
                    '<div id="conditionList">' + conditionHtml + '</div>' +
                    '<div class="mt-3"><button type="submit" class="btn btn-primary">确定</button> <button type="button" class="btn btn-secondary" onclick="layer.closeAll()">取消</button></div>' +
                    '</form></div>';

                layer.open({
                    type: 1,
                    title: '配置节点',
                    area: ['680px', '640px'],
                    content: dialogHtml,
                    success: function (layero, layerIdx) {
                        layero.find('.approver-type').on('change', function () {
                            layero.find('.admin-selector').toggle($(this).val() === 'admin');
                        });
                        layero.find('#btnAddCondition').on('click', function () {
                            layero.find('#conditionList').append(getConditionHtml());
                        });
                        layero.on('click', '.remove-condition', function () {
                            $(this).closest('.condition-item').remove();
                        });
                        layero.find('#node-form').on('submit', function (e) {
                            e.preventDefault();
                            var currentType = layero.find('.approver-type:checked').val() || 'admin';
                            var adminIds = [];
                            if (currentType === 'admin') {
                                layero.find('input[name="admin_ids[]"]:checked').each(function () {
                                    adminIds.push(parseInt($(this).val(), 10));
                                });
                            }
                            var conditions = [];
                            layero.find('.condition-item').each(function () {
                                var field = $(this).find('[name="condition_field[]"]').val();
                                var operator = $(this).find('[name="condition_operator[]"]').val();
                                var val = $(this).find('[name="condition_value[]"]').val();
                                if (field || operator || val) {
                                    conditions.push({ field: field || '', operator: operator || '', value: val || '' });
                                }
                            });

                            node.name = $.trim(layero.find('[name="name"]').val() || '');
                            node.approver_type = currentType;
                            node.approver_ids = JSON.stringify(adminIds);
                            node.approval_mode = layero.find('[name="approval_mode"]:checked').val() || 'any_sign';
                            node.condition_logic = layero.find('[name="condition_logic"]:checked').val() || 'AND';
                            node.condition_items = JSON.stringify(conditions);

                            if (!node.name) {
                                Toastr.warning('节点名称不能为空');
                                return;
                            }
                            if (index === nodes.length) {
                                nodes.push(node);
                            } else {
                                nodes[index] = node;
                            }
                            normalizeNodeSort();
                            renderNodes();
                            layer.close(layerIdx);
                        });
                    }
                });
            }

            function bindNodeEvents() {
                var dragFrom = null;
                $('.node-item').off('dragstart dragend dragover dragenter dragleave drop');
                $('.node-item').on('dragstart', function (e) {
                    dragFrom = parseInt($(this).attr('data-index'), 10);
                    e.originalEvent.dataTransfer.effectAllowed = 'move';
                    $(this).addClass('active');
                });
                $('.node-item').on('dragend', function () {
                    $(this).removeClass('active over');
                    $('.node-item').removeClass('over');
                });
                $('.node-item').on('dragover', function (e) {
                    e.preventDefault();
                });
                $('.node-item').on('dragenter', function () {
                    $(this).addClass('over');
                });
                $('.node-item').on('dragleave', function () {
                    $(this).removeClass('over');
                });
                $('.node-item').on('drop', function (e) {
                    e.preventDefault();
                    var to = parseInt($(this).attr('data-index'), 10);
                    if (isNaN(dragFrom) || isNaN(to) || dragFrom === to) return;
                    var moved = nodes.splice(dragFrom, 1)[0];
                    nodes.splice(to, 0, moved);
                    normalizeNodeSort();
                    renderNodes();
                });

                $('.btn-edit-node').off('click').on('click', function () {
                    var idx = parseInt($(this).closest('.node-item').attr('data-index'), 10);
                    if (isNaN(idx)) return;
                    showNodeDialog($.extend({}, nodes[idx]), idx);
                });
                $('.btn-del-node').off('click').on('click', function () {
                    if (nodes.length <= 1) {
                        Toastr.warning('至少保留一个审批节点');
                        return;
                    }
                    var idx = parseInt($(this).closest('.node-item').attr('data-index'), 10);
                    if (isNaN(idx)) return;
                    layer.confirm('确定要删除此节点吗？', function (i) {
                        nodes.splice(idx, 1);
                        normalizeNodeSort();
                        renderNodes();
                        layer.close(i);
                    });
                });
            }

            function loadNodes() {
                $.get(designerUrl + '?id=' + definitionId, function (res) {
                    if (res.code === 1) {
                        nodes = res.data && res.data.nodes ? res.data.nodes : [];
                        normalizeNodeSort();
                        renderNodes();
                    } else {
                        Toastr.error(res.msg || '加载节点失败');
                    }
                }, 'json').fail(function () {
                    Toastr.error('加载节点失败');
                });
            }

            function loadAdmins() {
                $.get(adminOptionsUrl, function (res) {
                    if (res.code === 1) {
                        admins = res.data && res.data.list ? res.data.list : [];
                        renderNodes();
                    }
                }, 'json');
            }

            $('#btnAddNode').off('click').on('click', function () {
                showNodeDialog({
                    sort: nodes.length + 1,
                    name: '',
                    approver_type: 'admin',
                    approver_ids: '[]',
                    approval_mode: 'any_sign',
                    condition_logic: 'AND',
                    condition_items: '[]'
                }, nodes.length);
            });

            $('#btnSave').off('click').on('click', function () {
                if (!nodes.length) {
                    Toastr.warning('请至少添加一个审批节点');
                    return;
                }
                var loading = layer.load(1, { shade: 0.2 });
                $.post(saveNodesUrl, {
                    definition_id: definitionId,
                    nodes_json: JSON.stringify(nodes)
                }, function (res) {
                    layer.close(loading);
                    if (res.code === 1) {
                        Toastr.success(res.msg || '保存成功');
                    } else {
                        Toastr.error(res.msg || '保存失败');
                    }
                }, 'json').fail(function () {
                    layer.close(loading);
                    Toastr.error('保存失败');
                });
            });

            loadNodes();
            loadAdmins();
        },
        savenodes: function () {},
        del: function () {},
        toggle: function () {}
    };

    window.__backendController = Controller;
})();
