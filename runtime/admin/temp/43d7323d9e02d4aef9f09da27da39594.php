<?php /*a:1:{s:58:"/www/wwwroot/thinkmes/app/admin/view/mes/product/edit.html";i:1770200827;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">编辑产品</h3>
    </div>
    <div class="card-body">
        <form id="form-edit" method="post" class="form-horizontal">
            <input type="hidden" name="row[id]" value="<?php echo htmlentities((string) $row['id']); ?>">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">产品名称 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="text" name="row[name]" class="form-control" value="<?php echo htmlentities((string) $row['name']); ?>" placeholder="请输入产品名称" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">产品编码</label>
                <div class="col-sm-6">
                    <input type="text" name="row[code]" class="form-control" value="<?php echo htmlentities((string) $row['code']); ?>" placeholder="请输入产品编码">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">产品规格</label>
                <div class="col-sm-6">
                    <textarea name="row[specification]" class="form-control" rows="3" placeholder="请输入产品规格"><?php echo htmlentities((string) $row['specification']); ?></textarea>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">状态</label>
                <div class="col-sm-6">
                    <label class="radio-inline">
                        <input type="radio" name="row[status]" value="1" <?php if($row['status'] == '1'): ?>checked<?php endif; ?>> 正常
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="row[status]" value="0" <?php if($row['status'] == '0'): ?>checked<?php endif; ?>> 禁用
                    </label>
                </div>
            </div>
            
            <!-- 型号和工价录入区域 -->
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">型号和工价</label>
                <div class="col-sm-9">
                    <div id="model-container" data-models='<?php echo $modelsJson; ?>'>
                        <!-- 型号数据将通过JavaScript动态渲染 -->
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="add-model">添加型号</button>
                </div>
            </div>
            
            <div class="form-group row">
                <div class="col-sm-8 offset-sm-2">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/product/index" class="btn btn-default">返回</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 隐藏的工序数据（供JS读取） -->
<script type="application/json" id="process-data"><?php echo $processListJson; ?></script>
