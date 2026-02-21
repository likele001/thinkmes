<?php /*a:1:{s:65:"/www/wwwroot/thinkmes/app/admin/view/mes/process_price/index.html";i:1770206754;}*/ ?>
<div class="card panel-intro">
    <div class="card-header">
        <div class="panel-lead"><em>工序工价管理</em> 管理型号与工序的工价设置</div>
    </div>
    <div class="card-body">
        <div id="toolbar" class="toolbar mb-2">
            <a href="javascript:;" class="btn btn-primary btn-refresh" title="刷新"><i class="fas fa-sync-alt"></i> 刷新</a>
            <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/process_price/add" class="btn btn-success btn-add" title="添加"><i class="fas fa-plus"></i> 添加工价</a>
            <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/process_price/batch" class="btn btn-info btn-add" title="批量设置"><i class="fas fa-cogs"></i> 批量设置</a>
            <a href="javascript:;" class="btn btn-success btn-edit btn-disabled disabled" title="编辑"><i class="fas fa-edit"></i> 编辑</a>
            <a href="javascript:;" class="btn btn-danger btn-del btn-disabled disabled" title="删除"><i class="fas fa-trash-alt"></i> 删除</a>
        </div>
        <table id="table" class="table table-striped table-bordered table-hover" width="100%"></table>
    </div>
</div>
