<?php /*a:1:{s:60:"/www/wwwroot/thinkmes/app/admin/view/tenant_audit/index.html";i:1770795477;}*/ ?>
<div class="card card-tabs">
    <div class="card-header">
        <h3 class="card-title">租户注册审核</h3>
    </div>
    <div class="card-content">
        <div class="widget-body nopadding">
            <div id="toolbar" class="toolbar">
                <div class="pull-right">
                    <select id="status-filter" class="form-control" style="width: 150px;">
                        <option value="">全部状态</option>
                        <option value="0">待审核</option>
                        <option value="1">已通过</option>
                        <option value="2">已拒绝</option>
                    </select>
                </div>
            </div>
            <table id="table" class="table table-striped table-bordered table-hover table-nowrap">
            </table>
        </div>
    </div>
</div>
