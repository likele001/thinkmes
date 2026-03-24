(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/mes/report/index';
    var editUrl = base + '/mes/report/edit';
    var delUrl = base + '/mes/report/del';
    var auditPageUrl = base + '/mes/report/audit_page';

    function statusFmt(v) {
        var statusMap = {0: '待审核', 1: '已通过', 2: '已拒绝'};
        return statusMap[v] || '未知';
    }

    function operFmt(v, row) {
        var html = '<a class="btn btn-xs btn-primary" href="' + editUrl + '?ids=' + v + '">编辑</a> ';
        if (row.status == 0) {
            html += '<a class="btn btn-xs btn-success" href="' + auditPageUrl + '?ids=' + v + '">审核</a> ';
        }
        html += '<button class="btn btn-xs btn-danger" data-id="' + v + '" type="button">删除</button>';
        return html;
    }

    function imageFmt(v, row) {
        var cover = row.image_cover || '';
        var imgCount = row.image_count || 0;
        var videoCount = row.video_count || 0;
        if (!cover && !videoCount) {
            return '-';
        }
        var html = '';
        if (cover) {
            html += '<a href="' + cover + '" target="_blank"><img src="' + cover + '" style="height:40px;border-radius:3px;"></a>';
            if (imgCount > 1) {
                html += ' <span class="badge bg-secondary">图+' + (imgCount - 1) + '</span>';
            }
        }
        if (videoCount > 0) {
            html += ' <span class="badge bg-info">视+' + videoCount + '</span>';
        }
        return html;
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            if (typeof $table.bootstrapTable !== 'function' || $table.data('bootstrap.table')) {
                return;
            }
            $table.bootstrapTable({
                url: indexUrl,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                responseHandler: function (res) {
                    var data = res.data || {};
                    return { total: data.total || 0, rows: data.list || [] };
                },
                columns: [
                    {field: 'state', checkbox: true, width: 40},
                    {field: 'id', title: 'ID', width: 80, sortable: true},
                    {field: 'order_no', title: '订单号', align: 'left'},
                    {field: 'product_name', title: '产品', align: 'left'},
                    {field: 'model_name', title: '型号', align: 'left'},
                    {field: 'item_nos_text', title: '编号', align: 'left'},
                    {field: 'allocation.process.name', title: '工序', align: 'left'},
                    {field: 'work_type', title: '工作类型', width: 100, formatter: function(value) {
                        return value == 'piece' ? '<span class="badge badge-primary">计件</span>' : '<span class="badge badge-info">计时</span>';
                    }},
                    {field: 'quantity', title: '数量', width: 100, align: 'right', formatter: function(value, row) {
                        return row.work_type == 'piece' ? value : '-';
                    }},
                    {field: 'work_hours', title: '工时', width: 100, align: 'right', formatter: function(value, row) {
                        return row.work_type == 'time' ? parseFloat(value).toFixed(2) : '-';
                    }},
                    {field: 'wage', title: '工资', width: 120, align: 'right', formatter: function(value) {
                        return '<span class="text-danger font-weight-bold">¥' + parseFloat(value || 0).toFixed(2) + '</span>';
                    }},
                    {field: 'status', title: '状态', width: 100, formatter: function(value) {
                        var statusMap = {0: '待审核', 1: '已通过', 2: '已拒绝'};
                        var classMap = {0: 'warning', 1: 'success', 2: 'danger'};
                        return '<span class="badge badge-' + (classMap[value] || 'secondary') + '">' + (statusMap[value] || '未知') + '</span>';
                    }},
                    {field: 'quality_status', title: '质量', width: 100, formatter: function(v, row) {
                        if (row.status === 0 || v === null || v === undefined) return '<span class="badge badge-secondary">未质检</span>';
                        if (v === 1) return '<span class="badge badge-success">合格</span>';
                        if (v === 2) return '<span class="badge badge-danger">不合格</span>';
                        return '<span class="badge badge-secondary">未质检</span>';
                    }},
                    {field: 'create_time', title: '报工时间', width: 180, formatter: function(v) {
                        if (!v) return '';
                        var n = Number(v);
                        return !isNaN(n) ? new Date((n > 1e12 ? n : n * 1000)).toLocaleString('zh-CN') : v;
                    }},
                    {field: 'operate', title: '操作', width: 240, events: {
                        'click .btn-audit-row': function(e, value, row) {
                            location.href = base + '/mes/report/audit_page?ids=' + row.id;
                        },
                        'click .btn-view': function(e, value, row) {
                            location.href = base + '/mes/report/detail?ids=' + row.id;
                        },
                        'click .btn-edit': function(e, value, row) {
                            location.href = base + '/mes/report/edit?ids=' + row.id;
                        },
                        'click .btn-del': function(e, value, row) {
                            if (confirm('确定要删除吗？')) {
                                $.post(delUrl, {ids: row.id}, function(r) {
                                    if (r.code == 1) { $table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); }
                                    else { alert(r.msg || '删除失败'); }
                                }, 'json');
                            }
                        }
                    }, formatter: function(value, row) {
                        var html = '';
                        if (row.status === 0 || row.status === '0') {
                            html += '<a href="javascript:;" class="btn btn-xs btn-warning btn-audit-row">审核</a> ';
                        }
                        html += '<a href="javascript:;" class="btn btn-xs btn-info btn-view">详情</a> ';
                        html += '<a href="' + base + '/mes/report/edit?ids=' + row.id + '" class="btn btn-xs btn-success btn-edit">编辑</a> ';
                        html += '<a href="javascript:;" class="btn btn-xs btn-danger btn-del">删除</a>';
                        return html;
                    }}
                ]
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#toolbar .btn-audit').on('click', '#toolbar .btn-audit', function() {
                var rows = $table.bootstrapTable('getSelections');
                if (!rows.length) { alert('请选择要审核的记录'); return; }
                var ids = rows.map(function(r) { return r.id; }).filter(function(v) { return v !== null && v !== undefined && v !== ''; });
                if (!ids.length) { alert('请选择要审核的记录'); return; }
                location.href = base + '/mes/report/audit_page?ids=' + ids.join(',');
            });
            $(document).off('click', '#toolbar .btn-edit').on('click', '#toolbar .btn-edit', function() {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length != 1) { alert('请选择一条记录'); return; }
                location.href = base + '/mes/report/edit?ids=' + rows[0].id;
            });
            $(document).off('click', '#toolbar .btn-del').on('click', '#toolbar .btn-del', function() {
                var rows = $table.bootstrapTable('getSelections');
                if (!rows.length) { alert('请选择要删除的记录'); return; }
                if (!confirm('确定要删除选中的 ' + rows.length + ' 条记录吗？')) return;
                var ids = rows.map(function(r) { return r.id; });
                $.post(delUrl, {ids: ids.join(',')}, function(r) {
                    if (r.code == 1) { $table.bootstrapTable('refresh'); alert(r.msg || '删除成功'); }
                    else { alert(r.msg || '删除失败'); }
                }, 'json');
            });
            // AI: 语音转报工
            $(document).off('click', '#btn-voice-report').on('click', '#btn-voice-report', function() {
                var url = prompt('请输入音频文件 URL（http(s)://）或网站相对路径（/uploads/...）:');
                if (!url) return;
                fetch(base + '/mes/report/ai/transcribe', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({audio_url: url})})
                    .then(function(r){ return r.json(); }).then(function(j){
                        if (!j || j.code != 0) { alert(j.msg || '识别失败'); return; }
                        var text = j.data && j.data.text ? j.data.text : '';
                        if (!text) { alert('未识别到文字'); return; }
                        if (!confirm('识别结果：\n' + text + '\n\n是否继续解析为报工？')) return;
                        fetch(base + '/mes/report/ai/parse', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({text: text})})
                            .then(function(r){ return r.json(); }).then(function(p){
                                if (!p || p.code != 0) { alert(p.msg || '解析失败'); return; }
                                var data = p.data && p.data.data ? p.data.data : p.data;
                                alert('解析结果：\n' + JSON.stringify(data, null, 2));
                            });
                    });
            });
            // AI: 异常检测
            $(document).off('click', '#btn-ai-anomaly').on('click', '#btn-ai-anomaly', function() {
                var days = prompt('请输入要扫描的天数（默认7天）：', '7');
                days = parseInt(days) || 7;
                if (!confirm('开始 AI 异常检测，可能需要较长时间，确定继续？')) return;
                fetch(base + '/mes/report/ai/anomaly', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({days: days})})
                    .then(function(r){ return r.json(); }).then(function(j){
                        if (!j) { alert('请求失败'); return; }
                        if (j.code != 0) { alert(j.msg || '分析失败'); return; }
                        alert('扫描完成，发现 ' + (j.data && j.data.count ? j.data.count : 0) + ' 条异常记录');
                        $table.bootstrapTable('refresh');
                    });
            });
            // AI: 老板问答
            $(document).off('click', '#btn-boss-qa').on('click', '#btn-boss-qa', function() {
                var q = prompt('请输入问题（例如：今日完成数量是多少？）');
                if (!q) return;
                fetch(base + '/mes/qa/ask', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({question: q})})
                    .then(function(r){ return r.json(); }).then(function(j){
                        if (!j) { alert('请求失败'); return; }
                        if (j.code != 0) { alert(j.msg || 'AI 无法回答'); return; }
                        alert('回答：\n' + (j.data && j.data.answer ? j.data.answer : JSON.stringify(j.data)));
                    });
            });
            $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function() {
                var rows = $table.bootstrapTable('getSelections');
                if (rows.length > 0) {
                    $('.btn-edit, .btn-del, .btn-audit').removeClass('disabled btn-disabled');
                } else {
                    $('.btn-edit, .btn-del, .btn-audit').addClass('disabled btn-disabled');
                }
            });
        },
        audit_page: function () {
            var form = $('#form-audit');
            if (!form.length) {
                return;
            }

            var uploadUrl = base + '/common/upload';
            var imageInput = $('#audit-images-input');
            var videoInput = $('#audit-videos-input');
            var imageHidden = $('#audit-images');
            var videoHidden = $('#audit-videos');
            var imagePreview = $('#audit-images-preview');
            var videoPreview = $('#audit-videos-preview');
            var imageArea = $('#audit-images-area');
            var videoArea = $('#audit-videos-area');

            var auditImages = [];
            var auditVideos = [];

            function syncHidden() {
                if (imageHidden.length) {
                    imageHidden.val(auditImages.length ? JSON.stringify(auditImages) : '');
                }
                if (videoHidden.length) {
                    videoHidden.val(auditVideos.length ? JSON.stringify(auditVideos) : '');
                }
            }

            function uploadFiles(files, type) {
                if (!files || !files.length) {
                    return;
                }
                Array.prototype.forEach.call(files, function (file) {
                    var fd = new FormData();
                    fd.append('file', file);
                    $.ajax({
                        url: uploadUrl,
                        type: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false,
                        success: function (r) {
                            if (!r || r.code !== 1 || !r.data || !r.data.url) {
                                alert((r && r.msg) || '上传失败');
                                return;
                            }
                            var url = r.data.url;
                            if (type === 'image') {
                                auditImages.push(url);
                                if (imagePreview && imagePreview.length) {
                                    var wrap = $('<div class="me-2 mb-2 position-relative" style="width:70px;height:70px;overflow:hidden;border:1px solid #ddd;border-radius:4px;"></div>');
                                    var img = $('<img>').attr('src', url).attr('loading', 'lazy').attr('decoding', 'async').css({ width: '100%', height: '100%', objectFit: 'cover' });
                                    var del = $('<span class="badge bg-danger" style="position:absolute;top:2px;right:2px;cursor:pointer;">×</span>');
                                    del.on('click', function () {
                                        var idx = auditImages.indexOf(url);
                                        if (idx > -1) {
                                            auditImages.splice(idx, 1);
                                        }
                                        wrap.remove();
                                        syncHidden();
                                    });
                                    wrap.append(img).append(del);
                                    imagePreview.append(wrap);
                                }
                            } else {
                                auditVideos.push(url);
                                if (videoPreview && videoPreview.length) {
                                    var vwrap = $('<div class="me-2 mb-2 position-relative d-inline-flex align-items-center" style="width:120px;height:56px;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;"></div>');
                                    var vlabel = $('<span class="small text-muted px-2">视频 ' + (auditVideos.length) + '</span>');
                                    var vdel = $('<span class="badge bg-danger" style="position:absolute;top:2px;right:2px;cursor:pointer;">×</span>');
                                    vdel.on('click', function () {
                                        var idx2 = auditVideos.indexOf(url);
                                        if (idx2 > -1) auditVideos.splice(idx2, 1);
                                        vwrap.remove();
                                        syncHidden();
                                    });
                                    vwrap.append(vlabel).append(vdel);
                                    videoPreview.append(vwrap);
                                }
                            }
                            syncHidden();
                        },
                        error: function () {
                            alert('上传失败');
                        }
                    });
                });
            }

            if (imageArea.length) {
                imageArea.off('.auditImages');
                imageArea.on('click.auditImages', function (e) {
                    if (imageInput.length && e.target === imageInput[0]) {
                        return;
                    }
                    imageInput.trigger('click');
                });
                imageArea.on('dragover.auditImages', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    imageArea.addClass('drag-over');
                });
                imageArea.on('dragleave.auditImages dragend.auditImages', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    imageArea.removeClass('drag-over');
                });
                imageArea.on('drop.auditImages', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    imageArea.removeClass('drag-over');
                    var dt = e.originalEvent.dataTransfer;
                    if (dt && dt.files) {
                        uploadFiles(dt.files, 'image');
                    }
                });
            }

            if (videoArea.length) {
                videoArea.off('.auditVideos');
                videoArea.on('click.auditVideos', function (e) {
                    if (videoInput.length && e.target === videoInput[0]) {
                        return;
                    }
                    videoInput.trigger('click');
                });
                videoArea.on('dragover.auditVideos', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    videoArea.addClass('drag-over');
                });
                videoArea.on('dragleave.auditVideos dragend.auditVideos', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    videoArea.removeClass('drag-over');
                });
                videoArea.on('drop.auditVideos', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    videoArea.removeClass('drag-over');
                    var dt = e.originalEvent.dataTransfer;
                    if (dt && dt.files) {
                        uploadFiles(dt.files, 'video');
                    }
                });
            }

            imageInput.off('change.auditImages').on('change.auditImages', function () {
                uploadFiles(this.files, 'image');
                this.value = '';
            });

            videoInput.off('change.auditVideos').on('change.auditVideos', function () {
                uploadFiles(this.files, 'video');
                this.value = '';
            });

            var url = form.attr('action');
            var listUrl = base + '/mes/report/index';
            form.off('submit.audit').on('submit.audit', function (e) {
                e.preventDefault();
                syncHidden();
                var data = form.serialize();
                if (typeof Fast !== 'undefined' && Fast.api && typeof Fast.api.ajax === 'function') {
                    Fast.api.ajax({
                        url: url,
                        type: 'POST',
                        data: data
                    }, function () {
                        window.location.href = listUrl;
                        return false;
                    });
                } else {
                    $.post(url, data, function (r) {
                        alert(r.msg || (r.code === 1 ? '操作成功' : '操作失败'));
                        if (r.code === 1) {
                            window.location.href = listUrl;
                        }
                    }, 'json');
                }
            });
        }
    };

    var action = (typeof Config !== 'undefined' && Config.actionname) ? Config.actionname : 'index';
    if (Controller[action]) {
        Controller[action]();
    }

    window.__backendController = Controller;
})();
