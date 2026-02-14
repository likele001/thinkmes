(function () {
    var $ = jQuery;
    var table = $("#table");

    window.__backendController = {
        index: function () {
            if (typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) {
                return;
            }
            table.bootstrapTable({
                url: '/admin/mes/after_sales/index',
                toolbar: '#toolbar',
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: 'ID', sortable: true},
                        {field: 'after_sales_no', title: '售后单号', sortable: true},
                        {field: 'customer.name', title: '客户'},
                        {field: 'order.order_no', title: '关联订单'},
                        {field: 'trace_code', title: '溯源码'},
                        {field: 'type', title: '售后类型', formatter: function(value) {
                            var types = {1: '退货', 2: '换货', 3: '维修'};
                            return types[value] || '未知';
                        }},
                        {field: 'status', title: '状态', formatter: function(value) {
                            var status = {0: '待处理', 1: '处理中', 2: '已完成', 3: '已取消'};
                            var classes = {0: 'warning', 1: 'primary', 2: 'success', 3: 'default'};
                            return '<span class="badge badge-' + classes[value] + '">' + status[value] + '</span>';
                        }},
                        {field: 'create_time', title: '创建时间', formatter: function(value) {
                            return value ? new Date(value * 1000).toLocaleString() : '-';
                        }},
                        {field: 'operate', title: '操作', events: {
                            'click .btn-edit': function (e, value, row, index) {
                                Fast.api.addtabs('/admin/mes/after_sales/edit?ids=' + row.id, '处理售后');
                            },
                            'click .btn-del': function (e, value, row, index) {
                                Fast.api.ajax({
                                    url: '/admin/mes/after_sales/del',
                                    data: {ids: row.id}
                                }, function() {
                                    table.bootstrapTable('refresh');
                                });
                            }
                        }, formatter: function() {
                            return '<a href="javascript:;" class="btn btn-xs btn-success btn-edit"><i class="fa fa-pencil"></i></a> ' +
                                   '<a href="javascript:;" class="btn btn-xs btn-danger btn-del"><i class="fa fa-trash"></i></a>';
                        }}
                    ]
                ]
            });

            // 刷新
            $(".btn-refresh").on("click", function () {
                table.bootstrapTable('refresh');
            });
        },
        add: function () {
            this.form();
        },
        edit: function () {
            this.form();
        },
        form: function () {
            // 表单提交逻辑由后端处理，前端只需配合 AdminLTE/FastAdmin 的基础功能
        }
    };
})();
