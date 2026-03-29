(function () {
    var qs = new URLSearchParams(location.search || '');
    var token = (qs.get('token') || '').trim();
    var tenantId = parseInt(qs.get('tenant_id') || '0', 10) || 0;
    var apiBase = (location.origin || '') + '/api/restaurant';

    function ajaxGet(url, data) {
        data = data || {};
        if (tenantId) data.tenant_id = tenantId;
        return jQuery.getJSON(url, data);
    }
    function ajaxPost(url, data) {
        data = data || {};
        if (tenantId) data.tenant_id = tenantId;
        return jQuery.ajax({ url: url, method: 'POST', dataType: 'json', data: data });
    }
    function money(v) {
        var n = parseFloat(v || 0);
        if (isNaN(n)) n = 0;
        return n.toFixed(2);
    }
    function alertMsg(msg) {
        alert(msg || '操作失败');
    }

    var state = {
        categories: [],
        currentCatId: null,
        menuByCat: {},
        lastOptionItem: null
    };

    function loadTableInfo() {
        return ajaxGet(apiBase + '/table/info', { token: token }).done(function (r) {
            if (!r || r.code != 1) return;
            var d = r.data || {};
            var table = d.table || {};
            jQuery('#table-name').text(table.name || ('#' + (table.id || '')));
            jQuery('#store-name').text((d.store && d.store.name) ? ('门店：' + d.store.name) : '');
            jQuery('#area-name').text((d.area && d.area.name) ? ('区域：' + d.area.name) : '');
        });
    }

    function renderCategories(cats) {
        var $bar = jQuery('#category-bar');
        var html = '';
        for (var i = 0; i < cats.length; i++) {
            var c = cats[i];
            var active = (state.currentCatId === c.id) ? 'btn-primary' : 'btn-outline-primary';
            html += '<button class="btn btn-sm ' + active + ' btn-cat" data-id="' + c.id + '">' + (c.name || '') + '</button>';
        }
        $bar.html(html);
    }

    function itemCard(it) {
        var name = it.name || '';
        var price = money(it.price);
        var hasOpt = it.option_groups && it.option_groups.length;
        var btn = hasOpt
            ? '<button class="btn btn-sm btn-outline-primary btn-item-opt" data-id="' + it.id + '">选规格</button>'
            : '<button class="btn btn-sm btn-primary btn-item-add" data-id="' + it.id + '">加入</button>';
        return '<div class="item-card">' +
            '<div class="d-flex justify-content-between align-items-start">' +
                '<div>' +
                    '<div class="font-weight-bold">' + name + '</div>' +
                    '<div class="muted">¥<span class="price">' + price + '</span></div>' +
                '</div>' +
                '<div>' + btn + '</div>' +
            '</div>' +
        '</div>';
    }

    function comboCard(c) {
        var name = c.name || '';
        var price = money(c.price);
        var items = c.items || [];
        var parts = [];
        for (var i = 0; i < items.length; i++) {
            var it = items[i];
            parts.push((it.name || ('#' + it.item_id)) + '*' + (it.quantity || 1));
        }
        return '<div class="item-card">' +
            '<div class="d-flex justify-content-between align-items-start">' +
                '<div>' +
                    '<div class="font-weight-bold">' + name + ' <span class="badge badge-info">套餐</span></div>' +
                    '<div class="muted">' + (parts.length ? ('含：' + parts.join('，')) : '') + '</div>' +
                    '<div class="muted">¥<span class="price">' + price + '</span></div>' +
                '</div>' +
                '<div><button class="btn btn-sm btn-primary btn-combo-add" data-id="' + c.id + '">加入</button></div>' +
            '</div>' +
        '</div>';
    }

    function renderMenu(catId) {
        var data = state.menuByCat[catId] || { items: [], combos: [] };
        var html = '';
        if (data.combos && data.combos.length) {
            html += '<div class="mb-2 font-weight-bold">套餐</div>';
            for (var i = 0; i < data.combos.length; i++) html += comboCard(data.combos[i]);
        }
        if (data.items && data.items.length) {
            html += '<div class="mb-2 font-weight-bold">菜品</div>';
            for (var j = 0; j < data.items.length; j++) html += itemCard(data.items[j]);
        }
        if (!html) html = '<div class="text-muted p-3">暂无可售商品</div>';
        jQuery('#menu-list').html(html);
    }

    function loadMenu() {
        return ajaxGet(apiBase + '/menu', { token: token }).done(function (r) {
            if (!r || r.code != 1) { alertMsg(r ? r.msg : '加载失败'); return; }
            var cats = (r.data && r.data.categories) ? r.data.categories : [];
            state.categories = cats;
            if (state.currentCatId === null && cats.length) state.currentCatId = cats[0].id;
            state.menuByCat = {};
            for (var i = 0; i < cats.length; i++) {
                state.menuByCat[cats[i].id] = { items: cats[i].items || [], combos: cats[i].combos || [] };
            }
            renderCategories(cats);
            renderMenu(state.currentCatId);
        });
    }

    function openOptions(itemId) {
        var it = null;
        var catIds = Object.keys(state.menuByCat);
        for (var i = 0; i < catIds.length; i++) {
            var data = state.menuByCat[catIds[i]];
            var items = data.items || [];
            for (var j = 0; j < items.length; j++) {
                if (parseInt(items[j].id, 10) === parseInt(itemId, 10)) { it = items[j]; break; }
            }
            if (it) break;
        }
        if (!it) return;
        state.lastOptionItem = it;
        jQuery('#opt-title').text('选择：' + (it.name || ''));
        var groups = it.option_groups || [];
        var html = '';
        for (var g = 0; g < groups.length; g++) {
            var grp = groups[g];
            var opts = grp.options || [];
            html += '<div class="mb-3">';
            var minSel = parseInt(grp.min_select || 0, 10) || 0;
            var maxSel = parseInt(grp.max_select || 0, 10) || 1;
            var reqText = grp.required == 1 ? '（必选' : '（可选';
            var rangeText = '，需选' + (grp.required == 1 && minSel < 1 ? 1 : minSel) + '-' + maxSel + '项）';
            html += '<div class="font-weight-bold">' + (grp.name || '') + reqText + rangeText + '</div>';
            for (var o = 0; o < opts.length; o++) {
                var op = opts[o];
                var id = op.id;
                var label = (op.name || '') + (parseFloat(op.price_delta || 0) ? (' +' + money(op.price_delta)) : '');
                html += '<div class="custom-control custom-checkbox">';
                html += '<input type="checkbox" class="custom-control-input opt-check" id="opt_' + id + '" data-group="' + grp.id + '" data-min="' + minSel + '" data-max="' + maxSel + '" data-required="' + (grp.required == 1 ? 1 : 0) + '" value="' + id + '">';
                html += '<label class="custom-control-label" for="opt_' + id + '">' + label + '</label>';
                html += '</div>';
            }
            html += '</div>';
        }
        jQuery('#opt-body').html(html || '<div class="text-muted">无可选规格</div>');
        jQuery('#modal-options').modal('show');
    }

    function addItem(itemId, optionIds) {
        return ajaxPost(apiBase + '/cart/add', { token: token, product_type: 'item', item_id: itemId, quantity: 1, option_ids: optionIds || [] }).done(function (r) {
            if (!r || r.code != 1) { alertMsg(r ? r.msg : '加入失败'); return; }
            refreshCart();
        });
    }
    function addCombo(comboId) {
        return ajaxPost(apiBase + '/cart/add', { token: token, product_type: 'combo', combo_id: comboId, quantity: 1 }).done(function (r) {
            if (!r || r.code != 1) { alertMsg(r ? r.msg : '加入失败'); return; }
            refreshCart();
        });
    }

    function renderCart(d) {
        var items = d.items || [];
        var html = '';
        if (!items.length) {
            html = '<div class="text-muted">购物车为空</div>';
            jQuery('#cart-body').html(html);
            jQuery('#cart-total').text(money(0));
            return;
        }
        for (var i = 0; i < items.length; i++) {
            var it = items[i];
            html += '<div class="d-flex align-items-center justify-content-between border-bottom py-2">';
            html += '<div class="mr-2">';
            html += '<div class="font-weight-bold">' + (it.name || '') + '</div>';
            html += '<div class="muted">¥' + money(it.price) + '</div>';
            html += '</div>';
            html += '<div class="d-flex align-items-center">';
            html += '<button class="btn btn-sm btn-outline-secondary btn-qty" data-id="' + it.id + '" data-delta="-1">-</button>';
            html += '<span class="mx-2" style="min-width:40px;text-align:center">' + (it.quantity || 0) + '</span>';
            html += '<button class="btn btn-sm btn-outline-secondary btn-qty" data-id="' + it.id + '" data-delta="1">+</button>';
            html += '<button class="btn btn-sm btn-link text-danger btn-remove" data-id="' + it.id + '">删除</button>';
            html += '</div>';
            html += '</div>';
        }
        jQuery('#cart-body').html(html);
        jQuery('#cart-total').text(money(d.total_amount || 0));
    }

    function refreshCart() {
        return ajaxGet(apiBase + '/cart/get', { token: token }).done(function (r) {
            if (!r || r.code != 1) return;
            renderCart(r.data || {});
        });
    }

    function checkout() {
        return ajaxPost(apiBase + '/order/create', { token: token, remark: '' }).done(function (r) {
            if (!r || r.code != 1) { alertMsg(r ? r.msg : '下单失败'); return; }
            jQuery('#modal-cart').modal('hide');
            refreshCart();
            loadOrders();
            openPay(r.data || {});
        });
    }

    function openPay(orderData) {
        var orderId = orderData.order_id;
        var orderNo = orderData.order_no;
        var total = money(orderData.total_amount || 0);
        ajaxGet(apiBase + '/payment/gateways', {}).done(function (r) {
            if (!r || r.code != 1) { alertMsg(r ? r.msg : '获取支付方式失败'); return; }
            var list = (r.data && r.data.list) ? r.data.list : [];
            var html = '<div class="mb-2">订单号：<span class="font-weight-bold">' + (orderNo || '') + '</span></div>';
            html += '<div class="mb-3">金额：<span class="font-weight-bold">¥' + total + '</span></div>';
            if (!list.length) {
                html += '<div class="text-muted">未配置支付方式</div>';
            } else {
                for (var i = 0; i < list.length; i++) {
                    var g = list[i];
                    html += '<button class="btn btn-outline-primary btn-block btn-pay" data-gid="' + g.id + '" data-oid="' + orderId + '">' + (g.name || g.code || ('网关#' + g.id)) + '</button>';
                }
            }
            jQuery('#pay-body').html(html);
            jQuery('#modal-pay').modal('show');
        });
    }

    function loadOrders() {
        return ajaxGet(apiBase + '/order/list', { token: token }).done(function (r) {
            if (!r || r.code != 1) return;
            var list = (r.data && r.data.list) ? r.data.list : [];
            if (!list.length) { jQuery('#order-list').html('<div class="text-muted p-2">暂无订单</div>'); return; }
            var html = '';
            for (var i = 0; i < list.length; i++) {
                var o = list[i];
                html += '<div class="item-card">';
                html += '<div class="d-flex justify-content-between"><div class="font-weight-bold">' + (o.order_no || '') + '</div><div>¥' + money(o.total_amount) + '</div></div>';
                html += '<div class="muted">状态：' + (o.status != null ? o.status : '-') + ' 时间：' + (o.create_time ? new Date(o.create_time * 1000).toLocaleString() : '-') + '</div>';
                html += '<div class="text-right"><button class="btn btn-sm btn-outline-primary btn-order-detail" data-id="' + o.id + '">详情</button></div>';
                html += '</div>';
            }
            jQuery('#order-list').html(html);
        });
    }

    function bindEvents() {
        jQuery(document).on('click', '.btn-cat', function () {
            var id = parseInt(jQuery(this).data('id'), 10);
            state.currentCatId = id;
            renderCategories(state.categories);
            renderMenu(id);
        });
        jQuery(document).on('click', '.btn-item-add', function () {
            addItem(parseInt(jQuery(this).data('id'), 10), []);
        });
        jQuery(document).on('click', '.btn-item-opt', function () {
            openOptions(parseInt(jQuery(this).data('id'), 10));
        });
        jQuery('#btn-opt-confirm').on('click', function () {
            var it = state.lastOptionItem;
            if (!it) return;
            var ids = [];
            var byGroup = {};
            jQuery('#opt-body .opt-check:checked').each(function () {
                var gid = parseInt(jQuery(this).data('group'), 10);
                var minSel = parseInt(jQuery(this).data('min'), 10) || 0;
                var maxSel = parseInt(jQuery(this).data('max'), 10) || 1;
                var required = parseInt(jQuery(this).data('required'), 10) || 0;
                ids.push(parseInt(this.value, 10));
                if (!byGroup[gid]) byGroup[gid] = { count: 0, min: minSel, max: maxSel, required: required };
                byGroup[gid].count++;
            });
            var groups = it.option_groups || [];
            for (var i = 0; i < groups.length; i++) {
                var g = groups[i];
                var gid = parseInt(g.id, 10);
                var info = byGroup[gid] || { count: 0, min: parseInt(g.min_select || 0, 10) || 0, max: parseInt(g.max_select || 0, 10) || 1, required: (g.required == 1 ? 1 : 0) };
                var minNeed = info.required ? (info.min < 1 ? 1 : info.min) : 0;
                if (info.required && info.count < minNeed) {
                    alert('请在「' + (g.name || '分组') + '」中至少选择 ' + minNeed + ' 项');
                    return;
                }
                if (info.count > info.max) {
                    alert('「' + (g.name || '分组') + '」最多选择 ' + info.max + ' 项');
                    return;
                }
            }
            jQuery('#modal-options').modal('hide');
            addItem(parseInt(it.id, 10), ids);
        });
        jQuery(document).on('click', '.btn-combo-add', function () {
            addCombo(parseInt(jQuery(this).data('id'), 10));
        });

        jQuery('#btn-open-cart,#btn-open-cart-2').on('click', function () {
            refreshCart().done(function () { jQuery('#modal-cart').modal('show'); });
        });
        jQuery(document).on('click', '.btn-qty', function () {
            var id = parseInt(jQuery(this).data('id'), 10);
            var delta = parseInt(jQuery(this).data('delta'), 10);
            ajaxGet(apiBase + '/cart/get', { token: token }).done(function (r) {
                if (!r || r.code != 1) return;
                var items = (r.data && r.data.items) ? r.data.items : [];
                var cur = null;
                for (var i = 0; i < items.length; i++) if (items[i].id == id) { cur = items[i]; break; }
                if (!cur) return;
                var q = parseFloat(cur.quantity || 0) + delta;
                if (q < 0) q = 0;
                ajaxPost(apiBase + '/cart/update', { token: token, id: id, quantity: q }).done(function () { refreshCart(); });
            });
        });
        jQuery(document).on('click', '.btn-remove', function () {
            var id = parseInt(jQuery(this).data('id'), 10);
            ajaxPost(apiBase + '/cart/remove', { token: token, id: id }).done(function () { refreshCart(); });
        });
        jQuery('#btn-cart-clear').on('click', function () {
            ajaxPost(apiBase + '/cart/clear', { token: token }).done(function () { refreshCart(); });
        });
        jQuery('#btn-checkout,#btn-cart-checkout').on('click', function () { checkout(); });
        jQuery('#btn-refresh-orders').on('click', function () { loadOrders(); });
        jQuery(document).on('click', '.btn-pay', function () {
            var gid = parseInt(jQuery(this).data('gid'), 10);
            var oid = parseInt(jQuery(this).data('oid'), 10);
            ajaxPost(apiBase + '/payment/pay', { token: token, gateway_id: gid, order_id: oid, return_url: location.href }).done(function (r) {
                if (!r || r.code != 1) { alertMsg(r ? r.msg : '发起支付失败'); return; }
                var d = r.data || {};
                if (d.form_html) {
                    var $box = jQuery('#pay-body');
                    $box.append(d.form_html);
                    var $f = $box.find('form').last();
                    if ($f.length) $f.submit();
                    return;
                }
                if (d.pay_url) {
                    location.href = d.pay_url;
                }
            });
        });
        jQuery('a[data-toggle="tab"][href="#tab-orders"]').on('shown.bs.tab', function () { loadOrders(); });
        jQuery(document).on('click', '.btn-order-detail', function () {
            var id = parseInt(jQuery(this).data('id'), 10);
            ajaxGet(apiBase + '/order/detail', { token: token, id: id }).done(function (r) {
                if (!r || r.code != 1) { alertMsg(r ? r.msg : '加载失败'); return; }
                var d = r.data || {};
                var o = d.order || {};
                var items = d.items || [];
                var html = '';
                html += '<div class="mb-2">订单号：<span class="font-weight-bold">' + (o.order_no || '') + '</span></div>';
                html += '<div class="mb-2">金额：<span class="font-weight-bold">¥' + money(o.total_amount || 0) + '</span></div>';
                html += '<div class="mb-2">状态：' + (o.status != null ? o.status : '-') + '</div>';
                html += '<table class="table table-sm table-bordered"><thead><tr><th>菜品</th><th>规格/套餐</th><th>单价</th><th>数量</th><th>金额</th></tr></thead><tbody>';
                if (!items.length) {
                    html += '<tr><td colspan="5" class="text-muted">暂无明细</td></tr>';
                } else {
                    for (var i = 0; i < items.length; i++) {
                        var it = items[i];
                        html += '<tr><td>' + (it.name || ('#' + it.item_id)) + '</td><td>' + (it.options_text || '') + '</td><td>' + money(it.price) + '</td><td>' + (it.quantity || 0) + '</td><td>' + money(it.amount) + '</td></tr>';
                    }
                }
                html += '</tbody></table>';
                jQuery('#order-detail-body').html(html);
                jQuery('#modal-order-detail').modal('show');
            });
        });
    }

    function init() {
        if (!token) {
            alertMsg('缺少 token，请从桌码二维码进入');
            return;
        }
        bindEvents();
        jQuery.when(loadTableInfo(), loadMenu(), refreshCart()).always(function () {});
    }

    jQuery(init);
})();
