<?php /*a:1:{s:58:"/www/wwwroot/thinkmes/app/admin/view/mes/report/audit.html";i:1771556051;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">审核报工</h3>
    </div>
    <div class="card-body">
        <form id="form-audit" method="post" action="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/report/audit">
            <input type="hidden" name="ids" value="<?php echo htmlentities((string) (isset($ids) && ($ids !== '')?$ids:'')); ?>">
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>报工ID</th>
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
                        <th>图片</th>
                        <th>视频</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($reports->isEmpty()): ?>
                    <tr>
                        <td colspan="13" class="text-center text-muted">暂无报工记录</td>
                    </tr>
                    <?php else: if(is_array($reports) || $reports instanceof \think\Collection || $reports instanceof \think\Paginator): $i = 0; $__LIST__ = $reports;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$r): $mod = ($i % 2 );++$i;?>
                    <tr>
                        <td><?php echo htmlentities((string) $r['id']); ?></td>
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
                            <?php if(empty($r['image_urls'])): ?>
                            <span class="text-muted">无</span>
                            <?php else: foreach($r['image_urls'] as $u): ?>
                            <a href="<?php echo htmlentities((string) $u); ?>" target="_blank">
                                <img src="<?php echo htmlentities((string) $u); ?>" style="height:40px;border-radius:3px;margin-right:4px;margin-bottom:4px;">
                            </a>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(empty($r['audit_videos'])): ?>
                            <span class="text-muted">无</span>
                            <?php else: foreach($r['audit_videos'] as $v): ?>
                            <a href="<?php echo htmlentities((string) $v); ?>" target="_blank" class="btn btn-xs btn-outline-secondary mr-1">视频</a>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="form-group">
                <label>审核结果</label>
                <div>
                    <label class="radio-inline mr-3">
                        <input type="radio" name="status" value="1" <?php if($status=='1'): ?>checked<?php endif; ?>> 通过
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="status" value="2" <?php if($status=='2'): ?>checked<?php endif; ?>> 拒绝
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>质检结果</label>
                <div>
                    <label class="radio-inline mr-3">
                        <input type="radio" name="quality_status" value="1" checked> 合格
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="quality_status" value="0"> 不合格
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>拒绝原因</label>
                <textarea name="audit_reason" class="form-control" rows="2" placeholder="若选择拒绝，请填写拒绝原因"></textarea>
            </div>

            <div class="form-group">
                <label>审核备注</label>
                <textarea name="audit_notes" class="form-control" rows="2" placeholder="可选，补充说明"></textarea>
            </div>

            <div class="form-group">
                <label>审核图片</label>
                <div>
                    <div class="upload-area border border-dashed rounded p-3 text-center mb-2"
                         id="audit-images-area"
                         style="border-style:dashed;cursor:pointer;background:#f8f9fa;">
                        <input type="file" id="audit-images-input" accept="image/*" multiple style="display:none;">
                        <div class="upload-icon mb-2">
                            <i class="fas fa-image fa-2x text-muted"></i>
                        </div>
                        <div class="upload-text">点击或拖拽图片到此处上传</div>
                        <div class="upload-note text-muted small">支持多张图片，建议用于质检说明</div>
                    </div>
                    <input type="hidden" name="audit_images" id="audit-images">
                    <div id="audit-images-preview" class="mt-2 d-flex flex-wrap"></div>
                </div>
            </div>

            <div class="form-group">
                <label>审核视频</label>
                <div>
                    <div class="upload-area border border-dashed rounded p-3 text-center mb-2"
                         id="audit-videos-area"
                         style="border-style:dashed;cursor:pointer;background:#f8f9fa;">
                        <input type="file" id="audit-videos-input" accept="video/*" multiple style="display:none;">
                        <div class="upload-icon mb-2">
                            <i class="fas fa-video fa-2x text-muted"></i>
                        </div>
                        <div class="upload-text">点击或拖拽视频到此处上传</div>
                        <div class="upload-note text-muted small">支持多段短视频，便于记录质检过程</div>
                    </div>
                    <input type="hidden" name="audit_videos" id="audit-videos">
                    <div id="audit-videos-preview" class="mt-2 d-flex flex-wrap"></div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">提交审核</button>
                <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/report/index" class="btn btn-default">返回列表</a>
            </div>
        </form>
    </div>
</div>
