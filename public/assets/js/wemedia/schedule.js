(function(){
  var base = '/index/wemedia/schedule';
  var page = 1, limit = 20;
  function loadList(){
    $.get(base + '/list', { page: page, limit: limit, status: $('#schedule-status').val() }, function(r){
      if (r.code !== 1) {
        $('#schedule-tbody').html('<tr><td colspan="7" class="text-center">' + (r.msg || '加载失败') + '</td></tr>');
        return;
      }
      var list = (r.data && r.data.list) ? r.data.list : [];
      var total = (r.data && r.data.total) ? r.data.total : 0;
      var html = '';
      list.forEach(function(row){
        html += '<tr><td>' + row.id + '</td><td>' + (row.relate_type_text || '-') + '</td><td>' + (row.relate_id || '-') + '</td><td>' + (row.platform || '-') + '</td><td>' + (row.plan_time_text || '-') + '</td><td>' + (row.status_text || '-') + '</td><td>';
        html += '<a href="' + base + '/edit?id=' + row.id + '" class="btn btn-xs btn-success me-1">编辑</a>';
        html += '<button type="button" class="btn btn-xs btn-danger btn-schedule-del" data-id="' + row.id + '">删除</button></td></tr>';
      });
      $('#schedule-tbody').html(html || '<tr><td colspan="7" class="text-center">暂无数据</td></tr>');
      $('#schedule-pager').text('共 ' + total + ' 条');
    }, 'json');
  }
  $('#schedule-search').on('click', function(){ page = 1; loadList(); });
  $(document).on('click', '.btn-schedule-del', function(){
    if (!confirm('确定删除？')) return;
    $.post(base + '/del', { id: $(this).data('id') }, function(r){
      alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
      if (r.code === 1) loadList();
    }, 'json');
  });
  loadList();
})();
