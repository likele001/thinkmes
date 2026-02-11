/**
 * iframe 多标签尺寸修复（对齐 FastAdmin 标准）
 * 使用固定像素高度，兼容 AdminLTE 3 的 .content-wrapper / .container-fluid
 */
(function() {
    'use strict';
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;

    function getMainTabContent() {
        var $el = $('#tab-content-main');
        if ($el.length) return $el;
        $el = $('.content-wrapper .content .tab-content').first();
        return $el.length ? $el : $('.tab-content').first();
    }

    /** 可用内容高度：视口高度减去顶栏、标签栏等（与 FastAdmin iframeHeight 思路一致） */
    function getContentHeight() {
        var h = $(window).height();
        var offset = 200;
        if (h > 400) offset = 180;
        return Math.max(400, h - offset);
    }

    function forceSize(el, heightPx) {
        if (!el) return;
        var px = heightPx + 'px';
        el.style.setProperty('display', 'block', 'important');
        el.style.setProperty('visibility', 'visible', 'important');
        el.style.setProperty('opacity', '1', 'important');
        el.style.setProperty('height', px, 'important');
        el.style.setProperty('min-height', px, 'important');
        el.style.setProperty('width', '100%', 'important');
        el.style.setProperty('position', 'relative', 'important');
        el.style.setProperty('z-index', '1', 'important');
    }

    function fixIframe(iframeEl) {
        if (!iframeEl || !iframeEl.tagName || iframeEl.tagName.toLowerCase() !== 'iframe') return;
        var height = getContentHeight();
        iframeEl.style.setProperty('display', 'block', 'important');
        iframeEl.style.setProperty('visibility', 'visible', 'important');
        iframeEl.style.setProperty('width', '100%', 'important');
        iframeEl.style.setProperty('height', height + 'px', 'important');
        iframeEl.style.setProperty('min-height', height + 'px', 'important');
        iframeEl.style.setProperty('border', '0', 'important');
    }

    function fixAllIframes() {
        var $tabContent = getMainTabContent();
        if (!$tabContent.length) return;
        var height = getContentHeight();
        $tabContent.find('.tab-pane').each(function() {
            var $pane = $(this);
            var paneEl = this;
            var isActive = $pane.hasClass('active') && $pane.hasClass('show');
            if (isActive) forceSize(paneEl, height);
            $pane.find('iframe').each(function() {
                fixIframe(this);
            });
        });
    }

    function fixContainerSizes() {
        var $tabContent = getMainTabContent();
        if (!$tabContent.length) return;
        var height = getContentHeight();
        var tabContentEl = $tabContent[0];
        forceSize(tabContentEl, height);
        var container = tabContentEl.closest('.container-fluid') || tabContentEl.closest('.container-lg');
        if (container) {
            container.style.setProperty('min-height', height + 'px', 'important');
            container.style.setProperty('height', 'auto', 'important');
        }
        var contentDiv = tabContentEl.closest('.content');
        if (contentDiv) {
            contentDiv.style.setProperty('min-height', height + 'px', 'important');
        }
        fixAllIframes();
    }

    function init() {
        setTimeout(fixContainerSizes, 50);
        setTimeout(fixAllIframes, 200);
        setTimeout(fixAllIframes, 600);
    }

    var debounce = function(fn, wait) {
        var t;
        return function() {
            clearTimeout(t);
            t = setTimeout(fn, wait);
        };
    };

    $(document).ready(function() {
        init();
    });
    $(window).on('load', function() {
        setTimeout(init, 300);
    });
    $(window).on('resize', debounce(function() {
        fixContainerSizes();
    }, 200));

    $(document).on('shown.bs.tab', '.content-header .nav-tabs .nav-link', function() {
        setTimeout(fixAllIframes, 80);
    });

    window.iframeFix = {
        fixAll: fixAllIframes,
        fixContainer: fixContainerSizes,
        fixIframe: fixIframe,
        getContentHeight: getContentHeight
    };
})();
