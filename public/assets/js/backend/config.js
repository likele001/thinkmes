/**
 * 系统配置：分组 tabs、按 group 拉取配置、表单保存
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var groupNames = { base: '基本', upload: '上传', safe: '安全', lang: '多语言', cache: '缓存' };

    var Controller = {
        index: function () {
            var $tabs = $('#config-tabs');
            var $form = $('#config-form');
            var $btnSave = $('#config-save');

            function loadGroups() {
                $.get(base + '/config/group', function (res) {
                    if (res.code !== 1 || !res.data || !res.data.length) return;
                    $tabs.empty();
                    res.data.forEach(function (g, i) {
                        var title = groupNames[g] || g;
                        var active = i === 0 ? ' active' : '';
                        $tabs.append('<li class="nav-item"><a class="nav-link' + active + '" href="javascript:;" data-group="' + g + '">' + title + '</a></li>');
                    });
                    var first = res.data[0];
                    if (first) loadGroup(first);
                }, 'json');
            }

            function loadGroup(group) {
                $form.empty().append('<p class="text-muted">加载中...</p>');
                $.get(base + '/config/index', { group: group }, function (res) {
                    $form.empty();
                    if (res.code !== 1 || !res.data || !res.data.length) {
                        $form.append('<p class="text-muted">暂无配置项，可执行 database/seed_config.sql 初始化。</p>');
                        return;
                    }
                    res.data.forEach(function (row) {
                        var name = row.name || '';
                        var title = row.title || name; // 使用 title，如果没有则回退到 name
                        var value = (row.value != null && row.value !== undefined) ? String(row.value) : '';
                        var id = 'config_' + name.replace(/[^a-zA-Z0-9_]/g, '_');
                        $form.append(
                            '<div class="form-group row mb-2">' +
                            '<label class="col-sm-2 col-form-label" for="' + id + '">' + title + '</label>' +
                            '<div class="col-sm-6"><input type="text" class="form-control" id="' + id + '" name="' + name + '" value="' + value.replace(/"/g, '&quot;') + '" data-group="' + (row.group || group) + '"></div>' +
                            '</div>'
                        );
                    });
                    $form.append('<input type="hidden" name="group" value="' + group + '">');
                }, 'json');
            }

            $(document).off('click', '#config-tabs .nav-link').on('click', '#config-tabs .nav-link', function () {
                var g = $(this).data('group');
                if (!g) return;
                $('#config-tabs .nav-link').removeClass('active');
                $(this).addClass('active');
                loadGroup(g);
            });

            $btnSave.on('click', function () {
                var group = $form.find('input[name="group"]').val() || 'base';
                var list = [];
                $form.find('input.form-control').each(function () {
                    var $el = $(this);
                    if ($el.attr('name') && $el.attr('name') !== 'group') {
                        list.push({ name: $el.attr('name'), value: $el.val(), group: $el.data('group') || group });
                    }
                });
                if (!list.length) {
                    if (typeof layer !== 'undefined') layer.msg('无配置项'); else alert('无配置项');
                    return;
                }
                $.post(base + '/config/save', { list: list }, function (res) {
                    if (res.code === 1) {
                        if (typeof layer !== 'undefined') layer.msg('保存成功'); else alert('保存成功');
                    } else {
                        if (typeof layer !== 'undefined') layer.msg(res.msg || '保存失败'); else alert(res.msg || '保存失败');
                    }
                }, 'json');
            });

            loadGroups();
        }
    };
    window.__backendController = Controller;
})();
