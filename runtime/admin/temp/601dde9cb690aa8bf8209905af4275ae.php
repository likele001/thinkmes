<?php /*a:1:{s:54:"/www/wwwroot/thinkmes/app/admin/view/mes/bom/edit.html";i:1771667961;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">编辑BOM</h3>
    </div>
    <div class="card-body">
        <form id="form-edit" method="post" class="form-horizontal">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">BOM编号</label>
                <div class="col-sm-6">
                    <input type="text" name="row[bom_no]" class="form-control" value="<?php echo htmlentities((string) (isset($row['bom_no']) && ($row['bom_no'] !== '')?$row['bom_no']:'')); ?>" placeholder="可留空自动生成">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">BOM名称</label>
                <div class="col-sm-6">
                    <input type="text" name="row[bom_name]" class="form-control" value="<?php echo htmlentities((string) (isset($row['bom_name']) && ($row['bom_name'] !== '')?$row['bom_name']:'')); ?>" placeholder="例如：电机A1型标准用料BOM">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">产品</label>
                <div class="col-sm-6">
                    <select name="row[product_id]" class="form-control">
                        <option value="">请选择产品</option>
                        <?php if(is_array($productList) || $productList instanceof \think\Collection || $productList instanceof \think\Paginator): $i = 0; $__LIST__ = $productList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $key); ?>" <?php if(isset($row['product_id']) && $row['product_id']==$key): ?>selected<?php endif; ?>><?php echo htmlentities((string) $vo); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">产品型号<span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select name="row[model_id]" class="form-control" required>
                        <option value="">请选择产品型号</option>
                        <?php if(is_array($modelList) || $modelList instanceof \think\Collection || $modelList instanceof \think\Paginator): $i = 0; $__LIST__ = $modelList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $key); ?>" <?php if(isset($row['model_id']) && $row['model_id']==$key): ?>selected<?php endif; ?>><?php echo htmlentities((string) $vo); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">版本号</label>
                <div class="col-sm-6">
                    <input type="text" name="row[version]" class="form-control" value="<?php echo htmlentities((string) (isset($row['version']) && ($row['version'] !== '')?$row['version']:'1.0')); ?>" placeholder="例如：V1.0">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">基准数量</label>
                <div class="col-sm-6">
                    <input type="number" name="row[base_quantity]" class="form-control" min="1" value="<?php echo htmlentities((string) (isset($row['base_quantity']) && ($row['base_quantity'] !== '')?$row['base_quantity']:'1')); ?>" placeholder="一般填 1，表示做 1 件成品的用料">
                    <small class="form-text text-muted">基准数量指这张BOM对应的成品数量，一般填 1，表示“做 1 件成品需要的标准用料”，系统会按订单数量和这个基数自动放大物料用量。</small>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">状态</label>
                <div class="col-sm-6">
                    <select name="row[status]" class="form-control">
                        <option value="0" <?php if(isset($row['status']) && $row['status']==0): ?>selected<?php endif; ?>>草稿</option>
                        <option value="1" <?php if(isset($row['status']) && $row['status']==1): ?>selected<?php endif; ?>>审核中</option>
                        <option value="2" <?php if(isset($row['status']) && $row['status']==2): ?>selected<?php endif; ?>>已发布</option>
                        <option value="3" <?php if(isset($row['status']) && $row['status']==3): ?>selected<?php endif; ?>>已废弃</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-8 offset-sm-2">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/bom/index" class="btn btn-default">返回</a>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
(function () {
    if (typeof jQuery === 'undefined') {
        setTimeout(arguments.callee, 50);
        return;
    }
    var $ = jQuery;
    $(function () {
        var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
        var form = $('#form-edit');
        if (!form.length) {
            return;
        }
        var id = parseInt('<?php echo htmlentities((string) (isset($row['id']) && ($row['id'] !== '')?$row['id']:"0")); ?>', 10) || 0;
        form.attr('action', base + '/mes/bom/edit?ids=' + id);
        form.on('submit', function (e) {
            e.preventDefault();
            var modelId = form.find('select[name="row[model_id]"]').val();
            if (!modelId) {
                alert('请选择产品型号');
                return;
            }
            var fd = new FormData(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: fd,
                dataType: 'json',
                processData: false,
                contentType: false
            }).done(function (r) {
                if (r && r.msg) {
                    alert(r.msg);
                }
                if (r && r.code === 1) {
                    location.href = base + '/mes/bom/index';
                }
            }).fail(function (xhr) {
                var msg = '操作失败';
                if (xhr && xhr.responseText) {
                    try {
                        var json = JSON.parse(xhr.responseText);
                        if (json.msg) {
                            msg = json.msg;
                        }
                    } catch (e) {}
                }
                alert(msg);
            });
        });
    });
})();
</script>
