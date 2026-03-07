(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            var $ = jQuery;
            $(document).off('click', '.btn-save').on('click', '.btn-save', function () {
                var form = $('#form-sms')[0] || document.querySelector('form');
                if (!form) return;
                var btn = $(this);
                btn.prop('disabled', true);
                $.post(base + '/sms_config/index', $(form).serialize(), function (r) {
                    btn.prop('disabled', false);
                    if (r.code == 1) alert(r.msg || '保存成功');
                    else alert(r.msg || '保存失败');
                }, 'json').fail(function () { btn.prop('disabled', false); });
            });
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
