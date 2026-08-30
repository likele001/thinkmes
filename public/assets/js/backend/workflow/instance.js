/**
 * 工作流-流程实例列表
 */
(function () {
    var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
    var indexUrl = base + '/workflow/instance/index';
    var detailUrl = base + '/workflow/approval/detail';

    function statusLabel(v) {
        var m = {
            0: ['审批中', 'warning'],
            1: ['已通过', 'success'],
            2: ['已拒绝', 'danger'],
            3: ['已撤回', 'secondary'],
            4: ['回写异常', 'dark']
        };
        var t = m[v];
        if (!t) return String(v);
        return '<span class="badge badge-' + t[1] + '">' + t[0] + '</span>';
    }

    function ts(v) {
        if (!v) return '';
        return new Date(v * 1000).toLocaleString('zh-CN');
    }

    var Controller = {
        index: function () {
            var table = $('#table');
            if (!table.length) return;

            table.bootstrapTable({
                url: indexUrl,
                pagination: true,
                sidePagination: 'server',
                pageSize: 20,
                queryParams: function (params) {
                    return {
                        limit: params.limit,
                        offset: params.offset,
                        page: params.page,
                        keyword: $('#keyword').val() || '',
                        module_code: $('#moduleCode').val() || '',
                        status: $('#status').val() || ''
                    };
                },
                responseHandler: function (res) {
                    var data = res.data || {};
                    return {
                        total: data.total || 0,
                        rows: data.list || []
                    };
                },
                columns: [
                    { field: 'id', title: 'ID', width: 70 },
                    { field: 'instance_no', title: '实例编号', width: 160 },
                    { field: 'definition_name', title: '流程名称', width: 160 },
                    { field: 'module_code', title: '模块', width: 130 },
                    { field: 'business_title', title: '业务标题' },
                    { field: 'business_id', title: '业务ID', width: 90 },
                    {
                        field: 'status',
                        title: '状态',
                        width: 100,
                        formatter: function (v) {
                            return statusLabel(v);
                        }
                    },
                    { field: 'initiator_name', title: '发起人', width: 100 },
                    { field: 'start_time', title: '发起时间', width: 170, formatter: function (v) { return ts(v); } },
                    { field: 'end_time', title: '结束时间', width: 170, formatter: function (v) { return ts(v); } },
                    {
                        field: 'operate',
                        title: '操作',
                        width: 100,
                        formatter: function (v, row) {
                            return '<a href="' + detailUrl + '?id=' + row.id + '" class="btn btn-xs btn-primary">详情</a>';
                        }
                    }
                ]
            });

            $('.btn-refresh').on('click', function () {
                table.bootstrapTable('refresh');
            });
            $('.btn-search').on('click', function () {
                table.bootstrapTable('refresh', { pageNumber: 1 });
            });
            $('.btn-reset').on('click', function () {
                $('#keyword').val('');
                $('#moduleCode').val('');
                $('#status').val('');
                table.bootstrapTable('refresh', { pageNumber: 1 });
            });
        }
    };

    window.__backendController = Controller;
})();
