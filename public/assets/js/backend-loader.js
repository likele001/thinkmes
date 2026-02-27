/**
 * 仿 FastAdmin：按 Config.jsname 加载页面 JS，并执行 Controller[Config.actionname]()
 * 同时初始化侧栏菜单（AdminLTE 3 结构 + Font Awesome 图标）
 */
(function () {
    var $ = (typeof jQuery !== 'undefined') ? jQuery : null;
    var base = (typeof Config !== 'undefined' && Config.site && Config.site.cdnurl) ? Config.site.cdnurl : '';
    var jsPath = (base && base !== '/') ? (base.replace(/\/$/, '') + '/assets/js/') : '/assets/js/';
    var menuUrl = (typeof Config !== 'undefined' && Config.menu_url) ? Config.menu_url : ((typeof Config !== 'undefined' && Config.moduleurl) ? (Config.moduleurl + '/index/menu') : '/admin/index/menu');

    // 图标转为 Font Awesome 类名（AdminLTE 3 使用）
    function mapIconToFontAwesome(icon) {
        if (!icon) return 'fas fa-circle';
        var s = (icon || '').replace(/^\s+|\s+$/g, '');
        if (/^f[abrs]?\s+fa-/.test(s)) return s.indexOf(' ') >= 0 ? s : 'fas ' + s;
        if (/^fa-/.test(s)) return 'fas ' + s;
        var iconName = s.replace(/^(fa|fas|far|fal|fab)\s+fa-/, '').replace(/^fa-/, '');
        var iconMap = {
            'home': 'fas fa-tachometer-alt',
            'dashboard': 'fas fa-tachometer-alt',
            'cube': 'fas fa-cube',
            'clipboard': 'fas fa-clipboard',
            'sitemap': 'fas fa-sitemap',
            'shopping-cart': 'fas fa-shopping-cart',
            'user': 'fas fa-user',
            'users': 'fas fa-users',
            'cog': 'fas fa-cog',
            'gear': 'fas fa-cog',
            'wrench': 'fas fa-wrench',
            'list': 'fas fa-list',
            'table': 'fas fa-table',
            'file': 'fas fa-file',
            'folder': 'fas fa-folder',
            'image': 'fas fa-image',
            'picture': 'fas fa-image',
            'bar-chart': 'fas fa-chart-bar',
            'line-chart': 'fas fa-chart-line',
            'pie-chart': 'fas fa-chart-pie',
            'lock': 'fas fa-lock',
            'unlock': 'fas fa-lock-open',
            'key': 'fas fa-key',
            'shield': 'fas fa-shield-alt',
            'bell': 'fas fa-bell',
            'envelope': 'fas fa-envelope',
            'search': 'fas fa-search',
            'plus': 'fas fa-plus',
            'minus': 'fas fa-minus',
            'edit': 'fas fa-edit',
            'pencil': 'fas fa-pencil-alt',
            'trash': 'fas fa-trash',
            'remove': 'fas fa-times',
            'close': 'fas fa-times',
            'check': 'fas fa-check',
            'times': 'fas fa-times',
            'arrow-left': 'fas fa-arrow-left',
            'arrow-right': 'fas fa-arrow-right',
            'chevron-left': 'fas fa-chevron-left',
            'chevron-right': 'fas fa-chevron-right'
        };
        return iconMap[iconName] || 'fas fa-circle';
    }

    function normalizeUrl(raw, name) {
        var url = raw || '';
        if ((!url || url === '#' || url === 'javascript:;') && name) {
            var path = String(name).replace(/\./g, '/').replace(/^\//, '');
            var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
            url = base ? (base.replace(/\/$/, '') + '/' + path) : ('/admin/' + path);
        }
        if (!url) {
            url = '#';
        }
        if (url === '#' || /^javascript:/i.test(url) || /^https?:\/\//i.test(url) || /^\/\//.test(url)) {
            return url;
        }
        var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
        if (url.charAt(0) !== '/') {
            url = (base ? base.replace(/\/$/, '') : '') + '/' + url.replace(/^\//, '');
        }
        // 确保同源链接都带 /admin 前缀，避免手机或 iframe 内缺少 admin 目录
        if (url && url.charAt(0) === '/' && url.indexOf('/admin') !== 0 && !/^\/\//.test(url)) {
            url = '/admin' + (url === '/' ? '' : url);
        }
        return url;
    }

    function findFirstChildHref(node) {
        if (!node) {
            return '#';
        }
        if (!node.children || !node.children.length) {
            return normalizeUrl(node.url, node.name);
        }
        for (var i = 0; i < node.children.length; i++) {
            var child = node.children[i];
            var u = normalizeUrl(child.url, child.name);
            if (u && u !== '#' && u !== 'javascript:;') {
                return u;
            }
            if (child.children && child.children.length) {
                var deep = findFirstChildHref(child);
                if (deep && deep !== '#' && deep !== 'javascript:;') {
                    return deep;
                }
            }
        }
        return normalizeUrl(node.url, node.name);
    }

    // 轻量菜单：简单 ul/li/a 结构，无 treeview，点击直接跳转或打开 Tab
    function renderMenu(items, ul) {
        if (!ul) ul = $('<ul class="menu-children"></ul>');
        (items || []).forEach(function (it) {
            var hasChild = it.children && it.children.length > 0;
            var href = normalizeUrl(it.url, it.name);
            var iconClass = mapIconToFontAwesome(it.icon || '');
            var title = (it.title || it.name || '').trim();
            if (!title) return;

            var li = $('<li class="menu-item"></li>');
            var addtabsId = (it.id ? ('m' + it.id) : ((it.name || '').replace(/[\/\.]/g, '_').replace(/^_+|_+$/g, '') || ('tab_' + Math.random().toString(36).slice(2, 8))));
            if (hasChild) {
                li.addClass('has-children');
            }
            // 父级菜单（有子菜单）默认不跳转，只负责展开/收起
            var linkHref = (hasChild || !href || href === '#') ? 'javascript:;' : href;
            var dataUrl = (hasChild ? '' : href);
            var a = $('<a class="menu-link"></a>')
                .attr('href', linkHref)
                .attr('data-url', dataUrl)
                .attr('data-addtabs', addtabsId)
                .attr('data-title', title)
                .html('<span class="menu-icon"><i class="' + iconClass + '"></i></span><span class="menu-text">' + title + '</span>');
            if (hasChild) {
                a.append('<span class="menu-arrow"><i class="fas fa-angle-left"></i></span>');
                var sub = $('<ul class="menu-children"></ul>');
                renderMenu(it.children, sub);
                li.append(a).append(sub);
            } else {
                li.append(a);
            }
            ul.append(li);
        });
        return ul;
    }

    function fallbackMenu() {
        var $menu = $('#menu-tree');
        if ($menu.length) {
            var indexUrl = (typeof Config !== 'undefined' && Config.moduleurl) ? (Config.moduleurl + '/index/index') : '/admin/index/index';
            $menu.html(
                '<li class="menu-item">' +
                '<a href="' + indexUrl + '" class="menu-link" data-url="' + indexUrl + '" data-addtabs="index" data-title="首页">' +
                '<span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span><span class="menu-text">首页</span></a></li>'
            );
        }
    }

    function loadMenu() {
        if (typeof jQuery === 'undefined') {
            setTimeout(loadMenu, 50);
            return;
        }
        var $ = jQuery;
        var $menu = $('#menu-tree');
        if (!$menu.length) {
            setTimeout(loadMenu, 100);
            return;
        }
        $.get(menuUrl, function (res) {
            if (res.code === 1 && res.data && res.data.length) {
                function filterIndexMenu(items) {
                    return items.filter(function(item) {
                        var href = (item.url && item.url !== '#') ? item.url : (item.name ? ('/' + item.name.replace(/\./g, '/')) : '#');
                        var isIndex = href && (href.indexOf('/admin/index/index') !== -1 || href === '/admin/' || href === '/admin' || href.indexOf('/admin/index') === 0);
                        if (item.children && item.children.length > 0) {
                            item.children = filterIndexMenu(item.children);
                        }
                        return !isIndex;
                    });
                }
                $menu.empty();
                var filteredData = filterIndexMenu(res.data);
                var indexUrl = (typeof Config !== 'undefined' && Config.moduleurl) ? (Config.moduleurl + '/index/index') : '/admin/index/index';
                var $homeItem = $(
                    '<li class="menu-item">' +
                    '<a href="' + indexUrl + '" class="menu-link" data-url="' + indexUrl + '" data-addtabs="index" data-title="首页">' +
                    '<span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span><span class="menu-text">首页</span></a></li>'
                );
                $menu.append($homeItem);
                var $menuItems = renderMenu(filteredData);
                $menuItems.children().each(function() {
                    $menu.append(this);
                });
                // 不再使用 AdminLTE treeview，避免 overlay/点击被拦截
            } else {
                fallbackMenu();
            }
        }, 'json').fail(function () {
            fallbackMenu();
        });
    }

    function loadPageJs() {
        if (!$ || typeof jQuery === 'undefined' || typeof Config === 'undefined') {
            setTimeout(loadPageJs, 50);
            return;
        }
        var jsname = (Config.jsname || '').replace(/^backend\//, '');
        if (!jsname) return;
        var jsFile = jsPath + 'backend/' + jsname + '.js';
        $.getScript(jsFile, function () {
            var action = Config.actionname || 'index';
            if (window.__backendController && typeof window.__backendController[action] === 'function') {
                try {
                    window.__backendController[action]();
                } catch (e) {
                    console.error('Error executing controller action:', e);
                }
            }
        }).fail(function () {});
    }

    // 捕获阶段处理；手机端若被遮罩挡住则用「坐标 + elementFromPoint」取到真正菜单项再跳转
    function bindMenuClick() {
        if (typeof jQuery === 'undefined') return;
        var $ = jQuery;

        function getLinkFromEvent(e) {
            var el = e.target;
            while (el && el !== document.body) {
                if (el.id === 'menu-tree') return null;
                if (el.classList && el.classList.contains('menu-link')) {
                    var arrow = el.querySelector('.menu-arrow');
                    if (arrow && arrow.contains(e.target)) return null;
                    return el;
                }
                el = el.parentElement;
            }
            if (window.innerWidth > 991) return null;
            var t = (e.touches && e.touches[0]) || (e.changedTouches && e.changedTouches[0]);
            var x = e.clientX != null ? e.clientX : (t ? t.clientX : 0);
            var y = e.clientY != null ? e.clientY : (t ? t.clientY : 0);
            var sidebar = document.querySelector('.main-sidebar');
            if (!sidebar || (!x && !y)) return null;
            var rect = sidebar.getBoundingClientRect();
            if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) return null;
            var under = document.elementFromPoint(x, y);
            var link = null;
            if (under && sidebar.contains(under)) {
                link = under.classList && under.classList.contains('menu-link') ? under : (under.closest && under.closest('.menu-link'));
            }
            if (!link) {
                var wrapper = document.querySelector('.wrapper');
                var saved = [];
                if (wrapper && wrapper.children) {
                    for (var i = 0; i < wrapper.children.length; i++) {
                        var child = wrapper.children[i];
                        if (child && child !== sidebar) {
                            saved.push({ el: child, val: child.style.pointerEvents });
                            child.style.pointerEvents = 'none';
                        }
                    }
                }
                under = document.elementFromPoint(x, y);
                saved.forEach(function (o) { o.el.style.pointerEvents = o.val || ''; });
                if (under && sidebar.contains(under)) {
                    link = under.classList && under.classList.contains('menu-link') ? under : (under.closest && under.closest('.menu-link'));
                }
            }
            return link || null;
        }

        function doNavigate(link) {
            var url = (link.getAttribute('data-url') || link.getAttribute('href') || '').trim();
            if (!url || url === '#' || /^javascript:/i.test(url)) return;
            if (url.indexOf('/admin') !== 0 && url.charAt(0) === '/') url = '/admin' + url;
            if (window.innerWidth <= 991) {
                window.location.href = url;
                return;
            }
            var id = link.getAttribute('data-addtabs') || ('tab_' + Math.random().toString(36).slice(2, 8));
            var title = link.getAttribute('data-title') || (link.querySelector('.menu-text') && link.querySelector('.menu-text').textContent) || '';
            var tmp = document.createElement('a');
            tmp.href = '#';
            tmp.style.display = 'none';
            tmp.setAttribute('addtabs', id);
            tmp.setAttribute('url', url);
            tmp.setAttribute('title', title);
            document.body.appendChild(tmp);
            tmp.click();
            document.body.removeChild(tmp);
        }

        var pendingMenuLink = null;

        function handleMenuClick(e) {
            var link = getLinkFromEvent(e);
            if (!link) return;
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            // 一级父菜单：只展开/收起，不跳转
            var item = link.closest && link.closest('.menu-item.has-children');
            if (item) {
                item.classList.toggle('open');
                return;
            }
            doNavigate(link);
        }

        function handleTouchStart(e) {
            if (window.innerWidth > 991) return;
            var link = getLinkFromEvent(e);
            if (link) {
                pendingMenuLink = link;
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
        }

        function handleTouchEnd(e) {
            if (window.innerWidth > 991) return;
            if (pendingMenuLink) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                var item = pendingMenuLink.closest && pendingMenuLink.closest('.menu-item.has-children');
                if (item) {
                    item.classList.toggle('open');
                } else {
                    doNavigate(pendingMenuLink);
                }
                pendingMenuLink = null;
            }
        }

        // Chrome/Android 可能默认把 document 上的 touch* 当 passive，导致 preventDefault 无效
        // 必须显式声明 { passive: false } 才能阻止侧栏被遮罩关闭
        document.addEventListener('touchstart', handleTouchStart, { capture: true, passive: false });
        document.addEventListener('touchend', handleTouchEnd, { capture: true, passive: false });
        document.addEventListener('touchcancel', function () { pendingMenuLink = null; }, { capture: true, passive: true });
        document.addEventListener('click', handleMenuClick, true);
        $(document).on('click', '#menu-tree .menu-item.has-children .menu-arrow', function (e) {
            e.stopPropagation();
            e.preventDefault();
            $(this).closest('.menu-item').toggleClass('open');
        });
    }

    // 立即绑定触摸/点击（不等到 DOMContentLoaded），确保比 AdminLTE 更早收到事件
    bindMenuClick();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            loadMenu();
            loadPageJs();
        });
    } else {
        loadMenu();
        loadPageJs();
    }
})();
