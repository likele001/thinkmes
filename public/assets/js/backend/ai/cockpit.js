/**
 * 经营数据大屏 - 数据由页面内 script 拉取 getCockpitData，此处仅占位供 backend-loader 加载不报错
 */
(function () {
    var Controller = {
        index: function () {
            // 逻辑已在 ai/cockpit/index.html 内联脚本中实现（拉取 getCockpitData、刷新按钮、定时刷新）
        }
    };
    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) Controller[action]();
    window.__backendController = Controller;
})();
