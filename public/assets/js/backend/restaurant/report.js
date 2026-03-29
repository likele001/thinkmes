(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    function money(v) {
        var n = parseFloat(v || 0);
        if (isNaN(n)) n = 0;
        return n.toFixed(2);
    }
    function load() {
        jQuery.getJSON(base + '/restaurant/report/overview', function (r) {
            if (!r || r.code != 1) return;
            var d = r.data || {};
            jQuery('#today-orders').text(d.today_orders != null ? d.today_orders : '-');
            jQuery('#today-revenue').text(money(d.today_revenue));
            jQuery('#unpaid-orders').text(d.unpaid_orders != null ? d.unpaid_orders : '-');
            var $tb = jQuery('#top-items');
            var rows = d.top_items || [];
            if (!rows.length) {
                $tb.html('<tr><td colspan="3">暂无数据</td></tr>');
                return;
            }
            var html = '';
            for (var i = 0; i < rows.length; i++) {
                var it = rows[i];
                html += '<tr><td>' + (it.name || ('#' + it.item_id)) + '</td><td>' + (it.qty || 0) + '</td><td>' + money(it.amount) + '</td></tr>';
            }
            $tb.html(html);
        });
    }
    var Controller = {
        index: function () {
            load();
            jQuery(document).off('click', '.btn-refresh').on('click', '.btn-refresh', function () { load(); });
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();

