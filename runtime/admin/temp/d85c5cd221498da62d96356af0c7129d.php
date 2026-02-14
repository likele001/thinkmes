<?php /*a:1:{s:53:"/www/wwwroot/thinkmes/app/admin/view/tenant/edit.html";i:1769948823;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">编辑租户</h3></div>
    <div class="card-body">
        <form id="form-edit" method="post" class="form-horizontal">
            <input type="hidden" name="id" value="<?php echo htmlentities((string) (isset($data['id']) && ($data['id'] !== '')?$data['id']:0)); ?>">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">租户名称</label>
                <div class="col-sm-6"><input type="text" name="name" class="form-control" value="<?php echo htmlentities((string) (isset($data['name']) && ($data['name'] !== '')?$data['name']:'')); ?>" required></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">绑定域名</label>
                <div class="col-sm-6"><input type="text" name="domain" class="form-control" value="<?php echo htmlentities((string) (isset($data['domain']) && ($data['domain'] !== '')?$data['domain']:'')); ?>"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">套餐</label>
                <div class="col-sm-6">
                    <select name="package_id" class="form-control">
                        <option value="0">请选择</option>
                        <?php if(is_array($packages) || $packages instanceof \think\Collection || $packages instanceof \think\Paginator): $i = 0; $__LIST__ = $packages;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$p): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $p['id']); ?>" <?php if($data['package_id'] == $p['id']): ?>selected<?php endif; ?>><?php echo htmlentities((string) $p['name']); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">到期时间</label>
                <div class="col-sm-6"><input type="date" name="expire_time" class="form-control" value="<?php echo htmlentities((string) (isset($data['expire_time']) && ($data['expire_time'] !== '')?$data['expire_time']:'')); ?>"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">状态</label>
                <div class="col-sm-6">
                    <select name="status" class="form-control">
                        <option value="1" <?php if($data['status'] == 1): ?>selected<?php endif; ?>>正常</option>
                        <option value="0" <?php if($data['status'] == 0): ?>selected<?php endif; ?>>禁用</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6 offset-sm-2">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="<?php echo url('tenant/index'); ?>" class="btn btn-default ml-2">返回</a>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
$(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    $('#form-edit').attr('action', base + '/tenant/edit');
    $('#form-edit').on('submit', function (e) {
        e.preventDefault();
        $.post($(this).attr('action'), $(this).serialize(), function (r) {
            alert(r.msg);
            if (r.code === 1) location.href = base + '/tenant/index';
        }, 'json');
    });
});
</script>
