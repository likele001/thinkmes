<?php /*a:1:{s:55:"/www/wwwroot/thinkmes/app/admin/view/profile/index.html";i:1771072146;}*/ ?>
<div class="panel panel-default">
  <div class="panel-heading">个人资料</div>
  <div class="panel-body">
    <form id="form-profile" class="form-horizontal">
      <div class="form-group">
        <label class="col-sm-2 control-label">账号</label>
        <div class="col-sm-6">
          <input type="text" class="form-control" value="<?php echo htmlentities((string) (isset($admin['username']) && ($admin['username'] !== '')?$admin['username']:'')); ?>" disabled>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label">昵称</label>
        <div class="col-sm-6">
          <input type="text" class="form-control" name="nickname" value="<?php echo htmlentities((string) (isset($admin['nickname']) && ($admin['nickname'] !== '')?$admin['nickname']:'')); ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label">新密码</label>
        <div class="col-sm-6">
          <input type="password" class="form-control" name="password" placeholder="不修改请留空">
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-6">
          <button type="submit" class="btn btn-primary">保存</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php if($tenant): ?>
<div class="panel panel-default">
  <div class="panel-heading">租户信息</div>
  <div class="panel-body">
    <div class="table-responsive">
      <table class="table table-bordered">
        <tr><th style="width:180px">租户名称</th><td><?php echo htmlentities((string) (isset($tenant['name']) && ($tenant['name'] !== '')?$tenant['name']:'-')); ?></td></tr>
        <tr><th>租户域名</th><td><?php echo htmlentities((string) (isset($tenant['domain']) && ($tenant['domain'] !== '')?$tenant['domain']:'-')); ?></td></tr>
        <tr><th>套餐</th><td><?php echo htmlentities((string) (isset($tenant['package_id']) && ($tenant['package_id'] !== '')?$tenant['package_id']:'-')); ?></td></tr>
        <tr><th>到期时间</th><td><?php echo htmlentities((string) (isset($tenant['expire_time_text']) && ($tenant['expire_time_text'] !== '')?$tenant['expire_time_text']:'-')); ?></td></tr>
        <tr><th>状态</th><td><?php echo isset($tenant['status']) ? ($tenant['status']==1?'正常':'停用') : '-'; ?></td></tr>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>
<script>
$(function(){
  $('#form-profile').on('submit', function(e){
    e.preventDefault();
    $.post('/admin/profile/updateProfile', $(this).serialize(), function(r){
      alert(r.msg || (r.code===1?'保存成功':'保存失败'));
      if (r.code===1) location.reload();
    }, 'json');
  });
});
</script>
