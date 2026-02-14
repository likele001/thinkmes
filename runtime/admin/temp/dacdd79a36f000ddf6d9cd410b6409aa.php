<?php /*a:1:{s:52:"/www/wwwroot/thinkmes/app/admin/view/admin/edit.html";i:1771064583;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">编辑管理员</h3></div>
    <div class="card-body">
        <form id="form-edit" method="post" class="form-horizontal">
            <input type="hidden" name="id" value="<?php echo htmlentities((string) (isset($data['id']) && ($data['id'] !== '')?$data['id']:0)); ?>">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">账号</label>
                <div class="col-sm-6"><input type="text" name="username" class="form-control" value="<?php echo htmlentities((string) (isset($data['username']) && ($data['username'] !== '')?$data['username']:'')); ?>" readonly></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">密码</label>
                <div class="col-sm-6"><input type="password" name="password" class="form-control" placeholder="留空不修改"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">昵称</label>
                <div class="col-sm-6"><input type="text" name="nickname" class="form-control" value="<?php echo htmlentities((string) (isset($data['nickname']) && ($data['nickname'] !== '')?$data['nickname']:'')); ?>"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">父级管理员</label>
                <div class="col-sm-6">
                    <select name="pid" class="form-control">
                        <option value="0">无（顶级）</option>
                        <?php if(is_array($parents) || $parents instanceof \think\Collection || $parents instanceof \think\Paginator): $i = 0; $__LIST__ = $parents;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$p): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $p['id']); ?>" <?php if($data['pid'] == $p['id']): ?>selected<?php endif; ?>><?php echo htmlentities((string) $p['username']); ?>（<?php echo htmlentities((string) (isset($p['nickname']) && ($p['nickname'] !== '')?$p['nickname']:'-')); ?>）</option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">数据权限</label>
                <div class="col-sm-6">
                    <select name="data_scope" class="form-control">
                        <option value="1" <?php if($data['data_scope'] == 1): ?>selected<?php endif; ?>>个人（仅自己）</option>
                        <option value="2" <?php if($data['data_scope'] == 2): ?>selected<?php endif; ?>>子级（自己+下级）</option>
                        <option value="3" <?php if($data['data_scope'] == 3): ?>selected<?php endif; ?>>全部</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">角色</label>
                <div class="col-sm-6">
                    <select name="role_ids[]" class="form-control" multiple>
                        <?php if(is_array($roles) || $roles instanceof \think\Collection || $roles instanceof \think\Paginator): $i = 0; $__LIST__ = $roles;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $r['id']); ?>" <?php if(in_array($r['id'], $roleIdsArr)): ?>selected<?php endif; ?>><?php echo htmlentities((string) $r['name']); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                    <small class="text-muted">可多选；保存后将合并为逗号分隔的角色ID</small>
                </div>
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
                    <a href="<?php echo url('admin/index'); ?>" class="btn btn-default ml-2">返回</a>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
(function() {
    function initForm() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initForm, 50);
            return;
        }
        var $ = jQuery;
        var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
        $('#form-edit').attr('action', base + '/admin/edit');
        $('#form-edit').on('submit', function (e) {
            e.preventDefault();
            $.post($(this).attr('action'), $(this).serialize(), function (r) {
                if (r && r.msg) {
                    alert(r.msg);
                }
                if (r && r.code === 1) {
                    var redirectUrl = (typeof Config !== 'undefined' && Config.table_index_url) ? Config.table_index_url : (base + '/admin/index');
                    location.href = redirectUrl;
                }
            }, 'json').fail(function(xhr) {
                try {
                    var r = JSON.parse(xhr.responseText);
                    alert(r.msg || '操作失败');
                } catch(e) {
                    alert('操作失败');
                }
            });
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initForm);
    } else {
        initForm();
    }
})();
</script>
