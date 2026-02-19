(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/attachment/index';
    var delUrl = base + '/attachment/del';
    var uploadUrl = base + '/common/upload';

    function operFmt(v, row) {
        var url = row.url || '';
        var link = url ? '<a class="btn btn-xs btn-info" href="' + url + '" target="_blank">查看</a> ' : '';
        return link + '<button class="btn btn-xs btn-danger" data-id="' + v + '" type="button">删除</button>';
    }

    function formatSize(size) {
        if (!size || size <= 0) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB'];
        var idx = 0;
        while (size >= 1024 && idx < units.length - 1) {
            size = size / 1024;
            idx++;
        }
        return size.toFixed(2) + ' ' + units[idx];
    }

    function initUpload() {
        var $area = $('#fileUploadArea');
        var $input = $('#fileInput');
        var $preview = $('#filePreview');
        var $img = $('#filePreviewImg');
        var $video = $('#filePreviewVideo');
        var $name = $('#fileName');
        var $size = $('#fileSize');
        var $progressWrap = $('#fileProgress');
        var $progressBar = $('#fileProgressBar');
        var $actions = $('#fileActions');
        var $uploadBtn = $('#fileUploadBtn');
        var $resetBtn = $('#fileResetBtn');
        var currentFile = null;

        if (!$area.length) {
            return;
        }

        function reset() {
            currentFile = null;
            $input.val('');
            $preview.hide();
            $img.addClass('d-none').attr('src', '');
            $video.addClass('d-none').attr('src', '');
            $name.text('');
            $size.text('');
            $progressWrap.hide();
            $progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
            $actions.hide();
        }

        function showPreview(file) {
            $name.text(file.name || '');
            $size.text(formatSize(file.size || 0));
            var type = file.type || '';
            var isImage = /^image\//.test(type);
            var isVideo = /^video\//.test(type);
            if (isImage || isVideo) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    if (isImage) {
                        $img.removeClass('d-none').attr('src', e.target.result);
                        $video.addClass('d-none').attr('src', '');
                    } else if (isVideo) {
                        $video.removeClass('d-none').attr('src', e.target.result);
                        $img.addClass('d-none').attr('src', '');
                    }
                };
                reader.readAsDataURL(file);
            } else {
                $img.addClass('d-none').attr('src', '');
                $video.addClass('d-none').attr('src', '');
            }
            $preview.show();
            $actions.show();
        }

        function handleFiles(files) {
            if (!files || !files.length) {
                return;
            }
            var file = files[0];
            currentFile = file;
            showPreview(file);
        }

        $area.on('click', function () {
            $input.trigger('click');
        });

        $input.on('change', function (e) {
            handleFiles(e.target.files);
        });

        $area.on('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $area.addClass('drag-over');
        });
        $area.on('dragleave dragend', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $area.removeClass('drag-over');
        });
        $area.on('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $area.removeClass('drag-over');
            var dt = e.originalEvent.dataTransfer;
            if (dt && dt.files) {
                handleFiles(dt.files);
            }
        });

        $uploadBtn.on('click', function () {
            if (!currentFile) {
                alert('请先选择文件');
                return;
            }
            var formData = new FormData();
            formData.append('file', currentFile);
            $progressWrap.show();
            var xhr = new XMLHttpRequest();
            xhr.open('POST', uploadUrl, true);
            xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) {
                    var percent = Math.round(e.loaded * 100 / e.total);
                    $progressBar.css('width', percent + '%').attr('aria-valuenow', percent).text(percent + '%');
                }
            };
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    try {
                        var res = JSON.parse(xhr.responseText || '{}');
                        alert(res.msg || (res.code === 1 ? '上传成功' : '上传失败'));
                        if (res.code === 1) {
                            $('#table').bootstrapTable('refresh');
                            reset();
                        }
                    } catch (e) {
                        alert('上传失败');
                    }
                }
            };
            xhr.send(formData);
        });

        $resetBtn.on('click', function () {
            reset();
        });

        reset();
    }

    var Controller = {
        index: function () {
            var $table = $('#table');
            $table.bootstrapTable({
                url: indexUrl,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                pageList: [10, 20, 50],
                columns: [
                    { field: 'id', title: 'ID', width: 60 },
                    { field: 'url', title: '地址', formatter: function (v) { return v ? '<a href="' + v + '" target="_blank">' + (v.length > 50 ? v.substring(0, 50) + '...' : v) + '</a>' : '-'; } },
                    { field: 'size', title: '大小(字节)' },
                    { field: 'mime_type', title: '类型' },
                    { field: 'storage', title: '存储' },
                    { field: 'create_time', title: '上传时间' },
                    { field: 'id', title: '操作', formatter: operFmt }
                ],
                responseHandler: function (res) {
                    return { total: (res.data && res.data.total) ? res.data.total : 0, rows: (res.data && res.data.list) ? res.data.list : [] };
                }
            });
            $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function () { $table.bootstrapTable('refresh'); });
            $(document).off('click', '#table button.btn-danger').on('click', '#table button.btn-danger', function () {
                var id = $(this).data('id');
                if (!id || !confirm('确定删除该附件记录？')) return;
                $.post(delUrl, { id: id }, function (r) {
                    alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
                    if (r.code === 1) $table.bootstrapTable('refresh');
                }, 'json');
            });
            initUpload();
        }
    };
    window.__backendController = Controller;
})();
