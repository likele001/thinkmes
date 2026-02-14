<?php /*a:1:{s:56:"/www/wwwroot/thinkmes/app/admin/view/auth_rule/edit.html";i:1770107143;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">编辑权限规则</h3></div>
    <div class="card-body">
        <form id="form-edit" method="post" class="form-horizontal">
            <input type="hidden" name="id" value="<?php echo htmlentities((string) (isset($data['id']) && ($data['id'] !== '')?$data['id']:0)); ?>">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">规则标识</label>
                <div class="col-sm-6"><input type="text" name="name" class="form-control" value="<?php echo htmlentities((string) (isset($data['name']) && ($data['name'] !== '')?$data['name']:'')); ?>" required></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">标题</label>
                <div class="col-sm-6"><input type="text" name="title" class="form-control" value="<?php echo htmlentities((string) (isset($data['title']) && ($data['title'] !== '')?$data['title']:'')); ?>"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">类型</label>
                <div class="col-sm-6">
                    <select name="type" class="form-control">
                        <option value="1" <?php if($data['type'] == 1): ?>selected<?php endif; ?>>菜单</option>
                        <option value="2" <?php if($data['type'] == 2): ?>selected<?php endif; ?>>按钮</option>
                        <option value="3" <?php if($data['type'] == 3): ?>selected<?php endif; ?>>接口</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">是否菜单</label>
                <div class="col-sm-6">
                    <select name="ismenu" class="form-control">
                        <option value="1" <?php if(($data['ismenu'] ?? 1) == 1): ?>selected<?php endif; ?>>显示</option>
                        <option value="0" <?php if(($data['ismenu'] ?? 1) == 0): ?>selected<?php endif; ?>>隐藏</option>
                    </select>
                    <small class="text-muted">隐藏后不在菜单中显示，但仍可作为权限节点使用</small>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">父ID</label>
                <div class="col-sm-6"><input type="number" name="pid" class="form-control" value="<?php echo htmlentities((string) (isset($data['pid']) && ($data['pid'] !== '')?$data['pid']:0)); ?>"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">图标</label>
                <div class="col-sm-6"><input type="text" name="icon" class="form-control" value="<?php echo htmlentities((string) (isset($data['icon']) && ($data['icon'] !== '')?$data['icon']:'')); ?>"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">排序</label>
                <div class="col-sm-6"><input type="number" name="sort" class="form-control" value="<?php echo htmlentities((string) (isset($data['sort']) && ($data['sort'] !== '')?$data['sort']:0)); ?>"></div>
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
                    <a href="<?php echo url('auth_rule/index'); ?>" class="btn btn-default ml-2">返回</a>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
$(function () {
    $('#form-edit').attr('action', (typeof Config !== 'undefined' && Config.moduleurl) ? (Config.moduleurl + '/auth_rule/edit') : '/admin/auth_rule/edit');
    $('#form-edit').on('submit', function (e) {
        e.preventDefault();
        $.post($(this).attr('action'), $(this).serialize(), function (r) {
            alert(r.msg);
            if (r.code === 1) location.href = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : '/admin/auth_rule/index';
        }, 'json');
    });
});
</script>
