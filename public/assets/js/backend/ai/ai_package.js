(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var table = $('#table');

    var Controller = {
        index: function () {
            if (!table.length || typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) return;
            table.bootstrapTable({
                url: base + '/ai/package/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                pageList: [10, 20, 50],
                sortName: 'id',
                sortOrder: 'asc',
                responseHandler: function (res) {
                    var data = res && res.data ? res.data : {};
                    return {
                        total: Array.isArray(data) ? data.length : (data.total || 0),
                        rows: Array.isArray(data) ? data : (data.list || [])
                    };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 80, sortable: true },
                    { field: 'name', title: '套餐名称', align: 'left' },
                    { field: 'price_month', title: '月价', width: 100, formatter: function (v) {
                        return v ? '¥' + parseFloat(v).toFixed(2) : '-';
                    }},
                    { field: 'price_quarter', title: '季价', width: 100, formatter: function (v) {
                        return v ? '¥' + parseFloat(v).toFixed(2) : '-';
                    }},
                    { field: 'price_year', title: '年价', width: 100, formatter: function (v) {
                        return v ? '¥' + parseFloat(v).toFixed(2) : '-';
                    }},
                    { field: 'description', title: '描述', align: 'left', formatter: function (v) {
                        return v ? (v.length > 50 ? v.substring(0, 50) + '...' : v) : '';
                    }},
                    { field: 'enabled', title: '状态', width: 80, formatter: function (v) {
                        return v == 1 ? '<span class="badge badge-success">启用</span>' : '<span class="badge badge-secondary">禁用</span>';
                    }},
                    { field: 'create_time', title: '创建时间', width: 180, formatter: function (v) {
                        return v ? new Date(v * 1000).toLocaleString('zh-CN') : '';
                    }}
                ]
            });

            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () {
                table.bootstrapTable('refresh');
            });

            $(document).off('click', '#btn-add-package').on('click', '#btn-add-package', function () {
                $('#form-add-package')[0].reset();
                $('#modal-add-package').modal('show');
            });

            $(document).off('click', '#btn-submit-package').on('click', '#btn-submit-package', function () {
                var data = {};
                $('#form-add-package').serializeArray().forEach(function (item) {
                    if (item.name === 'enabled') {
                        data[item.name] = $('#form-add-package input[name="enabled"]').is(':checked') ? 1 : 0;
                    } else {
                        data[item.name] = item.value;
                    }
                });
                if (!data.name || String(data.name).trim() === '') {
                    alert('请输入套餐名称');
                    return;
                }
                $.post(base + '/ai/createPackage', data, function (r) {
                    if (r.code == 1) {
                        $('#modal-add-package').modal('hide');
                        table.bootstrapTable('refresh');
                        alert(r.msg || '创建成功');
                    } else {
                        alert(r.msg || '创建失败');
                    }
                }, 'json').fail(function () {
                    alert('请求失败');
                });
            });

            // 为租户开通：打开弹窗并加载租户列表、套餐列表
            $(document).off('click', '#btn-purchase-for-tenant').on('click', '#btn-purchase-for-tenant', function () {
                var $tenantSelect = $('#purchase-tenant-id');
                var $packageSelect = $('#purchase-package-id');
                $tenantSelect.html('<option value="">请选择租户</option>');
                $packageSelect.html('<option value="">请选择套餐</option>');
                $.get(base + '/ai/package/tenantList', function (r) {
                    if (r && r.code == 1 && Array.isArray(r.data)) {
                        r.data.forEach(function (t) {
                            $tenantSelect.append('<option value="' + t.id + '">' + (t.name || 'ID:' + t.id) + '</option>');
                        });
                    }
                }, 'json');
                $.get(base + '/ai/packages', function (r) {
                    if (r && r.code == 1 && Array.isArray(r.data)) {
                        r.data.forEach(function (p) {
                            if (p.enabled == 1) {
                                $packageSelect.append('<option value="' + p.id + '">' + (p.name || 'ID:' + p.id) + '</option>');
                            }
                        });
                    }
                }, 'json');
                $('#form-purchase-for-tenant')[0].reset();
                $('#modal-purchase-for-tenant').modal('show');
            });

            $(document).off('click', '#btn-submit-purchase').on('click', '#btn-submit-purchase', function () {
                var tenantId = $('#purchase-tenant-id').val();
                var packageId = $('#purchase-package-id').val();
                if (!tenantId || !packageId) {
                    alert('请选择租户和套餐');
                    return;
                }
                var data = {
                    tenant_id: parseInt(tenantId, 10),
                    package_id: parseInt(packageId, 10),
                    period: $('#purchase-period').val() || 'month',
                    order_no: $('#purchase-order-no').val() || '',
                    amount: parseFloat($('#purchase-amount').val()) || 0,
                    payment_method: '后台开通'
                };
                $.post(base + '/ai/purchaseForTenant', data, function (r) {
                    if (r.code == 1) {
                        $('#modal-purchase-for-tenant').modal('hide');
                        alert(r.msg || '开通成功');
                    } else {
                        alert(r.msg || '开通失败');
                    }
                }, 'json').fail(function () {
                    alert('请求失败');
                });
            });

            $(document).off('click', '#btn-global-switch').on('click', '#btn-global-switch', function () {
                $.get(base + '/ai/globalSwitch', function (r) {
                    if (r && r.code == 1) {
                        var d = r.data || {};
                        $('#switch-enabled').prop('checked', d.enabled == 1);
                        $('#switch-require').prop('checked', d.require_purchase == 1);
                        $('#form-global-switch textarea[name="notice"]').val(d.notice || '');
                        $('#switch-voice-report').prop('checked', d.switch_voice_report == 1);
                        $('#switch-anomaly').prop('checked', d.switch_anomaly == 1);
                        $('#switch-qa').prop('checked', d.switch_qa == 1);
                        $('#switch-crm-follow').prop('checked', d.switch_crm_follow == 1);
                        $('#modal-global-switch').modal('show');
                    } else {
                        alert(r && r.msg ? r.msg : '获取配置失败');
                    }
                }, 'json').fail(function () {
                    alert('请求失败，请检查网络或是否已执行 AI 相关数据库迁移');
                });
            });

            $(document).off('click', '#btn-submit-switch').on('click', '#btn-submit-switch', function () {
                var data = {
                    enabled: $('#switch-enabled').is(':checked') ? 1 : 0,
                    require_purchase: $('#switch-require').is(':checked') ? 1 : 0,
                    notice: $('#form-global-switch textarea[name="notice"]').val(),
                    switch_voice_report: $('#switch-voice-report').is(':checked') ? 1 : 0,
                    switch_anomaly: $('#switch-anomaly').is(':checked') ? 1 : 0,
                    switch_qa: $('#switch-qa').is(':checked') ? 1 : 0,
                    switch_crm_follow: $('#switch-crm-follow').is(':checked') ? 1 : 0
                };
                $.ajax({ url: base + '/ai/updateGlobal', type: 'POST', contentType: 'application/json', data: JSON.stringify(data),
                    success: function (r) {
                        if (r.code == 1) {
                            $('#modal-global-switch').modal('hide');
                            alert(r.msg || '更新成功');
                        } else {
                            alert(r.msg || '更新失败');
                        }
                    }
                }).fail(function () {
                    alert('请求失败');
                });
            });
        }
    };
    window.__backendController = Controller;
})();
