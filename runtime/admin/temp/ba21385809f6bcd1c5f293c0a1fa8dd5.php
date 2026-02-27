<?php /*a:1:{s:55:"/www/wwwroot/thinkmes/app/admin/view/mes/bom/index.html";i:1771667983;}*/ ?>
<div class="card panel-intro">
    <div class="card-header">
        <div class="panel-lead"><em>BOM管理</em> 管理产品物料清单，支持多层级BOM结构</div>
    </div>
    <div class="card-body">
        <div id="toolbar" class="toolbar mb-2">
            <a href="javascript:;" class="btn btn-primary btn-refresh" title="刷新"><i class="fas fa-sync-alt"></i> 刷新</a>
            <a href="<?php echo htmlentities((string) $config['moduleurl']); ?>/mes/bom/add" class="btn btn-success btn-add" title="添加"><i class="fas fa-plus"></i> 添加BOM</a>
            <a href="javascript:;" class="btn btn-success btn-edit btn-disabled disabled" title="编辑"><i class="fas fa-edit"></i> 编辑</a>
            <a href="javascript:;" class="btn btn-danger btn-del btn-disabled disabled" title="删除"><i class="fas fa-trash-alt"></i> 删除</a>
        </div>
        <table id="table" class="table table-striped table-bordered table-hover" width="100%"></table>
    </div>
</div>

<script>
(function () {
    if (typeof jQuery === 'undefined') {
        setTimeout(arguments.callee, 50);
        return;
    }
    var $ = jQuery;
    $(function() {
        var base = (typeof Config !== 'undefined' && Config.moduleurl) ? Config.moduleurl : '';
        var table = $('#table');
        
        function fmtTime(v) {
            if (v === null || v === undefined || v === '') return '';
            var n = Number(v);
            if (!isNaN(n) && isFinite(n)) {
                return new Date((n > 1e12 ? n : n * 1000)).toLocaleString('zh-CN');
            }
            var s = String(v).trim();
            var d = new Date(s.replace(' ', 'T'));
            return isNaN(d.getTime()) ? s : d.toLocaleString('zh-CN');
        }
        
        if (typeof table.bootstrapTable !== 'function' || table.data('bootstrap.table')) {
            return;
        }
        table.bootstrapTable({
            url: base + '/mes/bom/index',
            pk: 'id',
            sortName: 'id',
            sortOrder: 'desc',
            pagination: true,
            sidePagination: 'server',
            pageSize: 20,
            pageList: [10, 20, 50],
            responseHandler: function (res) {
                var data = res.data || {};
                return {
                    total: data.total || 0,
                    rows: data.list || []
                };
            },
            columns: [
                {field: 'id', title: 'ID', width: 80, sortable: true},
                {field: 'bom_no', title: 'BOM编号', align: 'left'},
                {field: 'product.name', title: '产品', align: 'left'},
                {field: 'model.name', title: '型号', align: 'left'},
                {field: 'version', title: '版本', width: 100},
                {field: 'status', title: '状态', width: 100, formatter: function(value) {
                    var statusMap = {0: '草稿', 1: '审核中', 2: '已发布', 3: '已废弃'};
                    var classMap = {0: 'secondary', 1: 'warning', 2: 'success', 3: 'danger'};
                    return '<span class="badge badge-' + (classMap[value] || 'secondary') + '">' + (statusMap[value] || '未知') + '</span>';
                }},
                {field: 'create_time', title: '创建时间', width: 180, formatter: fmtTime},
                {field: 'operate', title: '操作', width: 150, events: {
                    'click .btn-edit': function(e, value, row) {
                        location.href = base + '/mes/bom/edit?ids=' + row.id;
                    },
                    'click .btn-items': function(e, value, row) {
                        location.href = base + '/mes/bom/items?ids=' + row.id;
                    },
                    'click .btn-del': function(e, value, row) {
                        if (confirm('确定要删除吗？')) {
                            $.post(base + '/mes/bom/del', {ids: row.id}, function(r) {
                                if (r.code == 1) {
                                    table.bootstrapTable('refresh');
                                    alert(r.msg || '删除成功');
                                } else {
                                    alert(r.msg || '删除失败');
                                }
                            }, 'json');
                        }
                    }
                }, formatter: function(value, row) {
                    return '<a href="' + base + '/mes/bom/edit?ids=' + row.id + '" class="btn btn-xs btn-success btn-edit">编辑</a> ' +
                           '<a href="' + base + '/mes/bom/items?ids=' + row.id + '" class="btn btn-xs btn-info btn-items">明细</a> ' +
                           '<a href="javascript:;" class="btn btn-xs btn-danger btn-del">删除</a>';
                }}
            ]
        });
        
        $(document).off('click', '#toolbar .btn-refresh').on('click', '#toolbar .btn-refresh', function() {
            table.bootstrapTable('refresh');
        });
        
        $(document).off('click', '#toolbar .btn-edit').on('click', '#toolbar .btn-edit', function() {
            var rows = table.bootstrapTable('getSelections');
            if (rows.length != 1) {
                alert('请选择一条记录');
                return;
            }
            location.href = base + '/mes/bom/edit?ids=' + rows[0].id;
        });
        
        $(document).off('click', '#toolbar .btn-del').on('click', '#toolbar .btn-del', function() {
            var rows = table.bootstrapTable('getSelections');
            if (rows.length == 0) {
                alert('请选择要删除的记录');
                return;
            }
            if (!confirm('确定要删除选中的 ' + rows.length + ' 条记录吗？')) {
                return;
            }
            var ids = rows.map(function(r) { return r.id; });
            $.post(base + '/mes/bom/del', {ids: ids.join(',')}, function(r) {
                if (r.code == 1) {
                    table.bootstrapTable('refresh');
                    alert(r.msg || '删除成功');
                } else {
                    alert(r.msg || '删除失败');
                }
            }, 'json');
        });
        
        table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function() {
            var rows = table.bootstrapTable('getSelections');
            if (rows.length > 0) {
                $('.btn-edit, .btn-del').removeClass('disabled btn-disabled');
            } else {
                $('.btn-edit, .btn-del').addClass('disabled btn-disabled');
            }
        });
    });
})();
</script>
