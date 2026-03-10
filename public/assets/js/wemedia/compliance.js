(function(){
  var base = '/index/wemedia/compliance';
  var page = 1, limit = 20;

  function loadList(){
    $.get(base + '/list', { page: page, limit: limit }, function(r){
      if (r.code !== 1) {
        $('#compliance-tbody').html('<tr><td colspan="5" class="text-center">' + (r.msg || '加载失败') + '</td></tr>');
        return;
      }
      var list = (r.data && r.data.list) ? r.data.list : [];
      var total = (r.data && r.data.total) ? r.data.total : 0;
      var html = '';
      list.forEach(function(row){
        var time = row.create_time ? new Date(row.create_time * 1000).toLocaleString() : '-';
        var cls = row.result === 1 ? 'text-danger' : 'text-success';
        html += '<tr><td>' + row.id + '</td><td>' + (row.content_preview || '-') + '</td><td class="' + cls + '">' + (row.result_text || '-') + '</td><td>' + time + '</td><td>';
        html += '<button type="button" class="btn btn-xs btn-danger btn-compliance-del" data-id="' + row.id + '">删除</button></td></tr>';
      });
      $('#compliance-tbody').html(html || '<tr><td colspan="5" class="text-center">暂无数据</td></tr>');
      $('#compliance-pager').text('共 ' + total + ' 条');
    }, 'json');
  }

  $('#compliance-check-btn').on('click', function(){
    var text = $('#compliance-text').val() || '';
    if (!text.trim()) { alert('请输入待检测文案'); return; }
    $(this).prop('disabled', true);
    $.post(base + '/check', { content_type: 'text', content_text: text }, function(r){
      if (r.code !== 1) {
        $('#compliance-result').hide().html('');
        alert(r.msg || '检测失败');
        return;
      }
      var d = r.data || {};
      var html = '<strong>结果：</strong><span class="' + (d.result === 1 ? 'text-danger' : 'text-success') + '">' + (d.result_text || '') + '</span>';
      if (d.suggestion) html += '<br><strong>建议：</strong>' + d.suggestion;
      $('#compliance-result').html(html).show();
      loadList();
    }, 'json').always(function(){ $('#compliance-check-btn').prop('disabled', false); });
  });

  $(document).on('click', '.btn-compliance-del', function(){
    if (!confirm('确定删除？')) return;
    $.post(base + '/del', { id: $(this).data('id') }, function(r){
      alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
      if (r.code === 1) loadList();
    }, 'json');
  });

  loadList();
})();
