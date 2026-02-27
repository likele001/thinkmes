<?php /*a:1:{s:57:"/www/wwwroot/thinkmes/app/index/view/customer/orders.html";i:1771721146;}*/ ?>
<div class="container-main">
  <div class="member-layout">
    <div class="member-left">
      <div class="card">
        <div class="card-body">
          <ul class="nav nav-pills flex-column">
            <li class="nav-item"><a class="nav-link" href="/index/customer/index" data-i18n="nav_place_order"><i class="fa fa-shopping-cart mr-2"></i>下单</a></li>
            <li class="nav-item"><a class="nav-link active" href="/index/customer/orders" data-i18n="nav_orders"><i class="fa fa-list mr-2"></i>我的订单</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="member-right">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title" data-i18n="title_orders">我的订单</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th data-i18n="th_order_no">订单号</th>
                  <th data-i18n="th_status">状态</th>
                  <th data-i18n="th_amount">金额</th>
                  <th data-i18n="th_currency">币种</th>
                  <th data-i18n="th_time">下单时间</th>
                  <th data-i18n="th_items">明细</th>
                </tr>
              </thead>
              <tbody id="order-list">
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(function(){
  var tokenKey = 'customer_token';
  var tk = localStorage.getItem(tokenKey) || '';
  if(!tk){
    location.href = '/index/customer/login';
    return;
  }
  function fmt(t){
    if(!t){return '-';}
    var d = new Date(t*1000);
    var y = d.getFullYear();
    var m = ('0'+(d.getMonth()+1)).slice(-2);
    var dd = ('0'+d.getDate()).slice(-2);
    var hh = ('0'+d.getHours()).slice(-2);
    var mm = ('0'+d.getMinutes()).slice(-2);
    return y+'-'+m+'-'+dd+' '+hh+':'+mm;
  }
  function statusText(s, lang){
    var zh = {
      0:'待确认',
      1:'已确认',
      2:'生产中',
      3:'已发货',
      4:'已完成',
      5:'已取消'
    };
    var en = {
      0:'Pending',
      1:'Confirmed',
      2:'In Production',
      3:'Shipped',
      4:'Completed',
      5:'Canceled'
    };
    var map = lang === 'en-us' ? en : zh;
    return map[s] || s;
  }
  function applyLang(lang){
    var dict = {
      'zh-cn': {
        nav_place_order: '下单',
        nav_orders: '我的订单',
        title_orders: '我的订单',
        th_order_no: '订单号',
        th_status: '状态',
        th_amount: '金额',
        th_currency: '币种',
        th_time: '下单时间',
        th_items: '明细'
      },
      'en-us': {
        nav_place_order: 'Place Order',
        nav_orders: 'My Orders',
        title_orders: 'My Orders',
        th_order_no: 'Order No',
        th_status: 'Status',
        th_amount: 'Amount',
        th_currency: 'Currency',
        th_time: 'Created At',
        th_items: 'Items'
      }
    };
    var d = dict[lang] || dict['zh-cn'];
    $('[data-i18n]').each(function(){
      var key = $(this).data('i18n');
      if(d[key]){
        $(this).text(d[key]);
      }
    });
  }
  function loadProfileAndOrders(){
    $.ajax({
      url:'/api/customer/profile',
      headers:{'Authorization':'Bearer '+tk},
      success:function(r){
        if(r.code !== 1){
          location.href = '/index/customer/login';
          return;
        }
        var c = r.data || {};
        var lang = (c.default_lang || 'zh-cn').toLowerCase();
        applyLang(lang);
        loadOrders(lang);
      },
      error:function(){
        applyLang('zh-cn');
        loadOrders('zh-cn');
      }
    });
  }
  function loadOrders(lang){
    $.ajax({
      url:'/api/customer/orders',
      headers:{'Authorization':'Bearer '+tk},
      success:function(r){
        if(r.code !== 1){
          $('#order-list').html('<tr><td colspan="6" class="text-center">加载失败</td></tr>');
          return;
        }
        var list = (r.data && r.data.list) ? r.data.list : [];
        if(!list.length){
          $('#order-list').html('<tr><td colspan="6" class="text-center">暂无订单</td></tr>');
          return;
        }
        var html = '';
        list.forEach(function(o){
          var items = o.items || [];
          var itemHtml = '';
          items.forEach(function(it){
            var name = it.name || '';
            itemHtml += '<div>'+name+' × '+(it.quantity||0)+'</div>';
          });
          if(!itemHtml){
            itemHtml = '-';
          }
          html += '<tr>'
            + '<td>'+(o.customer_order_no||'')+'</td>'
            + '<td>'+statusText(parseInt(o.status||0,10), lang)+'</td>'
            + '<td>'+(o.total_amount||0)+'</td>'
            + '<td>'+(o.currency||'')+'</td>'
            + '<td>'+fmt(o.create_time||0)+'</td>'
            + '<td>'+itemHtml+'</td>'
            + '</tr>';
        });
        $('#order-list').html(html);
      },
      error:function(){
        $('#order-list').html('<tr><td colspan="6" class="text-center">加载失败</td></tr>');
      }
    });
  }
  loadProfileAndOrders();
});
</script>

