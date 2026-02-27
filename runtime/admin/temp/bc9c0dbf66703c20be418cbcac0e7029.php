<?php /*a:1:{s:66:"/www/wwwroot/thinkmes/app/admin/view/mes/customer_product/add.html";i:1771762266;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">添加客户产品配置</h3>
    </div>
    <div class="card-body">
        <form id="form-add" method="post" class="form-horizontal">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">客户 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select name="row[customer_id]" class="form-control" id="customer_id" required>
                        <option value="">请选择客户</option>
                        <?php if(is_array($customerList) || $customerList instanceof \think\Collection || $customerList instanceof \think\Paginator): $i = 0; $__LIST__ = $customerList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $key); ?>"><?php echo htmlentities((string) $vo); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">产品型号 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select name="row[model_id]" class="form-control" id="model_id" required>
                        <option value="">请选择产品型号</option>
                        <?php if(is_array($modelList) || $modelList instanceof \think\Collection || $modelList instanceof \think\Paginator): $i = 0; $__LIST__ = $modelList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $key); ?>"><?php echo htmlentities((string) $vo); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">销售单价 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="number" name="row[price]" class="form-control" step="0.01" min="0" placeholder="请输入单价" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">币种</label>
                <div class="col-sm-6">
                    <select name="row[currency]" class="form-control">
                        <option value="CNY" selected>CNY 人民币</option>
                        <option value="USD">USD 美元</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">起订量</label>
                <div class="col-sm-6">
                    <input type="number" name="row[min_qty]" class="form-control" min="1" value="1">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">状态</label>
                <div class="col-sm-6">
                    <label class="radio-inline">
                        <input type="radio" name="row[status]" value="1" checked> 启用
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="row[status]" value="0"> 禁用
                    </label>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">备注</label>
                <div class="col-sm-6">
                    <input type="text" name="row[remark]" class="form-control" maxlength="255" placeholder="可选，补充说明">
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-8 offset-sm-2">
                    <button type="submit" class="btn btn-primary">提交</button>
                    <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/customer_product/index" class="btn btn-default">返回</a>
                </div>
            </div>
        </form>
    </div>
</div>
