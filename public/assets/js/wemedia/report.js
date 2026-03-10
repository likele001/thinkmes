(function(){
  var base = '/index/wemedia/report';
  var page = 1, limit = 20;
  var chartDom = document.getElementById('report-chart');
  var chart = chartDom && window.echarts ? echarts.init(chartDom) : null;

  function loadChart(){
    if (!chart) return;
    var platform = $('#report-chart-platform').val() || '';
    var days = $('#report-chart-days').val() || '30';
    $.get(base + '/chart', { platform: platform, days: days }, function(r){
      if (r.code !== 1 || !r.data) return;
      var dates = r.data.dates || [];
      var series = r.data.series || [];
      chart.setOption({
        tooltip: { trigger: 'axis' },
        legend: { data: series.map(function(s){ return s.name; }) },
        xAxis: { type: 'category', data: dates },
        yAxis: { type: 'value' },
        series: series.map(function(s){ return { name: s.name, type: 'line', data: s.data }; })
      });
    }, 'json');
  }

  function loadList(){
    $.get(base + '/list', {
      page: page,
      limit: limit,
      report_date: $('#report-date').val() || '',
      platform: $('#report-platform').val() || ''
    }, function(r){
      if (r.code !== 1) {
        $('#report-tbody').html('<tr><td colspan="6" class="text-center">' + (r.msg || '加载失败') + '</td></tr>');
        return;
      }
      var list = (r.data && r.data.list) ? r.data.list : [];
      var total = (r.data && r.data.total) ? r.data.total : 0;
      var html = '';
      list.forEach(function(row){
        html += '<tr><td>' + row.id + '</td><td>' + (row.report_date || '-') + '</td><td>' + (row.platform || '-') + '</td><td>' + (row.metric_type_text || '-') + '</td><td>' + (row.metric_value || 0) + '</td><td>';
        html += '<button type="button" class="btn btn-xs btn-success me-1 btn-report-edit" data-id="' + row.id + '" data-date="' + (row.report_date||'') + '" data-platform="' + (row.platform||'') + '" data-metric="' + (row.metric_type||'') + '" data-value="' + (row.metric_value||0) + '" data-remark="' + (row.remark||'').replace(/"/g,'&quot;') + '">编辑</button>';
        html += '<button type="button" class="btn btn-xs btn-danger btn-report-del" data-id="' + row.id + '">删除</button></td></tr>';
      });
      $('#report-tbody').html(html || '<tr><td colspan="6" class="text-center">暂无数据</td></tr>');
      $('#report-pager').text('共 ' + total + ' 条');
    }, 'json');
  }

  $('#report-chart-refresh').on('click', loadChart);
  $('#report-chart-platform, #report-chart-days').on('change', loadChart);
  $('#report-search').on('click', function(){ page = 1; loadList(); });
  $('#report-add-btn').on('click', function(){
    $('#report-edit-id').val('0');
    $('#report-edit-date').val(new Date().toISOString().slice(0,10));
    $('#report-edit-platform').val('');
    $('#report-edit-metric').val('view');
    $('#report-edit-value').val('0');
    $('#report-edit-remark').val('');
    $('#report-edit-modal').modal('show');
  });
  $(document).on('click', '.btn-report-edit', function(){
    $('#report-edit-id').val($(this).data('id'));
    $('#report-edit-date').val($(this).data('date'));
    $('#report-edit-platform').val($(this).data('platform'));
    $('#report-edit-metric').val($(this).data('metric'));
    $('#report-edit-value').val($(this).data('value'));
    $('#report-edit-remark').val($(this).data('remark'));
    $('#report-edit-modal').modal('show');
  });
  $('#report-edit-save').on('click', function(){
    var payload = {
      id: $('#report-edit-id').val(),
      report_date: $('#report-edit-date').val(),
      platform: $('#report-edit-platform').val(),
      metric_type: $('#report-edit-metric').val(),
      metric_value: $('#report-edit-value').val(),
      remark: $('#report-edit-remark').val()
    };
    if (!payload.report_date) { alert('请选择数据日期'); return; }
    $.post(base + '/save', payload, function(r){
      alert(r.msg || (r.code === 1 ? '保存成功' : '失败'));
      if (r.code === 1) { $('#report-edit-modal').modal('hide'); loadList(); loadChart(); }
    }, 'json');
  });
  $(document).on('click', '.btn-report-del', function(){
    if (!confirm('确定删除？')) return;
    $.post(base + '/del', { id: $(this).data('id') }, function(r){
      alert(r.msg || (r.code === 1 ? '删除成功' : '失败'));
      if (r.code === 1) { loadList(); loadChart(); }
    }, 'json');
  });

  loadList();
  loadChart();
})();
