/**
 * 客户中心 - 我的订单页
 * 独立 JS 避免模板引擎解析 { } 导致语法错误
 */
$(function () {
  var tokenKey = 'customer_token';
  var tk = localStorage.getItem(tokenKey) || '';
  if (!tk) {
    location.href = '/index/customer/login';
    return;
  }
  var currentLang = 'zh-cn';

  function fmt(t) {
    if (t == null || t === '' || (typeof t === 'number' && (t <= 0 || t < 86400))) return '-';
    var d = new Date((typeof t === 'number' ? t : parseInt(t, 10)) * 1000);
    if (isNaN(d.getTime())) return '-';
    var y = d.getFullYear(), m = ('0' + (d.getMonth() + 1)).slice(-2), dd = ('0' + d.getDate()).slice(-2);
    var hh = ('0' + d.getHours()).slice(-2), mm = ('0' + d.getMinutes()).slice(-2);
    return y + '-' + m + '-' + dd + ' ' + hh + ':' + mm;
  }

  function statusText(s, lang) {
    var zh = { 0: '待确认', 1: '已确认', 2: '生产中', 3: '已发货', 4: '已完成', 5: '已取消' };
    var en = { 0: 'Pending', 1: 'Confirmed', 2: 'In Production', 3: 'Shipped', 4: 'Completed', 5: 'Canceled' };
    return (lang === 'en-us' ? en : zh)[s] || s;
  }

  function applyLang(lang) {
    currentLang = lang || currentLang;
    var dict = {
      'zh-cn': { nav_place_order: '下单', nav_orders: '我的订单', title_orders: '我的订单', th_order_no: '订单号', th_status: '状态', th_amount: '金额', th_currency: '币种', th_time: '下单时间', th_action: '操作', btn_detail: '明细', btn_confirm: '确认订单', btn_save: '保存修改', th_product: '产品', th_qty: '数量', th_progress: '生产进度', btn_del: '删除', progress_done: '已完成', progress_pending: '待排产' },
      'en-us': { nav_place_order: 'Place Order', nav_orders: 'My Orders', title_orders: 'My Orders', th_order_no: 'Order No', th_status: 'Status', th_amount: 'Amount', th_currency: 'Currency', th_time: 'Created At', th_action: 'Action', btn_detail: 'Items', btn_confirm: 'Confirm Order', btn_save: 'Save', th_product: 'Product', th_qty: 'Qty', th_progress: 'Progress', btn_del: 'Delete', progress_done: 'Done', progress_pending: 'Pending' }
    };
    var d = dict[currentLang] || dict['zh-cn'];
    $('[data-i18n]').each(function () {
      var k = $(this).data('i18n');
      if (d[k]) $(this).text(d[k]);
    });
    return d;
  }

  function renderItemRow(it, status, lang) {
    var d = { btn_del: '删除', progress_done: '已完成', progress_pending: '待排产' };
    if (lang === 'en-us') {
      d.btn_del = 'Delete';
      d.progress_done = 'Done';
      d.progress_pending = 'Pending';
    }
    var name = (it.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    var qty = parseInt(it.quantity || 0, 10);
    var progressQ = parseInt(it.progress_quantity || 0, 10);
    var progressC = parseInt(it.progress_completed || 0, 10);
    var qtyCell = status === 0 ? '<input type="number" class="form-control form-control-sm input-item-qty" data-id="' + it.id + '" min="1" value="' + qty + '" style="width:80px;">' : '' + qty;
    var lastCell = status === 0 ? '<button type="button" class="btn btn-sm btn-outline-danger btn-del-item" data-id="' + it.id + '">' + d.btn_del + '</button>' : (progressQ > 0 ? d.progress_done + ' ' + progressC + '/' + progressQ : d.progress_pending);
    return '<tr data-item-id="' + it.id + '"><td>' + name + '</td><td>' + qtyCell + '</td><td>' + lastCell + '</td></tr>';
  }

  function renderOrderDetail(o, lang) {
    var items = o.items || [], status = parseInt(o.status || 0, 10), d = applyLang(lang);
    var rows = items.map(function (it) { return renderItemRow(it, status, lang); }).join('');
    var thead = status === 0 ? '<thead><tr><th>' + d.th_product + '</th><th>' + d.th_qty + '</th><th>' + d.btn_del + '</th></tr></thead>' : '<thead><tr><th>' + d.th_product + '</th><th>' + d.th_qty + '</th><th>' + d.th_progress + '</th></tr></thead>';
    var actions = status === 0 ? '<div class="mt-2"><button type="button" class="btn btn-sm btn-primary btn-save-order" data-id="' + o.id + '">' + d.btn_save + '</button> <button type="button" class="btn btn-sm btn-success btn-confirm-order" data-id="' + o.id + '">' + d.btn_confirm + '</button></div>' : '';
    return '<div class="order-detail" data-order-id="' + o.id + '"><table class="table table-sm table-bordered">' + thead + '<tbody>' + rows + '</tbody></table>' + actions + '</div>';
  }

  function loadProfileAndOrders() {
    $.ajax({
      url: '/api/customer/profile',
      headers: { 'Authorization': 'Bearer ' + tk },
      success: function (r) {
        if (r.code !== 1) {
          location.href = '/index/customer/login';
          return;
        }
        var c = r.data || {};
        currentLang = (c.default_lang || 'zh-cn').toLowerCase();
        applyLang(currentLang);
        loadOrders(currentLang);
      },
      error: function () {
        applyLang('zh-cn');
        loadOrders('zh-cn');
      }
    });
  }

  function loadOrders(lang) {
    $.ajax({
      url: '/api/customer/orders',
      headers: { 'Authorization': 'Bearer ' + tk },
      success: function (r) {
        if (r.code !== 1) {
          $('#order-list').html('<tr><td colspan="6" class="text-center">加载失败</td></tr>');
          return;
        }
        var list = (r.data && r.data.list) ? r.data.list : [];
        if (!Array.isArray(list)) list = [];
        if (!list.length) {
          $('#order-list').html('<tr><td colspan="6" class="text-center">暂无订单</td></tr>');
          return;
        }
        var html = '';
        list.forEach(function (o) {
          var orderId = o.id, status = parseInt(o.status || 0, 10), detail = renderOrderDetail(o, lang);
          html += '<tr class="order-row" data-order-id="' + orderId + '"><td>' + (o.customer_order_no || '') + '</td><td>' + statusText(status, lang) + '</td><td>' + (o.total_amount || 0) + '</td><td>' + (o.currency || '') + '</td><td>' + fmt(o.create_time || 0) + '</td><td><button type="button" class="btn btn-sm btn-outline-secondary btn-toggle-detail" data-id="' + orderId + '">' + (applyLang(lang).btn_detail) + '</button></td></tr>';
          html += '<tr class="detail-row" data-order-id="' + orderId + '" style="display:none;"><td colspan="6" class="p-3 bg-light">' + detail + '</td></tr>';
        });
        $('#order-list').html(html);
      },
      error: function () {
        $('#order-list').html('<tr><td colspan="6" class="text-center">加载失败</td></tr>');
      }
    });
  }

  $(document).on('click', '.btn-toggle-detail', function () {
    var id = $(this).data('id');
    $('.detail-row[data-order-id="' + id + '"]').toggle();
  });
  $(document).on('click', '.btn-del-item', function () {
    $(this).closest('tr').remove();
  });
  $(document).on('click', '.btn-save-order', function () {
    var orderId = $(this).data('id'), detail = $('.order-detail[data-order-id="' + orderId + '"]'), items = [];
    detail.find('.input-item-qty').each(function () {
      var id = parseInt($(this).data('id'), 10), qty = parseInt($(this).val(), 10);
      if (id && qty > 0) items.push({ id: id, quantity: qty });
    });
    if (!items.length) {
      alert('请保留至少一条有效明细');
      return;
    }
    $.ajax({
      url: '/api/customer/updateOrder',
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + tk },
      data: { customer_order_id: orderId, items_json: JSON.stringify(items) },
      success: function (r) {
        if (r.msg) alert(r.msg);
        if (r.code === 1) loadOrders(currentLang);
      },
      error: function () { alert('操作失败'); }
    });
  });
  $(document).on('click', '.btn-confirm-order', function () {
    var orderId = $(this).data('id');
    if (!confirm('确认后不可再修改数量或删除产品，确定要确认订单吗？')) return;
    $.ajax({
      url: '/api/customer/confirmOrder',
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + tk },
      data: { customer_order_id: orderId },
      success: function (r) {
        if (r.msg) alert(r.msg);
        if (r.code === 1) loadOrders(currentLang);
      },
      error: function () { alert('操作失败'); }
    });
  });

  loadProfileAndOrders();
});
