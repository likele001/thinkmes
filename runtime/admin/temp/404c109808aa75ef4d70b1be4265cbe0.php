<?php /*a:1:{s:58:"/www/wwwroot/thinkmes/app/admin/view/mes/customer/add.html";i:1771720143;}*/ ?>
<form id="form-add" class="form-horizontal" role="form" action="" method="post">
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">客户名称<span class="text-danger">*</span></label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-customer_name" class="form-control" name="row[customer_name]" value="" placeholder="请输入客户名称">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">登录账号<span class="text-danger">*</span></label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-login_account" class="form-control" name="row[login_account]" value="" placeholder="用于客户前端登录的账号">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">登录密码<span class="text-danger">*</span></label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-login_password" type="password" class="form-control" name="row[login_password]" value="" placeholder="6-32 位密码">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">联系人</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-contact_person" class="form-control" name="row[contact_person]" value="" placeholder="请输入联系人">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">联系电话</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-contact_phone" class="form-control" name="row[contact_phone]" value="" placeholder="请输入联系电话">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">地址</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-address" class="form-control" name="row[address]" value="" placeholder="请输入地址">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">状态</label>
        <div class="col-xs-12 col-sm-8">
            <select id="c-status" class="form-control" name="row[status]">
                <option value="1" selected>正常</option>
                <option value="0">禁用</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2">默认语言</label>
        <div class="col-xs-12 col-sm-8">
            <select id="c-default_lang" class="form-control" name="row[default_lang]">
                <option value="zh-cn" selected>简体中文</option>
                <option value="en-us">English</option>
            </select>
        </div>
    </div>
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-success btn-embossed">确定</button>
            <button type="reset" class="btn btn-default btn-embossed">重置</button>
        </div>
    </div>
</form>
<script>
(function() {
    var form = $('#form-add')[0];
    var submitBtn = $(form).find('button[type="submit"]');

    $(form).on('submit', function(e) {
        e.preventDefault();

        var name = $(form).find('[name="row[customer_name]"]').val();
        var account = $(form).find('[name="row[login_account]"]').val();
        var password = $(form).find('[name="row[login_password]"]').val();

        if (!name || !name.trim()) {
            alert('请输入客户名称');
            return false;
        }
        if (!account || !account.trim()) {
            alert('请输入登录账号');
            return false;
        }
        if (!password || password.length < 6 || password.length > 32) {
            alert('请输入 6-32 位登录密码');
            return false;
        }

        submitBtn.prop('disabled', true);

        $.ajax({
            url: $(form).attr('action') || window.location.href,
            type: 'POST',
            data: $(form).serialize(),
            dataType: 'json',
            success: function(r) {
                if (r.msg) {
                    alert(r.msg);
                }
                if (r.code === 1) {
                    window.location.href = '/admin/mes/customer';
                }
            },
            error: function() {
                alert('网络错误，请重试');
            },
            complete: function() {
                submitBtn.prop('disabled', false);
            }
        });
    });
})();
</script>

