<?php /*a:1:{s:63:"/www/wwwroot/thinkmes/app/admin/view/mes/product_model/add.html";i:1770205852;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">添加产品型号</h3>
    </div>
    <div class="card-body">
        <form id="form-add" method="post" class="form-horizontal">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">产品 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select name="row[product_id]" class="form-control" id="product_id" required>
                        <option value="">请选择产品</option>
                        <?php if(is_array($productList) || $productList instanceof \think\Collection || $productList instanceof \think\Paginator): $i = 0; $__LIST__ = $productList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $key); ?>"><?php echo htmlentities((string) $vo); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">型号名称 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="text" name="row[name]" class="form-control" placeholder="请输入型号名称" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">型号编码</label>
                <div class="col-sm-6">
                    <input type="text" name="row[model_code]" class="form-control" placeholder="请输入型号编码">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">型号描述</label>
                <div class="col-sm-6">
                    <textarea name="row[description]" class="form-control" rows="3" placeholder="请输入型号描述"></textarea>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">状态</label>
                <div class="col-sm-6">
                    <label class="radio-inline">
                        <input type="radio" name="row[status]" value="1" checked> 正常
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="row[status]" value="0"> 禁用
                    </label>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-8 offset-sm-2">
                    <button type="submit" class="btn btn-primary">提交</button>
                    <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/product_model/index" class="btn btn-default">返回</a>
                </div>
            </div>
        </form>
    </div>
</div>
