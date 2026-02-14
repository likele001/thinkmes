<?php /*a:1:{s:61:"/www/wwwroot/thinkmes/app/admin/view/tenant_package/edit.html";i:1771067008;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">编辑套餐</h3></div>
    <div class="card-body">
        <form id="form-edit" method="post" class="form-horizontal">
            <input type="hidden" name="id" value="<?php echo htmlentities((string) $data['id']); ?>">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">套餐名称</label>
                <div class="col-sm-6"><input type="text" name="name" class="form-control" value="<?php echo htmlentities((string) $data['name']); ?>" required placeholder="必填"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">最大管理员数</label>
                <div class="col-sm-6"><input type="number" name="max_admin" class="form-control" value="<?php echo htmlentities((string) $data['max_admin']); ?>" min="0" required></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">最大用户数</label>
                <div class="col-sm-6"><input type="number" name="max_user" class="form-control" value="<?php echo htmlentities((string) $data['max_user']); ?>" min="0" required></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">默认有效期（天）</label>
                <div class="col-sm-6"><input type="number" name="expire_days" class="form-control" value="<?php echo htmlentities((string) $data['expire_days']); ?>" placeholder="留空为永久" min="1"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">排序</label>
                <div class="col-sm-6"><input type="number" name="sort" class="form-control" value="<?php echo htmlentities((string) $data['sort']); ?>"></div>
            </div>
            <div class="form-group row">
                <div class="col-sm-6 offset-sm-2">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="<?php echo url('tenant_package/index'); ?>" class="btn btn-default ml-2">返回</a>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var form = document.getElementById('form-edit');
    form.setAttribute('action', base + '/tenant_package/edit');
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var action = form.getAttribute('action');
        var fd = new FormData(form);
        fetch(action, {
            method: 'POST',
            body: fd,
        }).then(function (res) { return res.json(); }).then(function (r) {
            alert(r.msg || (r.code === 1 ? '保存成功' : '保存失败'));
            if (r.code === 1) location.href = base + '/tenant_package/index';
        }).catch(function () {
            alert('请求失败，请检查网络连接');
        });
    });
});
</script>
