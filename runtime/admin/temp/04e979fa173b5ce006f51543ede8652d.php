<?php /*a:1:{s:62:"/www/wwwroot/thinkmes/app/admin/view/tenant_miniapp/index.html";i:1771143229;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">租户小程序配置</h3></div>
    <div class="card-body">
        <form id="miniapp-form" class="form-horizontal" method="post">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">小程序名称</label>
                <div class="col-sm-6">
                    <input type="text" name="name" value="<?php echo htmlentities((string) (isset($row['name']) && ($row['name'] !== '')?$row['name']:'')); ?>" class="form-control">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">AppID</label>
                <div class="col-sm-6">
                    <input type="text" name="app_id" value="<?php echo htmlentities((string) (isset($row['app_id']) && ($row['app_id'] !== '')?$row['app_id']:'')); ?>" class="form-control" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">AppSecret</label>
                <div class="col-sm-6">
                    <input type="text" name="app_secret" value="<?php echo htmlentities((string) (isset($row['app_secret']) && ($row['app_secret'] !== '')?$row['app_secret']:'')); ?>" class="form-control" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">状态</label>
                <div class="col-sm-6">
                    <select name="status" class="form-control">
                        <option value="1" <?php if($row['status']==1): ?>selected<?php endif; ?>>启用</option>
                        <option value="0" <?php if($row['status']==0): ?>selected<?php endif; ?>>禁用</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6 offset-sm-2">
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
$(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    $('#miniapp-form').attr('action', base + '/tenant/miniapp');
    $('#miniapp-form').on('submit', function (e) {
        e.preventDefault();
        $.post($(this).attr('action'), $(this).serialize(), function (r) {
            alert(r.msg || '保存完成');
        }, 'json');
    });
});
</script>
