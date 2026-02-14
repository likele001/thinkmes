<?php /*a:1:{s:65:"/www/wwwroot/thinkmes/app/admin/view/mes/order/material_list.html";i:1771067934;}*/ ?>
<div class="card panel-intro">
    <div class="card-header">
        <div class="panel-lead"><em>订单物料清单</em> 订单：<?php echo htmlentities((string) $order['order_name']); ?> (<?php echo htmlentities((string) $order['order_no']); ?>)</div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-shopping-cart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">总需求物料</span>
                        <span class="info-box-number"><?php echo count($orderMaterials); ?> 种</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">缺料品种</span>
                        <span class="info-box-number"><?php echo htmlentities((string) $shortageCount); ?> 种</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-yen-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">预估总成本</span>
                        <span class="info-box-number">￥<?php echo htmlentities((string) number_format($totalAmount,2)); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div id="toolbar" class="toolbar mb-2">
            <a href="javascript:;" class="btn btn-primary btn-refresh" title="刷新"><i class="fas fa-sync-alt"></i> 刷新</a>
            <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/order/index" class="btn btn-default"><i class="fas fa-arrow-left"></i> 返回订单列表</a>
        </div>
        <table id="table" class="table table-striped table-bordered table-hover" width="100%">
            <thead>
                <tr>
                    <th>物料名称</th>
                    <th>规格型号</th>
                    <th>需求数量</th>
                    <th>当前库存</th>
                    <th>缺料数量</th>
                    <th>预估单价</th>
                    <th>预估总额</th>
                    <th>供应商</th>
                    <th>状态</th>
                </tr>
            </thead>
            <tbody>
                <?php if(is_array($orderMaterials) || $orderMaterials instanceof \think\Collection || $orderMaterials instanceof \think\Paginator): $i = 0; $__LIST__ = $orderMaterials;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <tr>
                    <td><?php echo htmlentities((string) (isset($vo['material']['name']) && ($vo['material']['name'] !== '')?$vo['material']['name']:'-')); ?></td>
                    <td>-</td>
                    <td><?php echo htmlentities((string) $vo['required_quantity']); ?></td>
                    <td><?php echo htmlentities((string) (isset($vo['material']['stock']) && ($vo['material']['stock'] !== '')?$vo['material']['stock']:0)); ?></td>
                    <td>
                        <?php $shortage = max(0, $vo.required_quantity - ($vo.material->stock ?? 0)); if($shortage > 0): ?>
                        <span class="text-danger"><?php echo htmlentities((string) $shortage); ?></span>
                        <?php else: ?>
                        0
                        <?php endif; ?>
                    </td>
                    <td>￥<?php echo htmlentities((string) $vo['estimated_price']); ?></td>
                    <td>￥<?php echo htmlentities((string) $vo['estimated_amount']); ?></td>
                    <td><?php echo htmlentities((string) (isset($vo['supplier']['name']) && ($vo['supplier']['name'] !== '')?$vo['supplier']['name']:'-')); ?></td>
                    <td>
                        <?php if($vo['purchase_status'] == 0): ?>
                        <span class="badge badge-secondary">未采购</span>
                        <?php elseif($vo['purchase_status'] == 1): ?>
                        <span class="badge badge-info">采购中</span>
                        <?php else: ?>
                        <span class="badge badge-success">已入库</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {});
</script>
