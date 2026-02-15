<?php /*a:1:{s:56:"/www/wwwroot/thinkmes/app/index/view/user/changepwd.html";i:1771112068;}*/ ?>
<div class="card" style="max-width:520px;margin:20px auto;">
  <div class="card-header">修改密码</div>
  <div class="card-body">
    <form id="form-changepwd">
      <div class="form-group">
        <label>新密码</label>
        <input type="password" class="form-control" name="newpassword" placeholder="新密码">
      </div>
      <button type="submit" class="btn btn-primary btn-block">保存</button>
    </form>
  </div>
</div>
<script>
$(function(){
  var tk=localStorage.getItem('token')||'';
  if(!tk){location.href='/user/login';return;}
  $('#form-changepwd').on('submit', function(e){
    e.preventDefault();
    var data=$(this).serialize();
    $.ajax({
      url:'/api/user/changePassword',
      type:'POST',
      headers:{'Authorization':'Bearer '+tk},
      data:data,
      success:function(r){
        alert(r.msg|| (r.code===1?'修改成功':'修改失败'));
        if(r.code===1){location.href='/user/index';}
      }
    });
  });
});
</script>
