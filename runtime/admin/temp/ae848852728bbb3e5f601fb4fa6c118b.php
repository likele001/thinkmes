<?php /*a:1:{s:65:"/www/wwwroot/thinkmes/app/admin/view/mes/product_model/index.html";i:1770205852;}*/ ?>
<div class="card panel-intro">
    <div class="card-header">
        <div class="panel-lead"><em>产品型号管理</em> 管理产品的具体型号信息</div>
    </div>
    <div class="card-body">
        <div id="toolbar" class="toolbar mb-2">
            <a href="javascript:;" class="btn btn-primary btn-refresh" title="刷新"><i class="fas fa-sync-alt"></i> 刷新</a>
            <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/product_model/add" class="btn btn-success btn-add" title="添加"><i class="fas fa-plus"></i> 添加型号</a>
            <a href="javascript:;" class="btn btn-success btn-edit btn-disabled disabled" title="编辑"><i class="fas fa-edit"></i> 编辑</a>
            <a href="javascript:;" class="btn btn-danger btn-del btn-disabled disabled" title="删除"><i class="fas fa-trash-alt"></i> 删除</a>
        </div>
        <table id="table" class="table table-striped table-bordered table-hover" width="100%"></table>
    </div>
</div>
