(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/bom/index';
    var editUrl = base + '/mes/bom/edit';
    var delUrl = base + '/mes/bom/del';
    var itemsUrl = base + '/mes/bom/items';

    function statusFmt(v) {
        var statusMap = {0: '草稿', 1: '审核中', 2: '已发布', 3: '已废弃'};
        return statusMap[v] || '未知';
    }

    function operFmt(v, row) {
        var html = '<a class="btn btn-xs btn-primary" href="' + editUrl + '?ids=' + v + '">编辑</a> ';
        html += '<a class="btn btn-xs btn-info" href="' + itemsUrl + '?ids=' + v + '">明细</a> ';
        html += '<button class="btn btn-xs btn-danger" data-id="' + v + '" type="button">删除</button>';
        return html;
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
                pageList: [10, 20, 50],
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'bom_no', title: 'BOM编号', width: 150 },
                    { field: 'version', title: '版本号', width: 100 },
                    { field: 'base_quantity', title: '基准数量', width: 100 },
                    { field: 'status', title: '状态', width: 100, formatter: statusFmt },
                    { field: 'create_time', title: '创建时间', width: 150 },
                    { field: 'id', title: '操作', width: 200, formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
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
                $.post(delUrl, { ids: ids.join(',') }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '删除失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
            $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除该BOM？')) return;
                $.post(delUrl, { ids: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
            $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length > 0) { $('.btn-edit, .btn-del').removeClass('disabled btn-disabled'); }
                else { $('.btn-edit, .btn-del').addClass('disabled btn-disabled'); }
            });
        },
        add: function () {
            var form = $('#form-add');
            if (!form.length) return;
            // BOM类型切换：通用模板时隐藏产品/型号选择
            function toggleProductModel() {
                var isTemplate = $('#bom_type').val() === '1';
                if (isTemplate) {
                    $('#product-model-row-wrap').addClass('d-none');
                    $('#template-tip').removeClass('d-none');
                    $('#model_id').removeAttr('required');
                } else {
                    $('#product-model-row-wrap').removeClass('d-none');
                    $('#template-tip').addClass('d-none');
                    $('#model_id').attr('required', 'required');
                }
            }
            $('#bom_type').on('change', toggleProductModel);
            toggleProductModel();
            form.attr('action', base + '/mes/bom/add');
            form.on('submit', function (e) {
                e.preventDefault();
                var bomType = form.find('select[name="row[bom_type]"]').val();
                var modelId = form.find('select[name="row[model_id]"]').val();
                var productId = form.find('select[name="row[product_id]"]').val();
                if (bomType !== '1') {
                    if (!modelId) { alert('请选择产品型号或通用（默认）'); return; }
                    if (modelId === '0' && !productId) { alert('选择通用（默认）型号时请先选择产品'); return; }
                }
                $.ajax({ url: base + '/mes/bom/add', type: 'POST', data: new FormData(this), dataType: 'json', processData: false, contentType: false })
                    .done(function (r) { if (r && r.msg) alert(r.msg); if (r && r.code === 1) location.href = indexUrl; })
                    .fail(function (xhr) { try { var j = JSON.parse(xhr.responseText); alert(j.msg || '操作失败'); } catch(e) { alert('操作失败'); } });
            });
        },
        edit: function () {
            var form = $('#form-edit');
            if (!form.length) return;
            var id = form.data('id') || 0;
            form.attr('action', base + '/mes/bom/edit?ids=' + id);
            form.on('submit', function (e) {
                e.preventDefault();
                var bomType = form.find('select[name="row[bom_type]"]').val();
                var modelId = form.find('select[name="row[model_id]"]').val();
                var productId = form.find('select[name="row[product_id]"]').val();
                if (bomType !== '1') {
                    if (!modelId) { alert('请选择产品型号或通用（默认）'); return; }
                    if (modelId === '0' && !productId) { alert('选择通用（默认）型号时请先选择产品'); return; }
                }
                $.ajax({ url: form.attr('action'), type: 'POST', data: new FormData(this), dataType: 'json', processData: false, contentType: false })
                    .done(function (r) { if (r && r.msg) alert(r.msg); if (r && r.code === 1) location.href = indexUrl; })
                    .fail(function (xhr) { try { var j = JSON.parse(xhr.responseText); alert(j.msg || '操作失败'); } catch(e) { alert('操作失败'); } });
            });
        },
        items: function () {
            var bomId = 0;
            var $table = $('#table');
            if (!$table.length) return;

            // 从 URL 或隐藏字段获取 bomId
            var m = window.location.href.match(/[?&]ids=(\d+)/);
            if (m) bomId = parseInt(m[1]) || 0;

            var materialList = {}, materialListWithCategory = [], categoryList = {}, supplierList = {};
            try { var ml = document.getElementById('material-list-json'); if (ml) materialList = JSON.parse(ml.textContent || '{}'); } catch(e){}
            try { var mlc = document.getElementById('material-list-with-category-json'); if (mlc) materialListWithCategory = JSON.parse(mlc.textContent || '[]'); } catch(e){}
            try { var cl = document.getElementById('category-list-json'); if (cl) categoryList = JSON.parse(cl.textContent || '{}'); } catch(e){}
            try { var sl = document.getElementById('supplier-list-json'); if (sl) supplierList = JSON.parse(sl.textContent || '{}'); } catch(e){}

            function filterItemModalMaterials() {
                var cat = $('#item-modal-category').val();
                $('#item-form-material option').each(function () {
                    var $opt = $(this);
                    if ($opt.val() === '') { $opt.show(); return; }
                    var cid = $opt.attr('data-category-id') || '0';
                    if (cat === '' || cid === cat) { $opt.show(); } else { $opt.hide(); }
                });
                $('#item-form-material').val('');
            }
            function filterBatchMaterials() {
                var cat = $('#batch-category-filter').val();
                $('#batch-tbody select.batch-material').each(function () {
                    $(this).find('option').each(function () {
                        var $opt = $(this);
                        if ($opt.val() === '') { $opt.show(); return; }
                        var cid = $opt.attr('data-category-id') || '0';
                        if (cat === '' || cid === cat) { $opt.show(); } else { $opt.hide(); }
                    });
                    if ($(this).find('option:selected').is(':hidden')) { $(this).val(''); }
                });
            }
            function closeItemModal() {
                $('#item-modal').modal('hide');
                setTimeout(function () { $('body').removeClass('modal-open'); $('.modal-backdrop').remove(); }, 300);
            }
            function closeBatchModal() {
                $('#batch-modal').modal('hide');
                setTimeout(function () { $('body').removeClass('modal-open'); $('.modal-backdrop').remove(); }, 300);
            }

            $(document).on('click', '#item-modal .btn-close-modal', function () { closeItemModal(); });
            $(document).on('change', '#item-modal-category', filterItemModalMaterials);
            $(document).on('click', '#batch-modal .btn-close-batch-modal', function () { closeBatchModal(); });
            $(document).on('change', '#batch-category-filter', filterBatchMaterials);

            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) return;
            $table.bootstrapTable({
                url: base + '/mes/bom/items',
                queryParams: function (params) { params.ids = bomId; return params; },
                sidePagination: 'server',
                pagination: false,
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'level', title: '层级', width: 80 },
                    { field: 'sequence', title: '排序', width: 80 },
                    { field: 'material_id', title: '物料', formatter: function(v,row){ return (row.material && row.material.name) ? row.material.name : (v||'-'); } },
                    { field: 'quantity', title: '用量' },
                    { field: 'loss_rate', title: '损耗率(%)', formatter: function (v) { return v ? (v + '%') : '0%'; } },
                    { field: 'supplier_id', title: '供应商', formatter: function(v,row){ return (row.supplier && row.supplier.name) ? row.supplier.name : (v||'-'); } },
                    { field: 'id', title: '操作', width: 140, formatter: function (v, row) {
                        return '<button type="button" class="btn btn-xs btn-success btn-edit-item" data-id="' + row.id + '">编辑</button> ' +
                               '<button type="button" class="btn btn-xs btn-danger btn-del-item" data-id="' + row.id + '">删除</button>';
                    }}
                ],
                responseHandler: function (res) {
                    return { total: (res.data&&res.data.total)?res.data.total:0, rows: (res.data&&res.data.list)?res.data.list:[] };
                }
            });

            function openModal(row) {
                var form = $('#item-form')[0];
                form.reset();
                $('#item-modal-category').val('');
                $('#item-form input[name="id"]').val(row && row.id ? row.id : '');
                $('#item-form select[name="material_id"]').val(row && row.material_id ? row.material_id : '');
                $('#item-form input[name="quantity"]').val(row && row.quantity ? row.quantity : '');
                $('#item-form input[name="loss_rate"]').val(row && row.loss_rate ? row.loss_rate : '');
                $('#item-form select[name="supplier_id"]').val(row && row.supplier_id ? row.supplier_id : '');
                $('#item-form input[name="level"]').val(row && row.level ? row.level : 1);
                $('#item-form input[name="sequence"]').val(row && row.sequence ? row.sequence : 1);
                filterItemModalMaterials();
                $('#item-modal').modal('show');
            }

            $(document).off('click', '.btn-add-item').on('click', '.btn-add-item', function () { openModal(null); });
            $(document).off('click', '#table .btn-edit-item').on('click', '#table .btn-edit-item', function () {
                var id = $(this).data('id');
                var row = $table.bootstrapTable('getData').find(function (r) { return r.id === id; });
                if (!row) return;
                openModal(row);
            });
            $(document).off('click', '#table .btn-del-item').on('click', '#table .btn-del-item', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定要删除该物料明细吗？')) return;
                $.post(base + '/mes/bom/deleteItem', { id: id }, function (r) {
                    alert((r && r.msg) || (r && r.code === 1 ? '删除成功' : '删除失败'));
                    if (r && r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
            $(document).off('click', '.btn-save-item').on('click', '.btn-save-item', function () {
                var form = $('#item-form');
                var materialId = form.find('select[name="material_id"]').val();
                var qty = form.find('input[name="quantity"]').val();
                if (!materialId) { alert('请选择物料'); return; }
                if (!qty || parseFloat(qty) <= 0) { alert('请填写大于 0 的用量'); return; }
                var payload = {};
                form.serializeArray().forEach(function (item) { payload[item.name] = item.value; });
                var id = payload.id || '';
                $.post(id ? (base + '/mes/bom/updateItem') : (base + '/mes/bom/addItem'), payload, function (r) {
                    alert((r && r.msg) || (r && r.code === 1 ? '保存成功' : '保存失败'));
                    if (r && r.code === 1) { closeItemModal(); $table.bootstrapTable('refresh'); }
                }, 'json');
            });

            function buildBatchRows() {
                var catOpts = '<option value="">全部分类</option>';
                for (var cid in categoryList) { catOpts += '<option value="' + cid + '">' + (categoryList[cid] || '') + '</option>'; }
                $('#batch-category-filter').empty().append(catOpts);
                var materialOpts = '<option value="">请选择</option>';
                materialListWithCategory.forEach(function (m) {
                    var mCid = (m.category_id != null && m.category_id !== '') ? m.category_id : '0';
                    materialOpts += '<option value="' + m.id + '" data-category-id="' + mCid + '">' + (m.name || '') + '</option>';
                });
                var supplierOpts = '<option value="">可不选</option>';
                for (var s in supplierList) { supplierOpts += '<option value="' + s + '">' + (supplierList[s] || '') + '</option>'; }
                var tbody = $('#batch-tbody');
                tbody.empty();
                for (var i = 1; i <= 10; i++) {
                    tbody.append('<tr data-row="' + i + '"><td>' + i + '</td>' +
                        '<td><select class="form-control form-control-sm batch-material" name="material_id">' + materialOpts + '</select></td>' +
                        '<td><input type="number" step="0.0001" min="0" class="form-control form-control-sm batch-qty" name="quantity" placeholder="用量"></td>' +
                        '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="loss_rate" placeholder="0"></td>' +
                        '<td><select class="form-control form-control-sm" name="supplier_id">' + supplierOpts + '</select></td>' +
                        '<td><input type="number" min="1" class="form-control form-control-sm" name="level" value="1"></td>' +
                        '<td><input type="number" min="0" class="form-control form-control-sm" name="sequence" value="' + i + '"></td></tr>');
                }
                filterBatchMaterials();
            }
            $(document).off('click', '.btn-batch-add').on('click', '.btn-batch-add', function () { buildBatchRows(); $('#batch-modal').modal('show'); });
            $(document).off('click', '.btn-save-batch').on('click', '.btn-save-batch', function () {
                var items = [];
                $('#batch-tbody tr').each(function () {
                    var $tr = $(this);
                    var mid = $tr.find('select.batch-material').val();
                    var qty = $tr.find('input.batch-qty').val();
                    if (mid && qty && parseFloat(qty) > 0) {
                        items.push({ material_id: mid, quantity: qty, loss_rate: $tr.find('input[name="loss_rate"]').val() || 0, supplier_id: $tr.find('select[name="supplier_id"]').val() || 0, level: $tr.find('input[name="level"]').val() || 1, sequence: $tr.find('input[name="sequence"]').val() || 0 });
                    }
                });
                if (!items.length) { alert('请至少填写一行物料与用量'); return; }
                $.post(base + '/mes/bom/addItemBatch', { bom_id: bomId, items: items }, function (r) {
                    alert((r && r.msg) || (r && r.code === 1 ? '批量添加成功' : '批量添加失败'));
                    if (r && r.code === 1) { closeBatchModal(); $table.bootstrapTable('refresh'); }
                }, 'json');
            });
            $(document).off('click', '.btn-import-template').on('click', '.btn-import-template', function () {
                var tplId = $('#template_bom_id').val();
                if (!tplId) { alert('请选择通用模板'); return; }
                if (!confirm('导入后将覆盖当前 BOM 的明细，确定继续吗？')) return;
                $.post(base + '/mes/bom/importTemplateItems', { bom_id: bomId, template_bom_id: tplId }, function (r) {
                    alert((r && r.msg) || (r && r.code === 1 ? '导入成功' : '导入失败'));
                    if (r && r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
