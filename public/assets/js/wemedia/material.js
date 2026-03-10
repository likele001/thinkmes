(function(){
  var base = '/index/wemedia/material';
  var page = 1, limit = 20;
  function loadList(){
    $.get(base + '/list', { page: page, limit: limit, keyword: $('#material-keyword').val(), type: $('#material-type').val() }, function(r){
      if (r.code !== 1) {
        $('#material-tbody').html('<tr><td colspan="6" class="text-center">' + (r.msg || '加载失败') + '</td></tr>');
        return;
      }
      var list = (r.data && r.data.list) ? r.data.list : [];
      var total = (r.data && r.data.total) ? r.data.total : 0;
      var html = '';
      list.forEach(function(row){
        var up = row.create_time ? new Date(row.create_time * 1000).toLocaleString() : '-';
        html += '<tr><td>' + row.id + '</td><td>' + (row.name || '-') + '</td><td>' + (row.type_text || '-') + '</td><td>' + (row.size_text || '-') + '</td><td>' + up + '</td><td>';
        html += '<a href="' + base + '/edit?id=' + row.id + '" class="btn btn-xs btn-success me-1">编辑</a>';
        html += '<button type="button" class="btn btn-xs btn-danger btn-material-del" data-id="' + row.id + '">删除</button></td></tr>';
      });
      $('#material-tbody').html(html || '<tr><td colspan="6" class="text-center">暂无数据</td></tr>');
      $('#material-pager').text('共 ' + total + ' 条');
    }, 'json');
  }
  $('#material-search').on('click', function(){ page = 1; loadList(); });
  $(document).on('click', '.btn-material-del', function(){
    if (!confirm('确定删除？')) return;
    var id = $(this).data('id');
    $.post(base + '/del', { id: id }, function(r){
      alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
      if (r.code === 1) loadList();
    }, 'json');
  });
  loadList();
})();
