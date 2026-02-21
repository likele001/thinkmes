<?php /*a:1:{s:61:"/www/wwwroot/thinkmes/app/admin/view/mes/allocation/edit.html";i:1771381795;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">编辑分工分配</h3>
    </div>
    <div class="card-body">
        <form id="form-edit" method="post" class="form-horizontal">
            <input type="hidden" name="row[id]" value="<?php echo htmlentities((string) $data['id']); ?>">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">订单 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select name="row[order_id]" class="form-control" id="order_id" required>
                        <option value="">请选择订单</option>
                        <?php if(is_array($orderList) || $orderList instanceof \think\Collection || $orderList instanceof \think\Paginator): $i = 0; $__LIST__ = $orderList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $key); ?>" <?php if($key == $data['order_id']): ?>selected<?php endif; ?>><?php echo htmlentities((string) $vo); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">产品型号</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" value="<?php echo htmlentities((string) (isset($data['model']['full_name']) && ($data['model']['full_name'] !== '')?$data['model']['full_name']:'')); ?>" disabled>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">工序 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select name="row[process_id]" class="form-control" id="process_id" required>
                        <option value="">请选择工序</option>
                        <?php if(is_array($processList) || $processList instanceof \think\Collection || $processList instanceof \think\Paginator): $i = 0; $__LIST__ = $processList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $key); ?>" <?php if($key == $data['process_id']): ?>selected<?php endif; ?>><?php echo htmlentities((string) $vo); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">员工 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select name="row[user_id]" class="form-control" id="user_id" required>
                        <option value="">请选择员工</option>
                        <?php if(is_array($userList) || $userList instanceof \think\Collection || $userList instanceof \think\Paginator): $i = 0; $__LIST__ = $userList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $key); ?>" <?php if($key == $data['user_id']): ?>selected<?php endif; ?>><?php echo htmlentities((string) $vo); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">分配数量 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="number" name="row[quantity]" class="form-control" min="1" value="<?php echo htmlentities((string) $data['quantity']); ?>" placeholder="请输入分配数量" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">状态</label>
                <div class="col-sm-6">
                    <select name="row[status]" class="form-control">
                        <option value="0" <?php if($data['status'] == '0'): ?>selected<?php endif; ?>>待开始</option>
                        <option value="1" <?php if($data['status'] == '1'): ?>selected<?php endif; ?>>进行中</option>
                        <option value="2" <?php if($data['status'] == '2'): ?>selected<?php endif; ?>>已完成</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">备注</label>
                <div class="col-sm-6">
                    <textarea name="row[remark]" class="form-control" rows="3" placeholder="请输入备注"><?php echo htmlentities((string) (isset($data['remark']) && ($data['remark'] !== '')?$data['remark']:'')); ?></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-8 offset-sm-2">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/allocation/index" class="btn btn-default">返回</a>
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
        $('#form-edit').attr('action', base + '/mes/allocation/edit');
        $('#form-edit').on('submit', function (e) {
            e.preventDefault();
            $.post($(this).attr('action') + '?id=' + $('input[name="row[id]"]').val(), $(this).serialize(), function (r) {
                if (r && r.msg) {
                    alert(r.msg);
                }
                if (r && r.code === 1) {
                    location.href = base + '/mes/allocation/index';
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
