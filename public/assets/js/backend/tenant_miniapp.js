(function () {
    if (typeof jQuery === 'undefined') {
        setTimeout(arguments.callee, 50);
        return;
    }
    var $ = jQuery;
    $(function () {
        var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
        var form = $('#miniapp-form');
        if (!form.length) {
            return;
        }
        if (!form.attr('action')) {
            form.attr('action', base + '/tenant/miniapp');
        }
        form.on('submit', function (e) {
            e.preventDefault();
            $.post(form.attr('action'), form.serialize(), function (r) {
                alert((r && r.msg) || '保存完成');
            }, 'json');
        });
    });
})();

