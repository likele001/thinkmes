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
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'work_type', title: '工作类型', width: 100 },
                    { field: 'quantity', title: '数量', width: 100 },
                    { field: 'work_hours', title: '工时', width: 100 },
                    { field: 'image_cover', title: '图片', width: 140, formatter: imageFmt },
                    { field: 'wage', title: '工资', width: 100 },
                    { field: 'status', title: '状态', width: 100, formatter: statusFmt },
                    { field: 'create_time', title: '创建时间', width: 150 },
                    { field: 'id', title: '操作', width: 200, formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除该报工记录？')) return;
                $.post(delUrl, { ids: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
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
