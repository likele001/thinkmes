<?php /*a:1:{s:54:"/www/wwwroot/thinkmes/app/admin/view/addon/config.html";i:1771477464;}*/ ?>
<section class="content">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">插件配置 - <?php echo htmlentities((string) (isset($name) && ($name !== '')?$name:'')); ?></h3>
        </div>
        <div class="card-body">
            <form id="form-addon-config" method="post" action="<?php echo url('addon/config',['name'=>$name,'tenant_id'=>$tenantId]); ?>">
                <?php if($name=='cloudstorage'): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>配置作用租户</label>
                            <select name="tenant_id" class="form-control">
                                <option value="0" <?php if($tenantId==0): ?>selected<?php endif; ?>>平台默认</option>
                            </select>
                            <small class="form-text text-muted">不同租户可单独配置云存储，未配置时使用平台默认。</small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="row">
                    <?php if(!empty($schema)): if(is_array($schema) || $schema instanceof \think\Collection || $schema instanceof \think\Paginator): $i = 0; $__LIST__ = $schema;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo htmlentities((string) (isset($item['title']) && ($item['title'] !== '')?$item['title']:$item['name'])); ?></label>
                            <?php $fieldName = $item['name'];$type = $item['type'] ?? 'text';$value = $values[$fieldName] ?? ($item['default'] ?? ''); if($type=='select'): ?>
                            <select name="config[<?php echo htmlentities((string) $fieldName); ?>]" class="form-control">
                                <?php if(is_array($item['options']) || $item['options'] instanceof \think\Collection || $item['options'] instanceof \think\Paginator): $i = 0; $__LIST__ = $item['options'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$opt): $mod = ($i % 2 );++$i;?>
                                <option value="<?php echo htmlentities((string) $opt['value']); ?>" <?php if($opt['value']==$value): ?>selected<?php endif; ?>><?php echo htmlentities((string) $opt['label']); ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                            <?php elseif($type=='textarea'): ?>
                            <textarea name="config[<?php echo htmlentities((string) $fieldName); ?>]" class="form-control" rows="4"><?php echo htmlentities((string) $value); ?></textarea>
                            <?php else: ?>
                            <input type="text" name="config[<?php echo htmlentities((string) $fieldName); ?>]" value="<?php echo htmlentities((string) $value); ?>" class="form-control">
                            <?php endif; if(!empty($item['description'])): ?>
                            <small class="form-text text-muted"><?php echo htmlentities((string) $item['description']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; endif; else: echo "" ;endif; else: ?>
                    <div class="col-12">
                        <p class="text-muted mb-0">当前插件未定义配置项。</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</section>
