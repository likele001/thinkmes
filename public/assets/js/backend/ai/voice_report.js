(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var Controller = {
        index: function () {
            // 语音报工页面，当前为占位，后续接入录音+语音识别
        }
    };
    window.__backendController = Controller;
})();
