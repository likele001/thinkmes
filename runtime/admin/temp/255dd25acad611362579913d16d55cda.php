<?php /*a:1:{s:59:"/www/wwwroot/thinkmes/app/admin/view/mes/warehouse/add.html";i:1770787401;}*/ ?>
<form id="form-add" class="form-horizontal" role="form" action="" method="post">
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('仓库名称'); ?><span class="text-danger">*</span></label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-name" class="form-control" name="row[name]" value="" data-rule="required" placeholder="请输入仓库名称">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('仓库编码'); ?></label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-code" class="form-control" name="row[code]" value="" placeholder="请输入仓库编码">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('仓库地址'); ?></label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-address" class="form-control" name="row[address]" value="" placeholder="请输入仓库地址">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('负责人'); ?></label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-manager_id" class="form-control" name="row[manager_id]" value="" type="number" placeholder="请输入负责人ID">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('状态'); ?></label>
        <div class="col-xs-12 col-sm-8">
            <select id="c-status" class="form-control" name="row[status]">
                <option value="1">启用</option>
                <option value="0">禁用</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('设为默认'); ?></label>
        <div class="col-xs-12 col-sm-8">
            <select id="c-is_default" class="form-control" name="row[is_default]">
                <option value="0">否</option>
                <option value="1">是</option>
            </select>
        </div>
    </div>
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-success btn-embossed disabled"><?php echo __('确定'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('重置'); ?></button>
        </div>
    </div>
</form>
