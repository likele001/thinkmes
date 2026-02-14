<?php /*a:3:{s:62:"/www/wwwroot/thinkmes/app/admin/view/mes/purchase/inbound.html";i:1770885823;s:53:"/www/wwwroot/thinkmes/app/admin/view/common/meta.html";i:1770864701;s:62:"/www/wwwroot/thinkmes/app/admin/view/common/script-iframe.html";i:1771032882;}*/ ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<title><?php echo htmlentities((string) (isset($title) && ($title !== '')?$title:'keleadmin')); ?> - KeleAdmin</title>
<script>
// 向 localStorage 写入禁用 IFrame 的配置（AdminLTE 会从这里读取）
try {
    var iframeConfig = {
        autoIframeMode: false,
        autoItemActive: false,
        autoShowNewTab: false,
        onTabClick: function(e) { return e; },
        onTabChanged: function(e) { return e; },
        onTabCreated: function(e) { return e; }
    };
    localStorage.setItem('AdminLTE:IFrame:Options', JSON.stringify(iframeConfig));
} catch(e) {}
// 确保 AdminLTEOptions 正确设置
window.AdminLTEOptions = window.AdminLTEOptions || {};
window.AdminLTEOptions.autoIframeMode = false;
window.AdminLTEOptions.iframe = false;
</script>
<!-- Bootstrap 4 (AdminLTE 3 依赖) -->
<link rel="stylesheet" href="/assets/lib/bootstrap/css/bootstrap.min.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="/assets/lib/fontawesome/css/all.min.css">
<!-- AdminLTE 3 -->
<link rel="stylesheet" href="/assets/lib/adminlte/css/adminlte.min.css">
<!-- Bootstrap Table -->
<link rel="stylesheet" href="/assets/lib/bootstrap-table/css/bootstrap-table.min.css">
<!-- Custom Theme -->
<link rel="stylesheet" href="/assets/css/fastadmin-theme.css">
<style>
/* FastAdmin 兼容样式 */
.panel-lead { font-size: 14px; }
.panel-lead em { font-style: normal; font-weight: 600; margin-right: 8px; }
.toolbar .btn { margin-right: 6px; }

/* FastAdmin 布局：内容区占满剩余高度，供 addtabs 使用 */
.wrapper { display: flex !important; flex-direction: column !important; min-height: 100vh !important; }
.content-wrapper { display: flex !important; flex-direction: column !important; flex: 1 !important; min-height: 0 !important; width: 100% !important; }
.content-wrapper > .content { flex: 1 !important; min-height: 0 !important; overflow: hidden !important; width: 100% !important; }
</style>
<script type="text/javascript">
    var Config = <?php echo json_encode($config ?? [], 256); ?>;
</script>

<style>
/* 简单表单样式 */
.form-group {
    margin-bottom: 15px;
}
.form-control {
    max-width: 300px;
}
</style>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4>采购入库</h4>
    </div>
    <div class="panel-body">
        <form id="form-add" class="form-horizontal" role="form" action="" method="post">
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2">供应商</label>
                <div class="col-xs-12 col-sm-8">
                    <select id="c-supplier_id" class="form-control selectpicker" name="row[supplier_id]" data-rule="required">
                        <option value="">请选择供应商</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2">仓库</label>
                <div class="col-xs-12 col-sm-8">
                    <select id="c-warehouse_id" class="form-control selectpicker" name="row[warehouse_id]" data-rule="required">
                        <option value="">请选择仓库</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2">入库单号</label>
                <div class="col-xs-12 col-sm-8">
                    <input id="c-bill_no" class="form-control" name="row[bill_no]" value="" placeholder="请输入入库单号">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2">采购订单号</label>
                <div class="col-xs-12 col-sm-8">
                    <input id="c-order_no" class="form-control" name="row[order_no]" value="" placeholder="请输入采购订单号">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2">入库日期</label>
                <div class="col-xs-12 col-sm-8">
                    <input id="c-inbound_date" class="form-control" name="row[inbound_date]" type="date" placeholder="请选择入库日期">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-xs-12 col-sm-2">备注</label>
                <div class="col-xs-12 col-sm-8">
                    <textarea id="c-remark" class="form-control" name="row[remark]" rows="3" placeholder="请输入备注"></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-12 col-sm-8 col-sm-offset-4">
                    <button type="submit" class="btn btn-success submit-btn">提交</button>
                    <button type="reset" class="btn btn-default">重置</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
