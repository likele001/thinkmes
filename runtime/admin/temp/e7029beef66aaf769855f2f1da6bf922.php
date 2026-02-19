<?php /*a:1:{s:59:"/www/wwwroot/thinkmes/app/admin/view/mes/report/detail.html";i:1771493368;}*/ ?>
<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title">报工审核详情</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>订单号</th>
                    <th>产品</th>
                    <th>型号</th>
                    <th>员工</th>
                    <th>产品编号</th>
                    <th>工序</th>
                    <th>类型</th>
                    <th>数量/工时</th>
                    <th>工资</th>
                    <th>报工时间</th>
                    <th>审核状态</th>
                    <th>质检结果</th>
                </tr>
                </thead>
                <tbody>
                <?php if($reports->isEmpty()): ?>
                <tr>
                    <td colspan="12" class="text-center text-muted">暂无报工记录</td>
                </tr>
                <?php else: if(is_array($reports) || $reports instanceof \think\Collection || $reports instanceof \think\Paginator): $i = 0; $__LIST__ = $reports;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?>
                <tr>
                    <td><?php echo htmlentities((string) (isset($r['allocation']['order']['order_no']) && ($r['allocation']['order']['order_no'] !== '')?$r['allocation']['order']['order_no']:'')); ?></td>
                    <td><?php echo htmlentities((string) (isset($r['allocation']['model']['product']['name']) && ($r['allocation']['model']['product']['name'] !== '')?$r['allocation']['model']['product']['name']:'')); ?></td>
                    <td><?php echo htmlentities((string) (isset($r['allocation']['model']['name']) && ($r['allocation']['model']['name'] !== '')?$r['allocation']['model']['name']:'')); ?></td>
                    <td><?php echo htmlentities((string) (isset($r['worker_name']) && ($r['worker_name'] !== '')?$r['worker_name']:'')); ?></td>
                    <td>
                        <?php 
                        $itemText = '';
                        $rawNos = $r['item_nos'] ?? '';
                        if ($rawNos) {
                            $tmpNos = json_decode($rawNos, true);
                            if (is_array($tmpNos)) {
                                $itemText = implode('<br>', array_map('htmlspecialchars', $tmpNos));
                            } else {
                                $itemText = htmlspecialchars((string)$rawNos, ENT_QUOTES, 'UTF-8');
                            }
                        }
                        echo $itemText ?: '-';
                         ?>
                    </td>
                    <td><?php echo htmlentities((string) (isset($r['allocation']['process']['name']) && ($r['allocation']['process']['name'] !== '')?$r['allocation']['process']['name']:'')); ?></td>
                    <td>
                        <?php if($r['work_type'] == 'piece'): ?>
                        <span class="badge badge-primary">计件</span>
                        <?php else: ?>
                        <span class="badge badge-info">计时</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($r['work_type'] == 'piece'): ?>
                        <?php echo htmlentities((string) (isset($r['quantity']) && ($r['quantity'] !== '')?$r['quantity']:0)); else: ?>
                        <?php echo htmlentities((string) (isset($r['work_hours']) && ($r['work_hours'] !== '')?$r['work_hours']:0)); ?>
                        <?php endif; ?>
                    </td>
                    <td>¥<?php echo htmlentities((string) (isset($r['wage']) && ($r['wage'] !== '')?$r['wage']:0)); ?></td>
                    <td><?php echo htmlentities((string) (isset($r['create_time_text']) && ($r['create_time_text'] !== '')?$r['create_time_text']:'')); ?></td>
                    <td>
                        <?php if($r['status'] == 1): ?>
                        <span class="badge badge-success">已通过</span>
                        <?php elseif($r['status'] == 2): ?>
                        <span class="badge badge-danger">已拒绝</span>
                        <?php else: ?>
                        <span class="badge badge-warning">待审核</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($r['quality_status'] == 1): ?>
                        <span class="badge badge-success">合格</span>
                        <?php elseif($r['quality_status'] == 2): ?>
                        <span class="badge badge-danger">不合格</span>
                        <?php else: ?>
                        <span class="badge badge-secondary">未质检</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; else: echo "" ;endif; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(!$reports->isEmpty()): if(is_array($reports) || $reports instanceof \think\Collection || $reports instanceof \think\Paginator): $i = 0; $__LIST__ = $reports;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?>
        <div class="card mb-3">
            <div class="card-header">
                <strong>审核信息（ID: <?php echo htmlentities((string) $r['id']); ?>）</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p>审核状态：<?php echo htmlentities((string) (isset($r['status_text']) && ($r['status_text'] !== '')?$r['status_text']:'')); ?></p>
                        <p>质检结果：<?php echo htmlentities((string) (isset($r['quality_text']) && ($r['quality_text'] !== '')?$r['quality_text']:'')); ?></p>
                        <p>审核时间：<?php echo htmlentities((string) (isset($r['audit_time_text']) && ($r['audit_time_text'] !== '')?$r['audit_time_text']:'')); ?></p>
                        <p>审核人ID：<?php echo htmlentities((string) (isset($r['audit_user_id']) && ($r['audit_user_id'] !== '')?$r['audit_user_id']:0)); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p>拒绝原因：<?php echo htmlentities((string) (isset($r['audit_reason']) && ($r['audit_reason'] !== '')?$r['audit_reason']:'')); ?></p>
                        <p>审核备注：<?php echo htmlentities((string) (isset($r['audit_notes']) && ($r['audit_notes'] !== '')?$r['audit_notes']:'')); ?></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label>相关图片</label>
                        <div>
                            <?php if(empty($r['image_urls'])): ?>
                            <span class="text-muted">无</span>
                            <?php else: foreach($r['image_urls'] as $u): ?>
                            <a href="<?php echo htmlentities((string) $u); ?>" target="_blank">
                                <img src="<?php echo htmlentities((string) $u); ?>" style="height:60px;border-radius:3px;margin-right:4px;margin-bottom:4px;">
                            </a>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label>相关视频</label>
                        <div>
                            <?php if(empty($r['audit_videos'])): ?>
                            <span class="text-muted">无</span>
                            <?php else: foreach($r['audit_videos'] as $v): ?>
                            <video controls preload="metadata" style="width:200px;border-radius:4px;margin-right:8px;margin-bottom:8px;">
                                <source src="<?php echo htmlentities((string) $v); ?>">
                            </video>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
        <?php endif; ?>

        <div class="form-group">
            <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/report/index" class="btn btn-default">返回列表</a>
        </div>
    </div>
</div>

