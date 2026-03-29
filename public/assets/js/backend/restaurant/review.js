(function () {
    var Controller = {
        index: function () {
            var table = $('#table');
            if (!table.length) return;
            table.bootstrapTable({
                url: Config.moduleurl + '/restaurant/review/index',
                method: 'get',
                sidePagination: 'server',
                pagination: true,
                pageSize: 20,
                sortName: 'review_time',
                sortOrder: 'desc',
                responseHandler: function (res) { var d = res && res.data ? res.data : {}; return { total: d.total || 0, rows: d.list || [] }; },
                columns: [
                    { field: 'id', title: 'ID', width: 70 },
                    { field: 'store_name', title: '门店', width: 120 },
                    { field: 'platform', title: '平台', width: 90 },
                    { field: 'rating', title: '评分', width: 70 },
                    { field: 'content', title: '内容', align: 'left', formatter: function (v) { return '<div style="max-width:520px;white-space:normal">' + (v || '') + '</div>'; } },
                    { field: 'keywords', title: '关键词', width: 140 },
                    { field: 'reply_status', title: '回复', width: 80, formatter: function (v) { return v == 1 ? '<span class="badge badge-success">已回写</span>' : '<span class="badge badge-secondary">未回写</span>'; } },
                    { field: 'suggest_reply', title: '建议回复', align: 'left', formatter: function (v) { return '<div style="max-width:420px;white-space:normal">' + (v || '') + '</div>'; } },
                    { field: 'review_time', title: '时间', width: 160, formatter: function (v) { if (!v) return '-'; return new Date(v * 1000).toLocaleString(); } }
                ]
            });
            $.getJSON(Config.moduleurl + '/restaurant/review/stats', { days: 7 }, function (r) {
                if (!r || r.code != 1) return;
                var d = r.data || {};
                var kw = d.bad_keyword_top || [];
                var cat = d.bad_category_top || [];
                var parts = [];
                for (var i = 0; i < kw.length; i++) parts.push(kw[i].keyword + '(' + kw[i].count + ')');
                var cparts = [];
                for (var i = 0; i < cat.length; i++) cparts.push(cat[i].category + '(' + cat[i].count + ')');
                var html = '近' + (d.days || 7) + '天：评价 ' + (d.total_reviews || 0) + ' 条，差评 ' + (d.bad_reviews || 0) + ' 条，告警 ' + (d.alerts || 0) + ' 条';
                if (parts.length) html += '；差评关键词TOP：' + parts.join(' / ');
                if (cparts.length) html += '；问题分类TOP：' + cparts.join(' / ');
                $('#review-stats').html(html).show();
            });
            $('#toolbar .btn-refresh').off('click').on('click', function () { table.bootstrapTable('refresh'); });
            $('#btn-sync').off('click').on('click', function () {
                $.post(Config.moduleurl + '/restaurant/review/sync', { since: $('#since').val(), until: $('#until').val() }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已同步' : '失败'));
                    if (r.code == 1) table.bootstrapTable('refresh');
                }, 'json');
            });
            $('#btn-auto-reply').off('click').on('click', function () {
                $.post(Config.moduleurl + '/restaurant/review/autoReply', { limit: 50 }, function (r) {
                    alert(r.msg || (r.code == 1 ? '已回写' : '失败'));
                    if (r.code == 1) table.bootstrapTable('refresh');
                }, 'json');
            });
        }
    };
    window.__backendController = Controller;
})();
