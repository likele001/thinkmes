/**
 * 工艺路线管理页面JS
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/process_route/index';
    var addUrl = base + '/mes/process_route/add';
    var editUrl = base + '/mes/process_route/edit';
    var delUrl = base + '/mes/process_route/del';
    var getUrl = base + '/mes/process_route/get';

    function initStepsEditor($form) {
        var $table = $('#steps-table');
        var $tbody = $table.find('tbody');
        var $picker = $('#process-picker');
        var $json = $('#steps-json');
        var $toggle = $('#btn-toggle-json');

        if (!$table.length || !$picker.length || !$json.length) return null;

        var steps = [];

        function normalize() {
            for (var i = 0; i < steps.length; i++) steps[i].step_no = i + 1;
        }

        function writeJson() {
            normalize();
            var out = [];
            for (var i = 0; i < steps.length; i++) {
                var s = steps[i];
                out.push({
                    step_no: s.step_no,
                    process_id: s.process_id,
                    group_no: s.group_no,
                    is_optional: s.is_optional ? 1 : 0
                });
            }
            $json.val(JSON.stringify(out));
        }

        function render() {
            normalize();
            $tbody.empty();
            for (var i = 0; i < steps.length; i++) {
                (function (idx) {
                    var s = steps[idx];
                    var $tr = $('<tr></tr>');
                    $tr.append('<td class="text-center">' + (idx + 1) + '</td>');

                    var $sel = $picker.clone();
                    $sel.removeAttr('id');
                    $sel.val(String(s.process_id));
                    $sel.on('change', function () {
                        steps[idx].process_id = parseInt($(this).val() || '0', 10) || 0;
                        writeJson();
                    });
                    var $tdSel = $('<td></td>').append($sel);
                    $tr.append($tdSel);

                    var $group = $('<input type="number" class="form-control" min="1" style="width:90px">').val(String(s.group_no || 1));
                    $group.on('change', function () {
                        steps[idx].group_no = parseInt($(this).val() || '1', 10) || 1;
                        writeJson();
                    });
                    $tr.append($('<td></td>').append($group));

                    var $opt = $('<input type="checkbox">').prop('checked', !!s.is_optional);
                    $opt.on('change', function () {
                        steps[idx].is_optional = $(this).is(':checked');
                        writeJson();
                    });
                    $tr.append($('<td class="text-center"></td>').append($opt));

                    var $ops = $('<td></td>');
                    var $up = $('<a href="javascript:;" class="btn btn-xs btn-default mr-1">上移</a>');
                    var $down = $('<a href="javascript:;" class="btn btn-xs btn-default mr-1">下移</a>');
                    var $del = $('<a href="javascript:;" class="btn btn-xs btn-danger">删除</a>');
                    $up.on('click', function () {
                        if (idx <= 0) return;
                        var tmp = steps[idx - 1];
                        steps[idx - 1] = steps[idx];
                        steps[idx] = tmp;
                        render();
                        writeJson();
                    });
                    $down.on('click', function () {
                        if (idx >= steps.length - 1) return;
                        var tmp = steps[idx + 1];
                        steps[idx + 1] = steps[idx];
                        steps[idx] = tmp;
                        render();
                        writeJson();
                    });
                    $del.on('click', function () {
                        steps.splice(idx, 1);
                        render();
                        writeJson();
                    });
                    $ops.append($up).append($down).append($del);
                    $tr.append($ops);

                    $tbody.append($tr);
                })(i);
            }
        }

        function loadFromJson() {
            var raw = $.trim($json.val() || '');
            if (!raw) return;
            try {
                var arr = JSON.parse(raw);
                if (!Array.isArray(arr)) return;
                steps = [];
                for (var i = 0; i < arr.length; i++) {
                    var it = arr[i] || {};
                    var pid = parseInt(it.process_id || '0', 10) || 0;
                    if (!pid) continue;
                    steps.push({
                        step_no: parseInt(it.step_no || (steps.length + 1), 10) || (steps.length + 1),
                        process_id: pid,
                        group_no: parseInt(it.group_no || '1', 10) || 1,
                        is_optional: (parseInt(it.is_optional || '0', 10) || 0) === 1
                    });
                }
                steps.sort(function (a, b) { return (a.step_no || 0) - (b.step_no || 0); });
            } catch (e) {
            }
        }

        $('#btn-add-step').off('click').on('click', function () {
            var pid = parseInt($picker.val() || '0', 10) || 0;
            if (!pid) return;
            steps.push({ step_no: steps.length + 1, process_id: pid, group_no: 1, is_optional: false });
            render();
            writeJson();
        });

        $toggle.off('click').on('click', function () {
            if ($json.is(':visible')) $json.hide();
            else { writeJson(); $json.show(); }
        });

        function setFromJsonText(jsonText) {
            $json.val(jsonText || '');
            loadFromJson();
            render();
            writeJson();
        }

        loadFromJson();
        render();
        writeJson();
        return { writeJson: writeJson, setFromJsonText: setFromJsonText };
    }

    function statusFmt(value) {
        var map = {
            0: {text: '草稿', cls: 'badge-secondary'},
            1: {text: '审核中', cls: 'badge-info'},
            2: {text: '已发布', cls: 'badge-success'},
            3: {text: '已归档', cls: 'badge-dark'}
        };
        var item = map[value] || {text: value, cls: 'badge-secondary'};
        return '<span class="badge ' + item.cls + '">' + item.text + '</span>';
    }

    function yesNoFmt(value) {
        return value ? '<span class="badge badge-success">是</span>' : '<span class="badge badge-secondary">否</span>';
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }

            var $form = $('#route-search-form');

            $table.bootstrapTable({
                url: indexUrl,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                search: false,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                responseHandler: function (res) {
                    var d = res && res.data ? res.data : {};
                    return { total: d.total || 0, rows: d.list || [] };
                },
                queryParams: function (params) {
                    var formData = $form.serializeArray();
                    formData.forEach(function (item) {
                        params[item.name] = item.value;
                    });
                    return params;
                },
                toolbar: '#toolbar',
                columns: [
                    {checkbox: true},
                    {field: 'id', title: 'ID', sortable: true, width: 80},
                    {field: 'route_name', title: '路线名称', align: 'left'},
                    {field: 'route_code', title: '路线编码', align: 'left', width: 140},
                    {field: 'model.full_name', title: '产品型号', align: 'left'},
                    {field: 'route_type', title: '路线类型', width: 100},
                    {field: 'is_default', title: '默认', width: 80, formatter: yesNoFmt},
                    {field: 'status', title: '状态', width: 100, formatter: statusFmt},
                    {
                        field: 'create_time',
                        title: '创建时间',
                        width: 180,
                        formatter: function (value) {
                            return value ? new Date(value * 1000).toLocaleString('zh-CN') : '';
                        }
                    },
                    {
                        field: 'operate',
                        title: '操作',
                        width: 150,
                        formatter: function () {
                            return [
                                '<a href="javascript:;" class="btn btn-xs btn-success btn-edit-single"><i class="fa fa-edit"></i> 编辑</a>'
                            ].join(' ');
                        },
                        events: {
                            'click .btn-edit-single': function (e, value, row) {
                                location.href = editUrl + '?ids=' + row.id;
                            }
                        }
                    }
                ]
            });

            $form.on('submit', function (e) {
                e.preventDefault();
                $table.bootstrapTable('refresh', {pageNumber: 1});
            });

            $('.btn-refresh').on('click', function () {
                $table.bootstrapTable('refresh');
            });

            $('.btn-add').on('click', function () {
                location.href = addUrl;
            });

            $('.btn-edit').on('click', function () {
                var selections = $table.bootstrapTable('getSelections') || [];
                if (!selections.length) {
                    return;
                }
                var row = selections[0];
                location.href = editUrl + '?ids=' + row.id;
            });

            $('.btn-del').on('click', function () {
                var selections = $table.bootstrapTable('getSelections') || [];
                if (!selections.length) {
                    return;
                }
                if (!confirm('确定要删除选中的工艺路线吗？')) {
                    return;
                }
                var ids = selections.map(function (row) {
                    return row.id;
                });
                $.post(delUrl, {ids: ids.join(',')}, function (res) {
                    if (res && res.code === 1) {
                        $table.bootstrapTable('refresh');
                    } else if (res && res.msg) {
                        alert(res.msg);
                    } else {
                        alert('删除失败');
                    }
                }, 'json');
            });
        },
        add: function () {
            $('#tenant-id').off('change').on('change', function () {
                var tid = $(this).val() || '';
                if (!tid) return;
                location.href = addUrl + '?tenant_id=' + encodeURIComponent(tid);
            });
            var editor = initStepsEditor($('#add-form'));
            $('#route-template').off('change').on('change', function () {
                var id = $(this).val() || '';
                if (!id) return;
                $.get(getUrl, { id: id }, function (r) {
                    if (!r || r.code != 1) {
                        alert((r && r.msg) ? r.msg : '读取失败');
                        return;
                    }
                    var d = r.data || {};
                    if (editor && editor.setFromJsonText) editor.setFromJsonText(d.steps_json || '');
                    var $name = $('input[name="row[route_name]"]');
                    if ($name.length && $.trim($name.val() || '') === '' && d.route_name) $name.val(d.route_name);
                }, 'json');
            });
            $('#add-form').on('submit', function (e) {
                e.preventDefault();
                if (editor && editor.writeJson) editor.writeJson();
                var $form = $(this);
                $.post('', $form.serialize(), function (res) {
                    if (res && res.code === 1) {
                        if (typeof Fast !== 'undefined' && Fast.api && Fast.api.close) {
                            Fast.api.close();
                        } else {
                            history.back();
                        }
                    } else if (res && res.msg) {
                        alert(res.msg);
                    } else {
                        alert('保存失败');
                    }
                }, 'json');
            });
        },
        edit: function () {
            var editor = initStepsEditor($('#edit-form'));
            $('#route-template').off('change').on('change', function () {
                var id = $(this).val() || '';
                if (!id) return;
                if (!confirm('确定套用该路线步骤并覆盖当前步骤？')) {
                    $(this).val('');
                    return;
                }
                $.get(getUrl, { id: id }, function (r) {
                    if (!r || r.code != 1) {
                        alert((r && r.msg) ? r.msg : '读取失败');
                        return;
                    }
                    var d = r.data || {};
                    if (editor && editor.setFromJsonText) editor.setFromJsonText(d.steps_json || '');
                }, 'json');
            });
            $('#edit-form').on('submit', function (e) {
                e.preventDefault();
                if (editor && editor.writeJson) editor.writeJson();
                var $form = $(this);
                $.post('', $form.serialize(), function (res) {
                    if (res && res.code === 1) {
                        if (typeof Fast !== 'undefined' && Fast.api && Fast.api.close) {
                            Fast.api.close();
                        } else {
                            history.back();
                        }
                    } else if (res && res.msg) {
                        alert(res.msg);
                    } else {
                        alert('保存失败');
                    }
                }, 'json');
            });
        }
    };

    window.__backendController = Controller;
})();
