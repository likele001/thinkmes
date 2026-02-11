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

    function renderMenu(items, ul) {
        if (!ul) ul = $('<ul class="nav nav-treeview"></ul>');
        (items || []).forEach(function (it) {
            var hasChild = it.children && it.children.length > 0;
            var href = (it.url && it.url !== '#') ? it.url : (it.name ? ('/' + it.name.replace(/\./g, '/')) : '#');
            var isIndexLink = href && (href.indexOf('/admin/index/index') !== -1 || href === '/admin/' || href === '/admin');
            var iconClass = mapIconToFontAwesome(it.icon || '');
            var title = (it.title || it.name || '').trim();
            if (!title) return;

            var li, a;
            if (hasChild) {
                li = $('<li class="nav-item has-treeview"></li>');
                a = $('<a href="#" class="nav-link"></a>').html(
                    '<i class="nav-icon ' + iconClass + '"></i>' +
                    '<p>' + title + '<i class="right fas fa-angle-left"></i></p>'
                );
                li.append(a);
                var sub = $('<ul class="nav nav-treeview"></ul>');
                renderMenu(it.children, sub);
                li.append(sub);
            } else {
                li = $('<li class="nav-item"></li>');
                var isSub = ul.hasClass('nav-treeview');
                var icon = isSub ? 'far fa-circle nav-icon' : 'nav-icon ' + iconClass;
                var addtabsId = (it.id ? ('m' + it.id) : ((it.name || '').replace(/[\/\.]/g, '_').replace(/^_+|_+$/g, '') || ('tab_' + Math.random().toString(36).slice(2, 8))));
                a = $('<a class="nav-link" href="#"></a>').attr('addtabs', addtabsId).attr('url', href).attr('title', title).html(
                    '<i class="' + icon + '"></i><p>' + title + '</p>'
                );
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
            $menu.html('<li class="nav-item"><a href="#" class="nav-link" addtabs="index" url="' + indexUrl + '" title="首页"><i class="nav-icon fas fa-tachometer-alt"></i><p>首页</p></a></li>');
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
                var $homeItem = $('<li class="nav-item"><a href="#" class="nav-link" addtabs="index" url="' + indexUrl + '" title="首页"><i class="nav-icon fas fa-tachometer-alt"></i><p>首页</p></a></li>');
                $menu.append($homeItem);
                var $menuItems = renderMenu(filteredData);
                $menuItems.children().each(function() {
                    $menu.append(this);
                });
                // AdminLTE 3 Treeview 会在 DOM 更新后自动初始化（通过 data-widget="treeview"）
                if (typeof $ !== 'undefined' && $.fn.tree) {
                    $menu.tree();
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
