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
            'tachometer-alt': 'fas fa-tachometer-alt',
            'cube': 'fas fa-cube',
            'cubes': 'fas fa-cubes',
            'clipboard': 'fas fa-clipboard',
            'sitemap': 'fas fa-sitemap',
            'puzzle-piece': 'fas fa-puzzle-piece',
            'th-large': 'fas fa-th-large',
            'building': 'fas fa-building',
            'box': 'fas fa-box',
            'list': 'fas fa-list',
            'receipt': 'fas fa-receipt',
            'user-check': 'fas fa-user-check',
            'user-cog': 'fas fa-user-cog',
            'shopping-cart': 'fas fa-shopping-cart',
            'user': 'fas fa-user',
            'users': 'fas fa-users',
            'cog': 'fas fa-cog',
            'gear': 'fas fa-cog',
            'wrench': 'fas fa-wrench',
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

    // 去掉错误形态 /admin/随机路径/ 或 域名/admin/随机路径/ 改为 /随机路径/ 或 域名/随机路径/（路径式入口不应带 admin）
    // 仅匹配 8 位以上随机串（如 joxushcckurt），避免把 controller 名（如 attachment）当随机路径
    function stripAdminEntryPrefix(u) {
        if (typeof u !== 'string' || u === '') return u;
        // 相对路径：/admin/joxushcckurt/xxx -> /joxushcckurt/xxx（随机路径与后续之间补回斜杠）
        var m = u.match(/^(\/admin)\/([a-z0-9]{8,})\/(.+)$/);
        if (m) return '/' + m[2] + '/' + m[3];
        // 完整 URL：http(s)://host/admin/joxushcckurt/xxx -> http(s)://host/joxushcckurt/xxx
        m = u.match(/^(https?:\/\/[^\/]+)\/admin\/([a-z0-9]{8,})\/(.+)$/);
        if (m) return m[1] + '/' + m[2] + '/' + m[3];
        return u;
    }

    function normalizeUrl(raw, name) {
        var url = raw || '';
        url = stripAdminEntryPrefix(url);
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
            // 避免菜单返回 admin/config 时与 base(/admin) 拼成 /admin/admin/config
            if (url.indexOf('admin/') === 0 && base && (base.indexOf('/admin') !== -1 || base === '/admin')) {
                url = url.substring(6);
            }
            url = (base ? base.replace(/\/$/, '') : '') + '/' + url.replace(/^\//, '');
        }
        // 路径式入口时 URL 已为 /随机路径/xxx，不再加 /admin；否则确保带 /admin 前缀
        var useAdminPrefix = !base || base === '' || (base.indexOf('/admin') !== -1 && (base.endsWith('/admin') || base.endsWith('/admin/')));
        if (url && url.charAt(0) === '/' && url.indexOf('/admin') !== 0 && !/^\/\//.test(url) && useAdminPrefix) {
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

    var homeTitle = (typeof Config !== 'undefined' && Config.lang && Config.lang.home) ? Config.lang.home : '首页';
    function fallbackMenu() {
        var $menu = $('#menu-tree');
        if ($menu.length) {
            var indexUrl = (typeof Config !== 'undefined' && Config.moduleurl) ? (Config.moduleurl + '/index/index') : '/admin/index/index';
            $menu.html(
                '<li class="menu-item">' +
                '<a href="' + indexUrl + '" class="menu-link" data-url="' + indexUrl + '" data-addtabs="index" data-title="' + homeTitle + '">' +
                '<span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span><span class="menu-text">' + homeTitle + '</span></a></li>'
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
                // 从接口数据里去掉「首页」，避免与下面手写的首页重复（路径式入口时 href 可能不含 /admin/，故用 name 判断）
                function filterIndexMenu(items) {
                    return items.filter(function(item) {
                        var href = (item.url && item.url !== '#') ? item.url : (item.name ? ('/' + item.name.replace(/\./g, '/')) : '#');
                        var isIndex = (item.name && (item.name === 'admin/index/index' || item.name === 'admin/index')) ||
                            (href && (href.indexOf('/admin/index/index') !== -1 || href === '/admin/' || href === '/admin' || href.indexOf('/admin/index') === 0 || href.indexOf('/index/index') !== -1));
                        if (item.children && item.children.length > 0) {
                            item.children = filterIndexMenu(item.children);
                        }
                        return !isIndex;
                    });
                }
                $menu.empty();
                var filteredData = filterIndexMenu(res.data);
                var indexUrl = (typeof Config !== 'undefined' && Config.moduleurl) ? (Config.moduleurl + '/index/index') : '/admin/index/index';
                var homeText = (typeof Config !== 'undefined' && Config.lang && Config.lang.home) ? Config.lang.home : '首页';
                var $homeItem = $(
                    '<li class="menu-item">' +
                    '<a href="' + indexUrl + '" class="menu-link" data-url="' + indexUrl + '" data-addtabs="index" data-title="' + homeText + '">' +
                    '<span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span><span class="menu-text">' + homeText + '</span></a></li>'
                );
                $menu.append($homeItem);
                var $menuItems = renderMenu(filteredData);
                $menuItems.children().each(function() {
                    $menu.append(this);
                });
                if (typeof window.syncMenuActiveByTab === 'function') {
                    setTimeout(window.syncMenuActiveByTab, 0);
                }
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
            url = stripAdminEntryPrefix(url);
            var baseUrl = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
            var isPathEntry = baseUrl && baseUrl.indexOf('/admin') === -1;
            if (!isPathEntry && url.charAt(0) === '/' && url.indexOf('/admin') !== 0) url = '/admin' + url;
            if (window.innerWidth <= 991) {
                window.location.href = url;
                return;
            }
            var id = link.getAttribute('data-addtabs') || ('tab_' + Math.random().toString(36).slice(2, 8));
            var title = link.getAttribute('data-title') || (link.querySelector('.menu-text') && link.querySelector('.menu-text').textContent) || '';
            // FastAdmin addtabs 依赖页面中存在 a[addtabs] 元素，供标签点击/关闭时再次触发
            // 这里使用一个隐藏容器持久化保存这些链接，避免“后开的标签把前面的标签点不动/关闭后空白”
            function getAddtabsStore() {
                var store = document.getElementById('__addtabs_link_store__');
                if (!store) {
                    store = document.createElement('div');
                    store.id = '__addtabs_link_store__';
                    store.style.display = 'none';
                    document.body.appendChild(store);
                }
                return store;
            }
            function upsertAddtabsLink(tabId, tabUrl, tabTitle) {
                var store = getAddtabsStore();
                var a = null;
                for (var i = 0; i < store.children.length; i++) {
                    var ch = store.children[i];
                    if (ch && ch.getAttribute && ch.getAttribute('addtabs') === tabId) {
                        a = ch;
                        break;
                    }
                }
                if (!a) {
                    a = document.createElement('a');
                    a.href = '#';
                    store.appendChild(a);
                }
                a.setAttribute('addtabs', tabId);
                a.setAttribute('url', tabUrl);
                a.setAttribute('title', tabTitle || '');
                a.textContent = tabTitle || tabId;
                return a;
            }
            var a = upsertAddtabsLink(id, url, title);
            a.click();
        }

        var pendingMenuLink = null;
        var touchStartY = 0;
        var touchStartX = 0;
        var tapThreshold = 12;

        function handleMenuClick(e) {
            // 检查是否点击了箭头
            var arrow = e.target.closest && e.target.closest('.menu-arrow');
            if (arrow) {
                var menuItem = arrow.closest && arrow.closest('.menu-item');
                if (menuItem && menuItem.classList.contains('has-children')) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    menuItem.classList.toggle('open');
                    return;
                }
            }
            
            var link = getLinkFromEvent(e);
            if (!link) return;
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            // 只有点击「一级父项」那一行（带箭头的那行）才展开/收起；点击二级、叶子项只跳转，不收起上级
            var parentLi = link.closest && link.closest('.menu-item');
            if (parentLi && parentLi.classList.contains('has-children') && !link.closest('.menu-children')) {
                parentLi.classList.toggle('open');
                return;
            }
            doNavigate(link);
        }

        // touchstart 不 preventDefault，否则会阻止侧栏上下滑动
        function handleTouchStart(e) {
            if (window.innerWidth > 991) return;
            var link = getLinkFromEvent(e);
            var t = (e.touches && e.touches[0]);
            if (t) {
                touchStartX = t.clientX;
                touchStartY = t.clientY;
            }
            if (link) pendingMenuLink = link;
        }

        function handleTouchMove(e) {
            if (window.innerWidth > 991 || !pendingMenuLink) return;
            var t = (e.touches && e.touches[0]);
            if (!t) return;
            if (Math.abs(t.clientY - touchStartY) > tapThreshold || Math.abs(t.clientX - touchStartX) > tapThreshold) {
                pendingMenuLink = null;
            }
        }

        function handleTouchEnd(e) {
            if (window.innerWidth > 991) return;
            if (pendingMenuLink) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                var parentLi = pendingMenuLink.closest && pendingMenuLink.closest('.menu-item');
                if (parentLi && parentLi.classList.contains('has-children') && !pendingMenuLink.closest('.menu-children')) {
                    parentLi.classList.toggle('open');
                } else {
                    doNavigate(pendingMenuLink);
                }
            }
            pendingMenuLink = null;
        }

        document.addEventListener('touchstart', handleTouchStart, { capture: true, passive: true });
        document.addEventListener('touchmove', handleTouchMove, { capture: true, passive: true });
        document.addEventListener('touchend', handleTouchEnd, { capture: true, passive: false });
        document.addEventListener('touchcancel', function () { pendingMenuLink = null; }, { capture: true, passive: true });
        document.addEventListener('click', handleMenuClick, true);
        // jQuery 事件处理器作为备用（处理动态添加的菜单项）
        $(document).on('click', '#menu-tree .menu-item.has-children .menu-arrow', function (e) {
            e.stopPropagation();
            e.preventDefault();
            $(this).closest('.menu-item').toggleClass('open');
        });
        // 也处理点击菜单链接本身的情况
        $(document).on('click', '#menu-tree .menu-item.has-children > .menu-link', function (e) {
            var $menuItem = $(this).closest('.menu-item');
            if ($menuItem.hasClass('has-children') && !$(this).closest('.menu-children').length) {
                // 如果点击的不是箭头，也允许切换
                if (!$(e.target).closest('.menu-arrow').length) {
                    e.preventDefault();
                    e.stopPropagation();
                    $menuItem.toggleClass('open');
                }
            }
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
