 (function () {
    if (typeof jQuery === 'undefined') {
        return;
    }
    var $ = jQuery;
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    var indexUrl = base + '/mes/customer_product/index';
    var addUrl = base + '/mes/customer_product/add';
    var editUrl = base + '/mes/customer_product/edit';
    var delUrl = base + '/mes/customer_product/del';

    function statusFmt(v) {
        return v == 1 ? '正常' : '禁用';
    }

    function operFmt(v) {
        return '<a class="btn btn-xs btn-primary" href="' + editUrl + '?ids=' + v + '">编辑</a> ' +
            '<button class="btn btn-xs btn-danger" data-id="' + v + '" type="button">删除</button>';
    }

    function bindIndexEvents($table) {
        $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () {
            $table.bootstrapTable('refresh');
        });

        $(document).off('click', '#toolbar .btn-add').on('click', '#toolbar .btn-add', function () {
            window.location.href = addUrl;
        });

        $(document).off('click', '#toolbar .btn-edit').on('click', '#toolbar .btn-edit', function () {
            var rows = $table.bootstrapTable('getSelections');
            if (!rows.length) {
                alert('请选择一条记录');
                return;
            }
            if (rows.length > 1) {
                alert('只能编辑一条记录');
                return;
            }
            window.location.href = editUrl + '?ids=' + rows[0].id;
        });

        $(document).off('click', '#toolbar .btn-del').on('click', '#toolbar .btn-del', function () {
            var rows = $table.bootstrapTable('getSelections');
            if (!rows.length) {
                alert('请选择要删除的记录');
                return;
            }
            if (!confirm('确定要删除选中的 ' + rows.length + ' 条记录吗？')) {
                return;
            }
            var ids = rows.map(function (r) { return r.id; }).join(',');
            $.post(delUrl, { ids: ids }, function (r) {
                alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                if (r.code === 1) {
                    $table.bootstrapTable('refresh');
                }
            }, 'json');
        });

        $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
            var rows = $table.bootstrapTable('getSelections');
            if (rows.length > 0) {
                $('#toolbar .btn-edit, #toolbar .btn-del').removeClass('disabled btn-disabled');
            } else {
                $('#toolbar .btn-edit, #toolbar .btn-del').addClass('disabled btn-disabled');
            }
        });

        $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
            var id = $(this).data('id');
            if (!id || !confirm('确定删除该配置？')) return;
            $.post(delUrl, { ids: id }, function (r) {
                alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                if (r.code === 1) $table.bootstrapTable('refresh');
            }, 'json');
        });
    }

    function bindFormSubmit($form) {
        $form.on('submit', function (e) {
            e.preventDefault();
            var $f = $(this);
            var customerId = $f.find('[name="row[customer_id]"]').val();
            var modelId = $f.find('[name="row[model_id]"]').val();
            var price = parseFloat($f.find('[name="row[price]"]').val() || '0');
            if (!customerId) {
                alert('请选择客户');
                return false;
            }
            if (!modelId) {
                alert('请选择产品型号');
                return false;
            }
            if (!(price > 0)) {
                alert('请输入正确的单价');
                return false;
            }
            var btn = $f.find('button[type=submit]');
            btn.prop('disabled', true);
            $.post(window.location.href, $f.serialize(), function (r) {
                if (r.msg) {
                    alert(r.msg);
                }
                if (r.code === 1) {
                    window.location.href = indexUrl;
                } else {
                    btn.prop('disabled', false);
                }
            }, 'json').fail(function () {
                alert('请求失败');
                btn.prop('disabled', false);
            });
        });
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (!$table.length || (typeof $table.bootstrapTable !== 'function') || $table.data('bootstrap.table')) {
                return;
            }
            $table.bootstrapTable({
                url: indexUrl,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                columns: [
                    { field: 'state', checkbox: true },
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'customer_name', title: '客户', width: 150 },
                    { field: 'product_model_name', title: '产品型号', width: 220 },
                    { field: 'price', title: '单价', width: 100 },
                    { field: 'currency', title: '币种', width: 80 },
                    { field: 'min_qty', title: '起订量', width: 80 },
                    { field: 'status', title: '状态', width: 80, formatter: statusFmt },
                    { field: 'create_time', title: '创建时间', width: 150 },
                    { field: 'id', title: '操作', width: 150, formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return {
                        total: (res.data && res.data.total) ? res.data.total : 0,
                        rows: (res.data && res.data.list) ? res.data.list : []
                    };
                }
            });
            bindIndexEvents($table);
        },
        add: function () {
            var $form = $('#form-add');
            if ($form.length) {
                bindFormSubmit($form);
            }
        },
        edit: function () {
            var $form = $('#form-edit');
            if ($form.length) {
                bindFormSubmit($form);
            }
        }
    };

    window.__backendController = Controller;
})();
