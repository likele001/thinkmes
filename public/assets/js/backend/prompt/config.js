(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '/admin';
    var Controller = {
        index: function () {
            // 应用设置页 - 表单保存
            $(document).off('click', '#btn-save').on('click', '#btn-save', function () {
                var formData = $('#config-form').serialize();
                $.post(base + '/prompt/config/index', formData, function (r) {
                    alert(r.msg || (r.code == 1 ? '保存成功' : '保存失败'));
                }, 'json');
            });
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
