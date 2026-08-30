// 临时修复：为没有Toastr的情况提供备用方案
if (typeof Toastr === 'undefined') {
    window.Toastr = {
        success: function(msg) {
            if (typeof Layer !== 'undefined') {
                Layer.msg(msg, {icon: 1});
            } else {
                alert(msg);
            }
        },
        error: function(msg) {
            if (typeof Layer !== 'undefined') {
                Layer.msg(msg, {icon: 2});
            } else {
                alert(msg);
            }
        },
        warning: function(msg) {
            if (typeof Layer !== 'undefined') {
                Layer.msg(msg, {icon: 0});
            } else {
                alert(msg);
            }
        },
        info: function(msg) {
            if (typeof Layer !== 'undefined') {
                Layer.msg(msg, {icon: 6});
            } else {
                alert(msg);
            }
        }
    };
}
