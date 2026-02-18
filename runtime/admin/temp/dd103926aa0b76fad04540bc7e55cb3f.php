<?php /*a:1:{s:66:"/www/wwwroot/thinkmes/app/admin/view/mes/production_plan/edit.html";i:1771380949;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">编辑生产计划</h3>
    </div>
    <div class="card-body">
        <form id="form-edit" method="post" class="form-horizontal">
            <input type="hidden" name="row[id]" value="<?php echo htmlentities((string) $data['id']); ?>">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">订单 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select name="row[order_id]" class="form-control" id="order_id" required>
                        <option value="">请选择订单</option>
                        <?php if(is_array($orderList) || $orderList instanceof \think\Collection || $orderList instanceof \think\Paginator): $i = 0; $__LIST__ = $orderList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $key); ?>" <?php if($data['order_id'] == $key): ?>selected<?php endif; ?>><?php echo htmlentities((string) $vo); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">产品型号 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <select name="row[model_id]" class="form-control" id="model_id" data-selected="<?php echo htmlentities((string) (isset($data['model_id']) && ($data['model_id'] !== '')?$data['model_id']:'')); ?>" required>
                        <option value="">请先选择订单</option>
                    </select>
                    <small class="form-text text-muted">选择订单后自动加载该订单的型号</small>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">计划名称 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="text" name="row[plan_name]" class="form-control" value="<?php echo htmlentities((string) $data['plan_name']); ?>" placeholder="请输入计划名称" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">计划数量 <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="number" name="row[total_quantity]" class="form-control" min="1" value="<?php echo htmlentities((string) $data['total_quantity']); ?>" placeholder="请输入计划数量" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">计划开始时间</label>
                <div class="col-sm-6">
                    <input type="datetime-local" name="row[planned_start_time]" class="form-control" value="<?php echo !empty($data['planned_start_time']) ? date('Y-m-dTH : i', $data['planned_start_time']) : ''; ?>">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">计划结束时间</label>
                <div class="col-sm-6">
                    <input type="datetime-local" name="row[planned_end_time]" class="form-control" value="<?php echo !empty($data['planned_end_time']) ? date('Y-m-dTH : i', $data['planned_end_time']) : ''; ?>">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">状态</label>
                <div class="col-sm-6">
                    <select name="row[status]" class="form-control">
                        <option value="0" <?php if($data['status'] == '0'): ?>selected<?php endif; ?>>待开始</option>
                        <option value="1" <?php if($data['status'] == '1'): ?>selected<?php endif; ?>>进行中</option>
                        <option value="2" <?php if($data['status'] == '2'): ?>selected<?php endif; ?>>已完成</option>
                        <option value="3" <?php if($data['status'] == '3'): ?>selected<?php endif; ?>>已暂停</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">备注</label>
                <div class="col-sm-6">
                    <textarea name="row[remark]" class="form-control" rows="3" placeholder="请输入备注"><?php echo htmlentities((string) $data['remark']); ?></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-8 offset-sm-2">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/production_plan/index" class="btn btn-default">返回</a>
                </div>
            </div>
        </form>
    </div>
</div>
