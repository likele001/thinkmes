<?php /*a:1:{s:56:"/www/wwwroot/thinkmes/app/admin/view/mes/order/edit.html";i:1771379320;}*/ ?>
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">编辑订单</h3></div>
    <div class="card-body">
        <form id="form-edit" method="post" class="form-horizontal">
            <input type="hidden" name="row[id]" value="<?php echo htmlentities((string) $row['id']); ?>">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">订单名称</label>
                <div class="col-sm-6"><input type="text" name="row[order_name]" class="form-control" required value="<?php echo htmlentities((string) (isset($row['order_name']) && ($row['order_name'] !== '')?$row['order_name']:'')); ?>" placeholder="请输入订单名称"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">客户</label>
                <div class="col-sm-6">
                    <select name="row[customer_id]" class="form-control" id="customer_id">
                        <option value="">请选择客户</option>
                        <?php if(is_array($customerList) || $customerList instanceof \think\Collection || $customerList instanceof \think\Paginator): $i = 0; $__LIST__ = $customerList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities((string) $key); ?>" <?php if($row['customer_id'] == $key): ?>selected<?php endif; ?>><?php echo htmlentities((string) $vo); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">客户名称</label>
                <div class="col-sm-6"><input type="text" name="row[customer_name]" class="form-control" value="<?php echo htmlentities((string) (isset($row['customer_name']) && ($row['customer_name'] !== '')?$row['customer_name']:'')); ?>" placeholder="选择客户后自动填充"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">客户电话</label>
                <div class="col-sm-6"><input type="text" name="row[customer_phone]" class="form-control" value="<?php echo htmlentities((string) (isset($row['customer_phone']) && ($row['customer_phone'] !== '')?$row['customer_phone']:'')); ?>" placeholder="选择客户后自动填充"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">交货时间</label>
                <div class="col-sm-6"><input type="datetime-local" name="row[delivery_time]" class="form-control" value="<?php echo !empty($row['delivery_time']) ? date('Y-m-dTH : i', $row['delivery_time']) : ''; ?>"></div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">订单状态</label>
                <div class="col-sm-6">
                    <select name="row[status]" class="form-control">
                        <option value="0" <?php if($row['status'] == 0): ?>selected<?php endif; ?>>待生产</option>
                        <option value="1" <?php if($row['status'] == 1): ?>selected<?php endif; ?>>生产中</option>
                        <option value="2" <?php if($row['status'] == 2): ?>selected<?php endif; ?>>已完成</option>
                        <option value="3" <?php if($row['status'] == 3): ?>selected<?php endif; ?>>已取消</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">备注</label>
                <div class="col-sm-6"><textarea name="row[remark]" class="form-control" rows="3" placeholder="请输入备注"><?php echo htmlentities((string) (isset($row['remark']) && ($row['remark'] !== '')?$row['remark']:'')); ?></textarea></div>
            </div>
            
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">订单型号</label>
                <div class="col-sm-10">
                    <div id="model-list">
                        <?php if(!empty($orderModels)): if(is_array($orderModels) || $orderModels instanceof \think\Collection || $orderModels instanceof \think\Paginator): $k = 0; $__LIST__ = $orderModels;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$om): $mod = ($k % 2 );++$k;?>
                        <div class="model-item mb-2">
                            <select name="models[<?php echo htmlentities((string) $k-1); ?>][model_id]" class="form-control d-inline-block" style="width: 300px;">
                                <option value="">请选择型号</option>
                                <?php if(is_array($modelList) || $modelList instanceof \think\Collection || $modelList instanceof \think\Paginator): $i = 0; $__LIST__ = $modelList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <option value="<?php echo htmlentities((string) $key); ?>" <?php if($om['model_id'] == $key): ?>selected<?php endif; ?>><?php echo htmlentities((string) $vo); ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                            <input type="number" name="models[<?php echo htmlentities((string) $k-1); ?>][quantity]" class="form-control d-inline-block" style="width: 150px; margin-left: 10px;" placeholder="数量" min="1" value="<?php echo htmlentities((string) $om['quantity']); ?>">
                            <button type="button" class="btn btn-danger btn-sm ml-2 btn-remove-model">删除</button>
                        </div>
                        <?php endforeach; endif; else: echo "" ;endif; else: ?>
                        <div class="model-item mb-2">
                            <select name="models[0][model_id]" class="form-control d-inline-block" style="width: 300px;">
                                <option value="">请选择型号</option>
                                <?php if(is_array($modelList) || $modelList instanceof \think\Collection || $modelList instanceof \think\Paginator): $i = 0; $__LIST__ = $modelList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <option value="<?php echo htmlentities((string) $key); ?>"><?php echo htmlentities((string) $vo); ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                            <input type="number" name="models[0][quantity]" class="form-control d-inline-block" style="width: 150px; margin-left: 10px;" placeholder="数量" min="1" value="1">
                            <button type="button" class="btn btn-danger btn-sm ml-2 btn-remove-model" style="display:none;">删除</button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-success btn-sm mt-2" id="btn-add-model">添加型号</button>
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-sm-6 offset-sm-2">
                <button type="submit" form="form-edit" class="btn btn-primary">保存修改</button>
                <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/order/index" class="btn btn-default ml-2">返回列表</a>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    if (typeof jQuery === 'undefined') {
        setTimeout(arguments.callee, 50);
        return;
    }
    var $ = jQuery;
    
    $(function () {
        var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
        var modelIndex = $('.model-item').length;
        
        var modelOptions = '';
        $('#model-list select:first option').each(function() {
            if ($(this).val() !== '') {
                modelOptions += '<option value="' + $(this).val() + '">' + $(this).text() + '</option>';
            }
        });
        
        $('#btn-add-model').on('click', function() {
            var html = '<div class="model-item mb-2">' +
                '<select name="models[' + modelIndex + '][model_id]" class="form-control d-inline-block" style="width: 300px;">' +
                '<option value="">请选择型号</option>' +
                modelOptions +
                '</select>' +
                '<input type="number" name="models[' + modelIndex + '][quantity]" class="form-control d-inline-block" style="width: 150px; margin-left: 10px;" placeholder="数量" min="1" value="1">' +
                '<button type="button" class="btn btn-danger btn-sm ml-2 btn-remove-model">删除</button>' +
                '</div>';
            $('#model-list').append(html);
            modelIndex++;
            updateRemoveButtons();
        });
        
        $(document).on('click', '.btn-remove-model', function() {
            $(this).closest('.model-item').remove();
            updateRemoveButtons();
        });
        
        function updateRemoveButtons() {
            var count = $('.model-item').length;
            $('.btn-remove-model').toggle(count > 1);
        }
        updateRemoveButtons();
        
        $('#form-edit').attr('action', base + '/mes/order/edit?ids=<?php echo htmlentities((string) $row['id']); ?>');
        $('#form-edit').on('submit', function (e) {
            e.preventDefault();

            var data = {};
            $('#form-edit [name^="row["]').each(function () {
                var name = $(this).attr('name');
                var key = name.match(/row\[(.*?)\]/)[1];
                data[key] = $(this).val();
            });

            var models = [];
            $('#model-list .model-item').each(function () {
                var mid = $(this).find('select[name*="[model_id]"]').val();
                var qty = parseInt($(this).find('input[name*="[quantity]"]').val(), 10) || 0;
                if (mid) {
                    if (qty <= 0) {
                        qty = 1;
                        $(this).find('input[name*="[quantity]"]').val(qty);
                    }
                    models.push({ model_id: mid, quantity: qty });
                }
            });

            if (models.length === 0) {
                alert('请至少添加一个订单型号');
                return;
            }

            var fd = new FormData();
            Object.keys(data).forEach(function (k) {
                fd.append('row[' + k + ']', data[k]);
            });
            models.forEach(function (m, i) {
                fd.append('models[' + i + '][model_id]', m.model_id);
                fd.append('models[' + i + '][quantity]', m.quantity);
            });

            $.ajax({
                url: $('#form-edit').attr('action'),
                type: 'POST',
                dataType: 'json',
                data: fd,
                processData: false,
                contentType: false
            }).done(function (r) {
                alert(r.msg || (r.code === 1 ? '保存成功' : '失败'));
                if (r.code === 1) {
                    location.href = base + '/mes/order/index';
                }
            }).fail(function (xhr, status, err) {
                var msg = '请求失败：' + (xhr.responseText || status || err || '');
                try {
                    var json = JSON.parse(xhr.responseText);
                    msg = json.msg || msg;
                } catch (e) {}
                alert(msg);
            });
        });
    });
})();
</script>
