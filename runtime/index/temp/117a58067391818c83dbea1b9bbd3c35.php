<?php /*a:1:{s:56:"/www/wwwroot/thinkmes/app/index/view/customer/login.html";i:1771720869;}*/ ?>
<section class="auth-hero">
<div class="auth-container">
  <div class="card auth-card">
    <div class="card-header">
      <h3 class="card-title">客户登录</h3>
    </div>
    <div class="card-body">
      <form id="form-customer-login">
        <div class="form-group">
          <label>登录账号</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa fa-user"></i></span>
            <input type="text" class="form-control" name="login_account" placeholder="请输入客户登录账号">
          </div>
        </div>
        <div class="form-group">
          <label>密码</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa fa-lock"></i></span>
            <input type="password" class="form-control" name="password" placeholder="请输入密码">
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">登录</button>
        <div class="text-right" style="margin-top:10px;">
          <a href="/index/user/login">返回用户登录</a>
        </div>
      </form>
    </div>
  </div>
</div>
</section>
<script>
$(function(){
  function getTenantFromQuery(){
    var params = new URLSearchParams(location.search);
    var tid = params.get('tenant_id') || params.get('tenant') || '';
    return tid || '';
  }
  $('#form-customer-login').on('submit', function(e){
    e.preventDefault();
    var $f = $(this);
    var account = $.trim($f.find('input[name="login_account"]').val() || '');
    var pwd = $f.find('input[name="password"]').val() || '';
    if(!account){
      alert('请输入登录账号');
      return;
    }
    if(!pwd){
      alert('请输入密码');
      return;
    }
    var data = {login_account: account, password: pwd};
    var tenantId = getTenantFromQuery();
    if(tenantId){
      data.tenant_id = tenantId;
    }
    $.post('/api/customer/login', data, function(r){
      if(r.code === 1){
        var tk = r.data && r.data.token ? r.data.token : '';
        localStorage.setItem('customer_token', tk);
        document.cookie = 'customer_token='+tk+'; path=/; max-age=2592000';
        location.href = '/index/customer/index';
      }else{
        alert(r.msg || '登录失败');
      }
    }, 'json').fail(function(){
      alert('请求失败，请稍后重试');
    });
  });
});
</script>

