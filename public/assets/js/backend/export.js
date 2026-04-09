/**
 * 数据导出
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var downloadUrl = base + '/export/download';

    function buildUrl() {
        var type = $('#exp-type').val() || 'orders';
        var orderNo = $('#exp-order-no').val() || '';
        var from = $('#exp-from').val() || '';
        var to = $('#exp-to').val() || '';
        var status = $('#exp-status').val() || '';
        var params = ['type=' + encodeURIComponent(type)];
        if (orderNo) params.push('order_no=' + encodeURIComponent(orderNo));
        if (from) params.push('from=' + encodeURIComponent(from));
        if (to) params.push('to=' + encodeURIComponent(to));
        if (type === 'reports' && status !== '') params.push('status=' + encodeURIComponent(status));
        return downloadUrl + '?' + params.join('&');
    }

    var Controller = {
        index: function () {
            $('#btn-export').off('click').on('click', function () {
                window.location.href = buildUrl();
            });
            $('#exp-type').off('change').on('change', function () {
                var t = $('#exp-type').val();
                if (t === 'reports') $('#exp-status').show();
                else $('#exp-status').hide();
            }).trigger('change');
        }
    };

    window.__backendController = Controller;
})();