$(function() {
    // 绑定供应商change事件
    $('#c-supplier_id').on('change', function() {
        var supplierId = $(this).val();
        if (supplierId) {
            // 动态获取该供应商的物料列表（示例）
            $.get('<?php echo url("admin/mes/purchase/getMaterials"); ?>?supplier_id=' + supplierId, function(res) {
                if (res.code === 1) {
                    var options = '<option value="">请选择物料</option>';
                    $.each(res.data, function(i, item) {
                        options += '<option value="' + item.id + '">' + item.name + '</option>';
                    });
                    $('#c-material_id').html(options);
                }
            });
        }
    });

    // 表单验证
    $('#form-add').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?php echo url("admin/mes/purchase/saveInbound"); ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.code === 1) {
                    alert(res.msg);
                    // 可以跳转到列表页或刷新列表
                } else {
                    alert(res.msg);
                }
            }
        });
    });

    // 重置按钮
    $('button[type="reset"]').on('click', function() {
        $('#form-add')[0].reset();
    });
});
</script>
<script>
// 检测是否在 iframe 中，如果是，添加 URL 参数并重新加载（仅首次）
(function() {
    if (window.self !== window.top) {
        // 在 iframe 中
        var url = new URL(window.location.href);
        if (!url.searchParams.has('iframe')) {
            url.searchParams.set('iframe', '1');
            window.location.replace(url.toString());
            return;
        }
    }
})();
</script>
<script>
// 向 localStorage 写入禁用 IFrame 的配置（AdminLTE 会从这里读取）
try {
    var iframeConfig = {
        autoIframeMode: false,
        autoItemActive: false,
        autoShowNewTab: false,
        onTabClick: function(e) { return e; },
        onTabChanged: function(e) { return e; },
        onTabCreated: function(e) { return e; }
    };
    localStorage.setItem('AdminLTE:IFrame:Options', JSON.stringify(iframeConfig));
} catch(e) {}

// 确保 AdminLTEOptions 正确设置
window.AdminLTEOptions = window.AdminLTEOptions || {};
window.AdminLTEOptions.autoIframeMode = false;
window.AdminLTEOptions.iframe = false;
</script>
<!-- 基础库 -->
<script src="/assets/lib/jquery/jquery.min.js"></script>
<script src="/assets/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/assets/lib/bootstrap-table/js/bootstrap-table.min.js"></script>
<script src="/assets/lib/fontawesome/js/all.min.js"></script>
<script src="/assets/lib/adminlte/js/adminlte.min.js"></script>
<script>
// 双重保护：完全禁用 AdminLTE 的 IFrame 插件
(function() {
    // 在 AdminLTE 加载后立即禁用 IFrame 插件
    function disableIFrame() {
        if (typeof AdminLTE !== 'undefined' && AdminLTE !== null) {
            // 完全删除 IFrame 插件
            if (AdminLTE.IFrame) {
                delete AdminLTE.IFrame;
            }

            // 创建一个空的 IFrame 对象，防止其他代码调用时出错
            AdminLTE.IFrame = function() {
                return this;
            };
            AdminLTE.IFrame.prototype = {};
            AdminLTE.IFrame.prototype._init = function() {
                return;
            };
            AdminLTE.IFrame.prototype._initFrameElement = function() {
                return;
            };
            AdminLTE.IFrame._jQueryInterface = function() {
                return this;
            };

            // 移除所有可能触发 IFrame 初始化的事件监听器
            if (typeof jQuery !== 'undefined') {
                jQuery(document).off('init.lte.iframe');
                jQuery(document).off('DOMContentLoaded', '[data-widget="iframe"]');
            }
        } else {
            // 如果 AdminLTE 还没加载，等待一下再试（最多等待 2 秒）
            var attempts = arguments.callee.attempts || 0;
            arguments.callee.attempts = attempts + 1;
            if (attempts < 40) { // 40 * 50ms = 2秒
                setTimeout(disableIFrame, 50);
            }
        }
    }

    // 立即执行
    disableIFrame();
})();
</script>
<script src="/assets/js/backend-loader.js"></script>

