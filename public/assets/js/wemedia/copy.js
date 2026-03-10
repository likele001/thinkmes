(function(){
  var base = '/index/wemedia/copy';
  var page = 1, limit = 20;
  function loadList(){
    $.get(base + '/list', { page: page, limit: limit, keyword: $('#copy-keyword').val(), status: $('#copy-status').val() }, function(r){
      if (r.code !== 1) {
        $('#copy-tbody').html('<tr><td colspan="6" class="text-center">' + (r.msg || '加载失败') + '</td></tr>');
        return;
      }
      var list = (r.data && r.data.list) ? r.data.list : [];
      var total = (r.data && r.data.total) ? r.data.total : 0;
      var html = '';
      list.forEach(function(row){
        var up = row.update_time ? new Date(row.update_time * 1000).toLocaleString() : '-';
        html += '<tr><td>' + row.id + '</td><td><span title="' + (row.content_preview || '').replace(/"/g, '&quot;') + '">' + (row.title || '-') + '</span></td>';
        html += '<td>' + (row.platform || '-') + '</td><td>' + (row.status_text || '-') + '</td><td>' + up + '</td><td>';
        html += '<a href="' + base + '/edit?id=' + row.id + '" class="btn btn-xs btn-success me-1">编辑</a>';
        html += '<a href="/index/wemedia/video/add?from_copy=' + row.id + '" class="btn btn-xs btn-outline-primary me-1">转为口播</a>';
        html += '<button type="button" class="btn btn-xs btn-danger btn-copy-del" data-id="' + row.id + '">删除</button></td></tr>';
      });
      $('#copy-tbody').html(html || '<tr><td colspan="6" class="text-center">暂无数据</td></tr>');
      $('#copy-pager').text('共 ' + total + ' 条');
    }, 'json');
  }
  $('#copy-search').on('click', function(){ page = 1; loadList(); });
  $(document).on('click', '.btn-copy-del', function(){
    if (!confirm('确定删除？')) return;
    var id = $(this).data('id');
    $.post(base + '/del', { id: id }, function(r){
      alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
      if (r.code === 1) loadList();
    }, 'json');
  });
  loadList();
})();
