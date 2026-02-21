<?php /*a:1:{s:70:"/www/wwwroot/thinkmes/app/admin/view/mes/shipment/select_shipment.html";i:1770876403;}*/ ?>
<div class="card">
    <div class="card-header">
        <h5>请选择发货单</h5>
    </div>
    <div class="card-body">
        <div id="toolbar" class="toolbar">
            <div class="form-inline">
                <div class="form-group">
                    <label>搜索发货单:</label>
                    <input type="text" id="search-shipment" class="form-control" placeholder="发货单号或订单号">
                </div>
                <button type="button" class="btn btn-primary btn-search" id="btn-search">搜索</button>
            </div>
        </div>
        <table id="table" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>发货ID</th>
                    <th>发货单号</th>
                    <th>订单号</th>
                    <th>客户名称</th>
                    <th>发货数量</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if(is_array($shipments) || $shipments instanceof \think\Collection || $shipments instanceof \think\Paginator): $i = 0; $__LIST__ = $shipments;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$shipment): $mod = ($i % 2 );++$i;?>
                <tr>
                    <td><?php echo htmlentities((string) $shipment['id']); ?></td>
                    <td><?php echo htmlentities((string) $shipment['shipment_no']); ?></td>
                    <td><?php echo htmlentities((string) (isset($shipment['order']['order_no']) && ($shipment['order']['order_no'] !== '')?$shipment['order']['order_no']:'')); ?></td>
                    <td><?php echo htmlentities((string) (isset($shipment['customer']['customer_name']) && ($shipment['customer']['customer_name'] !== '')?$shipment['customer']['customer_name']:'')); ?></td>
                    <td><?php echo htmlentities((string) $shipment['shipment_quantity']); ?></td>
                    <td>
                        <?php if($shipment['status'] == 0): ?>
                            <span class="badge badge-warning">待发货</span>
                        <?php elseif($shipment['status'] == 1): ?>
                            <span class="badge badge-info">已发货</span>
                        <?php elseif($shipment['status'] == 2): ?>
                            <span class="badge badge-success">已签收</span>
                        <?php else: ?>
                            <span class="badge badge-danger">已退回</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="javascript:selectShipment(<?php echo htmlentities((string) $shipment['id']); ?>)" class="btn btn-xs btn-info">跟踪</a>
                    </td>
                </tr>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </tbody>
        </table>
        <?php if(empty($shipments)): ?>
        <div class="alert alert-info" role="alert">
            <p>暂无发货单</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function selectShipment(shipmentId) {
    window.location.href = "<?php echo url('mes.Shipment/track'); ?>?id=" + shipmentId;
}

$(function() {
    $('#btn-search').click(function() {
        var keyword = $('#search-shipment').val().trim();
        if (keyword) {
            // 这里可以添加客户端搜索或服务器端搜索
            var items = [];
            $('#table tbody tr').each(function() {
                var text = $(this).text();
                if (text.indexOf(keyword) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        } else {
            $('#table tbody tr').show();
        }
    });
});
</script>
