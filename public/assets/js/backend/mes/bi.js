/**
 * MES BI 报表页面 JS
 * 大屏（dashboard）由视图内 bi-dashboard.js + ECharts 独立渲染，此处 dashboard 仅占位，避免 Chart is not defined
 */
(function () {
    var Controller = {
        dashboard: function () {
            // 大屏内容在 iframe 内由 dashboard.html + bi-dashboard.js 渲染（ECharts），不在此处请求/绘图，避免重复与 Chart 未定义报错
        },
        productionEfficiency: function () {
            // 生产效率分析
        },
        qualityAnalysis: function () {
            // 质量分析
        },
        costAnalysis: function () {
            // 成本分析
        }
    };

    window.__backendController = Controller;
})();
