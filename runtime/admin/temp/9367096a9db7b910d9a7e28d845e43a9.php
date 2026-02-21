<?php /*a:1:{s:73:"/www/wwwroot/thinkmes/app/admin/view/mes/production_plan/allocations.html";i:1771552608;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">查看分工 - <?php echo htmlentities((string) (isset($plan['plan_name']) && ($plan['plan_name'] !== '')?$plan['plan_name']:'')); ?></h3>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <strong>计划编码：</strong><?php echo htmlentities((string) (isset($plan['plan_code']) && ($plan['plan_code'] !== '')?$plan['plan_code']:'')); ?>　
            <strong>订单：</strong><?php echo htmlentities((string) (isset($plan['order']['order_no']) && ($plan['order']['order_no'] !== '')?$plan['order']['order_no']:'')); ?>　
            <strong>计划数量：</strong><?php echo htmlentities((string) (isset($plan['total_quantity']) && ($plan['total_quantity'] !== '')?$plan['total_quantity']:0)); ?>　
            <strong>完成数量：</strong><?php echo htmlentities((string) (isset($plan['completed_quantity']) && ($plan['completed_quantity'] !== '')?$plan['completed_quantity']:0)); ?>
        </div>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>分配编码</th>
                    <th>订单号</th>
                    <th>产品</th>
                    <th>产品型号</th>
                    <th>工序名称</th>
                    <th>员工姓名</th>
                    <th>分配数量</th>
                    <th>已完成数量</th>
                    <th>剩余数量</th>
                    <th>状态</th>
                    <th>创建时间</th>
                </tr>
            </thead>
            <tbody>
                <?php if($allocations->isEmpty()): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">尚未为该生产计划分配工序</td>
                </tr>
                <?php else: if(is_array($allocations) || $allocations instanceof \think\Collection || $allocations instanceof \think\Paginator): $i = 0; $__LIST__ = $allocations;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$a): $mod = ($i % 2 );++$i;?>
                <tr>
                    <td><?php echo htmlentities((string) (isset($a['allocation_code']) && ($a['allocation_code'] !== '')?$a['allocation_code']:'-')); ?></td>
                    <td><?php echo htmlentities((string) (isset($a['order']['order_no']) && ($a['order']['order_no'] !== '')?$a['order']['order_no']:'-')); ?></td>
                    <td><?php echo htmlentities((string) (isset($a['model']['product']['name']) && ($a['model']['product']['name'] !== '')?$a['model']['product']['name']:'-')); ?></td>
                    <td>
                        <?php if(isset($a['model'])): ?>
                        <?php echo htmlentities((string) (isset($a['model']['name']) && ($a['model']['name'] !== '')?$a['model']['name']:'')); if($a['model']['model_code']): ?> (<?php echo htmlentities((string) $a['model']['model_code']); ?>)<?php endif; else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlentities((string) (isset($a['process']['name']) && ($a['process']['name'] !== '')?$a['process']['name']:'-')); ?></td>
                    <td><?php echo htmlentities((string) (isset($a['user']['nickname']) && ($a['user']['nickname'] !== '')?$a['user']['nickname']:'-')); ?></td>
                    <td><?php echo htmlentities((string) (isset($a['quantity']) && ($a['quantity'] !== '')?$a['quantity']:0)); ?></td>
                    <td><?php echo htmlentities((string) (isset($a['completed_quantity']) && ($a['completed_quantity'] !== '')?$a['completed_quantity']:0)); ?></td>
                    <td><?php echo max(0, (int)$a['quantity'] - (int)$a['completed_quantity']); ?></td>
                    <td>
                        <?php switch($a['status']): case "0": ?><span class="badge badge-secondary">待开始</span><?php break; case "1": ?><span class="badge badge-primary">进行中</span><?php break; case "2": ?><span class="badge badge-success">已完成</span><?php break; default: ?><span class="badge badge-secondary">未知</span>
                        <?php endswitch; ?>
                    </td>
                    <td>
                        <?php if(isset($a['create_time']) && $a['create_time']): ?>
                        <?php echo htmlentities((string) date('Y-m-d H:i:s',!is_numeric($a['create_time'])? strtotime($a['create_time']) : $a['create_time'])); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; else: echo "" ;endif; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="mt-3">
            <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/production_plan/index" class="btn btn-default">返回计划列表</a>
        </div>
    </div>
</div>
