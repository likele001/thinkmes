<?php /*a:1:{s:56:"/www/wwwroot/thinkmes/app/index/view/customer/index.html";i:1771721109;}*/ ?>
<div class="container-main">
  <div class="member-layout">
    <div class="member-left">
      <div class="card">
        <div class="card-body">
          <ul class="nav nav-pills flex-column">
            <li class="nav-item"><a class="nav-link active" href="/index/customer/index" data-i18n="nav_place_order"><i class="fa fa-shopping-cart mr-2"></i>下单</a></li>
            <li class="nav-item"><a class="nav-link" href="/index/customer/orders" data-i18n="nav_orders"><i class="fa fa-list mr-2"></i>我的订单</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="member-right">
      <div class="card">
        <div class="card-body">
          <div style="display:flex;align-items:center;gap:16px;">
            <div class="avatar-circle" id="avatar">C</div>
            <div style="flex:1;">
              <div style="display:flex;align-items:center;gap:8px;">
                <strong id="customer-name">-</strong>
                <span id="customer-code" style="color:#666;"></span>
              </div>
              <div style="color:#666;margin-top:6px;" data-i18n="label_tenant">所属企业 <span id="tenant-name">-</span></div>
              <div style="color:#666;margin-top:6px;" data-i18n="label_contact">联系人 <span id="contact-name">-</span> ｜ 电话 <span id="contact-phone">-</span></div>
            </div>
            <div>
              <a class="btn btn-outline-secondary" href="/index/customer/orders" data-i18n="btn_view_orders"><i class="fa fa-list"></i> 查看订单</a>
            </div>
          </div>
        </div>
      </div>
      <div class="card" style="margin-top:10px;">
        <div class="card-header">
          <h5 class="card-title" data-i18n="title_products">可下单产品</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th data-i18n="th_product">产品型号</th>
                  <th data-i18n="th_price">单价</th>
                  <th data-i18n="th_currency">币种</th>
                  <th data-i18n="th_min_qty">起订量</th>
                  <th data-i18n="th_quantity">下单数量</th>
                </tr>
              </thead>
              <tbody id="product-list">
              </tbody>
            </table>
          </div>
          <div class="form-group" style="margin-top:10px;">
            <label data-i18n="label_order_remark">订单备注</label>
            <input type="text" class="form-control" id="order-remark" maxlength="255" placeholder="可选，填写给工厂的备注">
          </div>
          <div style="text-align:right;margin-top:10px;">
            <button class="btn btn-primary" id="btn-submit-order" data-i18n="btn_submit_order"><i class="fa fa-check"></i> 提交订单</button>
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
  function applyLang(lang){
    var dict = {
      'zh-cn': {
        nav_place_order: '下单',
        nav_orders: '我的订单',
        label_tenant: '所属企业 ',
        label_contact: '联系人 ',
        btn_view_orders: '查看订单',
        title_products: '可下单产品',
        th_product: '产品型号',
        th_price: '单价',
        th_currency: '币种',
        th_min_qty: '起订量',
        th_quantity: '下单数量',
        label_order_remark: '订单备注',
        btn_submit_order: '提交订单'
      },
      'en-us': {
        nav_place_order: 'Place Order',
        nav_orders: 'My Orders',
        label_tenant: 'Tenant ',
        label_contact: 'Contact ',
        btn_view_orders: 'View Orders',
        title_products: 'Orderable Products',
        th_product: 'Product',
        th_price: 'Price',
        th_currency: 'Currency',
        th_min_qty: 'Min Qty',
        th_quantity: 'Quantity',
        label_order_remark: 'Order Remark',
        btn_submit_order: 'Submit Order'
      }
    };
    var d = dict[lang] || dict['zh-cn'];
    $('[data-i18n]').each(function(){
      var key = $(this).data('i18n');
      if(d[key]){
        var html = $(this).html();
        if(html.indexOf('<span') >= 0){
          var span = $(this).find('span')[0];
          var spanHtml = span ? span.outerHTML : '';
          $(this).html(d[key] + ' ' + spanHtml);
        }else{
          $(this).text(d[key]);
        }
      }
    });
  }
  function loadProfile(){
    $.ajax({
      url:'/api/customer/profile',
      headers:{'Authorization':'Bearer '+tk},
      success:function(r){
        if(r.code !== 1){
          location.href = '/index/customer/login';
          return;
        }
        var c = r.data || {};
        var name = c.customer_name || c.name || '-';
        $('#customer-name').text(name);
        $('#customer-code').text(c.customer_code || '');
        $('#tenant-name').text(c.tenant_company_name || c.tenant_name || '-');
        $('#contact-name').text(c.contact_name || '-');
        $('#contact-phone').text(c.contact_phone || '-');
        var avatar = (name || 'C').substr(0,1).toUpperCase();
        $('#avatar').text(avatar);
        var lang = c.default_lang || 'zh-cn';
        applyLang(lang.toLowerCase());
      }
    });
  }
  function loadProducts(){
    $.ajax({
      url:'/api/customer/products',
      headers:{'Authorization':'Bearer '+tk},
      success:function(r){
        if(r.code !== 1){
          $('#product-list').html('<tr><td colspan="5" class="text-center">加载失败</td></tr>');
          return;
        }
        var list = (r.data && r.data.list) ? r.data.list : [];
        if(!list.length){
          $('#product-list').html('<tr><td colspan="5" class="text-center">暂无可下单产品</td></tr>');
          return;
        }
        var html = '';
        list.forEach(function(it){
          html += '<tr data-id="'+it.id+'">'
            + '<td>'+(it.name||'')+'</td>'
            + '<td>'+(it.price||0)+'</td>'
            + '<td>'+(it.currency||'')+'</td>'
            + '<td>'+(it.min_qty||1)+'</td>'
            + '<td><input type="number" class="form-control input-qty" min="'+(it.min_qty||1)+'" placeholder="'+(it.min_qty||1)+'"></td>'
            + '</tr>';
        });
        $('#product-list').html(html);
      },
      error:function(){
        $('#product-list').html('<tr><td colspan="5" class="text-center">加载失败</td></tr>');
      }
    });
  }
  $('#btn-submit-order').on('click', function(){
    var items = [];
    $('#product-list tr').each(function(){
      var id = parseInt($(this).data('id') || 0, 10);
      var qty = parseInt($(this).find('.input-qty').val() || 0, 10);
      if(id && qty > 0){
        items.push({customer_product_id:id, quantity:qty});
      }
    });
    if(!items.length){
      alert('请至少选择一个产品并填写数量');
      return;
    }
    var payload = {
      items: JSON.stringify(items),
      remark: $('#order-remark').val() || ''
    };
    $.post({
      url:'/api/customer/createOrder',
      headers:{'Authorization':'Bearer '+tk},
      data:payload,
      success:function(r){
        if(r.msg){
          alert(r.msg);
        }
        if(r.code === 1){
          location.href = '/index/customer/orders';
        }
      },
      error:function(){
        alert('下单失败，请稍后重试');
      }
    });
  });
  loadProfile();
  loadProducts();
});
</script>

