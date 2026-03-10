/**
 * 分工二维码管理
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/allocation_qrcode/index';
    var getInfoUrl = base + '/mes/allocation_qrcode/getInfo';
    var regenerateUrl = base + '/mes/allocation_qrcode/regenerate';

    function operFmt(value, row) {
        return '<a href="javascript:;" class="btn btn-xs btn-info btn-view-qr" data-id="' + (row.id || '') + '" data-allocation-id="' + (row.allocation_id || '') + '">查看二维码</a> ' +
            '<a href="javascript:;" class="btn btn-xs btn-warning btn-regen-qr" data-id="' + (row.id || '') + '" data-allocation-id="' + (row.allocation_id || '') + '">重新生成</a>';
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
                    {field: 'id', title: 'ID', width: 70, sortable: true},
                    {field: 'allocation_id', title: '分工ID', width: 80},
                    {field: 'order_no', title: '订单号', align: 'left'},
                    {field: 'product_name', title: '产品', align: 'left'},
                    {field: 'model_name', title: '型号', align: 'left'},
                    {field: 'process_name', title: '工序', width: 100},
                    {field: 'quantity', title: '分配数量', width: 90, align: 'right'},
                    {field: 'qrcode_url', title: '报工链接', align: 'left', formatter: function(v) {
                        if (!v) return '<span class="text-muted">未生成</span>';
                        return '<span class="text-truncate d-inline-block" style="max-width:200px" title="' + (v || '') + '">' + (v || '') + '</span>';
                    }},
                    {field: 'operate', title: '操作', width: 200, formatter: operFmt}
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });

            $(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () {
                $table.bootstrapTable('refresh');
            });

            $(document).off('click', '.btn-view-qr').on('click', '.btn-view-qr', function () {
                var id = $(this).data('id');
                Controller.api.showQrcodeModal(id, null);
            });

            $(document).off('click', '.btn-regen-qr').on('click', '.btn-regen-qr', function () {
                var id = $(this).data('id');
                var allocationId = $(this).data('allocation-id');
                if (!confirm('确定要重新生成该任务的二维码吗？')) return;
                $.post(regenerateUrl, { id: id, allocation_id: allocationId }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已重新生成' : '失败'));
                    if (r.code == 1) $table.bootstrapTable('refresh');
                }, 'json');
            });

            // 显式关闭弹窗，避免在 iframe 内 data-dismiss 无效导致关不掉
            $(document).off('click', '#qrcode-modal-btn-close-x, #qrcode-modal-btn-close').on('click', '#qrcode-modal-btn-close-x, #qrcode-modal-btn-close', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $('#qrcodeModal').modal('hide');
            });

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

            $(document).off('click', '#qrcode-modal-regenerate').on('click', '#qrcode-modal-regenerate', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var allocationId = $('#qrcodeModal').data('allocation-id');
                var qrId = $('#qrcodeModal').data('qr-id');
                if (!allocationId && !qrId) {
                    alert('无法获取分工信息，请关闭后从列表操作');
                    return;
                }
                if (!confirm('确定要重新生成二维码吗？')) return;
                $.post(regenerateUrl, { id: qrId, allocation_id: allocationId }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已重新生成' : '失败'));
                    if (r.code == 1) {
                        $('#qrcodeModal').modal('hide');
                        $table.bootstrapTable('refresh');
                    }
                }, 'json');
            });
        },
        api: {
            showQrcodeModal: function (qrId, allocationId) {
                var $modal = $('#qrcodeModal');
                var $imgWrap = $('#qrcode-modal-img-wrap');
                var $url = $('#qrcode-modal-url');
                $imgWrap.html('<p class="text-muted">加载中...</p>');
                $url.val('');
                $modal.data('qr-id', qrId).data('allocation-id', allocationId).modal('show');
                var reqUrl = getInfoUrl + (getInfoUrl.indexOf('?') >= 0 ? '&' : '?') + 'id=' + encodeURIComponent(qrId);
                if (typeof console !== 'undefined' && console.log) console.log('[分工二维码 getInfo] 请求 URL:', reqUrl);
                $.ajax({
                    url: getInfoUrl,
                    type: 'GET',
                    data: { id: qrId },
                    dataType: 'json'
                }).done(function (r) {
                    if (typeof console !== 'undefined' && console.log) {
                        console.log('[分工二维码 getInfo] response:', r);
                    }
                    if (!r || typeof r !== 'object') {
                        var err = '服务器返回数据异常（非 JSON 对象）';
                        if (typeof console !== 'undefined' && console.warn) console.warn('[分工二维码 getInfo]', err, r);
                        $imgWrap.html('<p class="text-danger">' + err + '</p>');
                        return;
                    }
                    if (r.code !== 1 || !r.data || !r.data.url) {
                        var errMsg = (r.msg && String(r.msg)) ? r.msg : '获取二维码失败';
                        var detail = '（code=' + (r.code !== undefined ? r.code : '?') + (r.msg ? ', msg=' + r.msg : '') + '）';
                        if (typeof console !== 'undefined' && console.warn) console.warn('[分工二维码 getInfo] 失败:', errMsg, detail, r);
                        $imgWrap.html('<p class="text-danger">' + errMsg + '</p><p class="small text-muted">' + detail + '</p>');
                        return;
                    }
                    var url = r.data.url;
                    $url.val(url);
                    $modal.data('allocation-id', r.data.allocation_id);
                    var qrImgUrl = (r.data.image && r.data.image.length > 0) ? r.data.image : ('https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(url));
                    $imgWrap.html('<img src="' + qrImgUrl + '" alt="二维码" style="max-width:200px;height:auto;">');
                }).fail(function (xhr, status, err) {
                    if (typeof console !== 'undefined' && console.error) {
                        var txt = (xhr && xhr.responseText) ? xhr.responseText.substring(0, 800) : '';
                        console.error('[分工二维码 getInfo] 请求失败:', { status: status, err: String(err), statusCode: xhr && xhr.status, responseText: txt });
                    }
                    var hint = (xhr && xhr.status === 200 && status === 'parsererror') ? '（服务器返回非 JSON，请查看控制台）' : ('（' + (xhr && xhr.status ? xhr.status : status) + '）');
                    $imgWrap.html('<p class="text-danger">请求失败 ' + hint + '</p>');
                });
            }
        }
    };

    window.__backendController = Controller;
})();
