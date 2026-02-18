<?php /*a:1:{s:53:"/www/wwwroot/thinkmes/app/index/view/worker/scan.html";i:1771455183;}*/ ?>
<div class="container-main">
  <div class="member-layout">
    <div class="member-left">
      <div class="card">
        <div class="card-body">
          <ul class="nav nav-pills flex-column">
            <li class="nav-item"><a class="nav-link" href="/index/user/index"><i class="fa fa-home mr-2"></i>会员中心</a></li>
            <li class="nav-item"><a class="nav-link" href="/index/user/profile"><i class="fa fa-user mr-2"></i>个人资料</a></li>
            <li class="nav-item"><a class="nav-link" href="/index/user/changepwd"><i class="fa fa-key mr-2"></i>修改密码</a></li>
            <li class="nav-item"><a class="nav-link" href="/index/user/logout"><i class="fa fa-sign-out-alt mr-2"></i>退出</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="member-right">
      <div class="card">
        <div class="card-header">扫码报工</div>
        <div class="card-body">
          <div id="task-info" style="margin-bottom:15px;">
            <div><strong>订单号：</strong><span id="order-no">-</span></div>
            <div><strong>产品名称：</strong><span id="product-name">-</span></div>
            <div><strong>型号：</strong><span id="model-name">-</span></div>
            <div><strong>工序：</strong><span id="process-name">-</span></div>
            <div><strong>分配数量：</strong><span id="assign-qty">0</span></div>
            <div><strong>已报数量：</strong><span id="reported-qty">0</span></div>
            <div><strong>待报数量：</strong><span id="pending-qty">0</span></div>
          </div>
          <form id="form-report">
            <input type="hidden" id="allocation-id" value="<?php echo htmlentities((string) (isset($allocation_id) && ($allocation_id !== '')?$allocation_id:0)); ?>">
            <div class="form-group">
              <label>报工方式</label>
              <select class="form-control" id="work-type" name="work_type">
                <option value="piece">按件报工</option>
                <option value="hour">按工时报工</option>
              </select>
            </div>
            <div class="form-group" id="field-itemnos">
              <label>选择产品编号</label>
              <div id="item-list" class="border rounded p-2" style="max-height:260px;overflow-y:auto;"></div>
              <small class="form-text text-muted">已选择 <span id="selected-count">0</span> 个产品编号，每个编号可单独上传图片</small>
            </div>
            <div class="form-group" id="field-hours" style="display:none;">
              <label>工时（小时）</label>
              <input type="number" class="form-control" id="work-hours" name="work_hours" min="0.1" step="0.1" placeholder="请输入本次工时">
            </div>
            <button type="submit" class="btn btn-primary">提交报工</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(function(){
  var tk = localStorage.getItem('token')||'';
  if(!tk){location.href='/index/user/login';return;}
  var allocationId = parseInt($('#allocation-id').val()||'0',10)||0;
  var itemImages = {};
  if(!allocationId){
    alert('无效的任务信息');
    return;
  }
  $('#work-type').on('change', function(){
    var v=$(this).val();
    if(v==='hour'){
      $('#field-itemnos').hide();
      $('#field-hours').show();
    }else{
      $('#field-itemnos').show();
      $('#field-hours').hide();
    }
  });
  $.ajax({
    url:'/api/worker/taskInfo',
    data:{allocation_id:allocationId},
    headers:{'Authorization':'Bearer '+tk},
    success:function(r){
      if(r.code!==1){alert(r.msg||'加载任务失败');return;}
      var d=r.data||{};
      $('#order-no').text(d.order_no||'-');
      $('#product-name').text(d.product_name||'-');
      $('#model-name').text(d.model_name||'-');
      $('#process-name').text(d.process_name||'-');
      $('#assign-qty').text(d.assign_qty||0);
      $('#reported-qty').text(d.reported_qty||0);
      $('#pending-qty').text(d.pending_qty||0);

      var items = d.item_nos || [];
      var html = '';
      if(items.length){
        items.forEach(function(no, idx){
          var safeNo = String(no);
          html += '<div class="product-item-block mb-2 p-2 border rounded" data-item-no="'+safeNo+'">'
            +   '<div class="form-check mb-2">'
            +     '<input class="form-check-input item-no-check" type="checkbox" value="'+safeNo+'" id="item_'+idx+'">'
            +     '<label class="form-check-label" for="item_'+idx+'">'+safeNo+'</label>'
            +   '</div>'
            +   '<div class="d-flex align-items-start">'
            +     '<div class="flex-grow-1">'
            +       '<input type="file" class="form-control form-control-sm item-image-input" accept="image/*" multiple>'
            +       '<small class="form-text text-muted">为该产品上传图片，可多选</small>'
            +     '</div>'
            +   '</div>'
            +   '<div class="item-image-preview mt-2 d-flex flex-wrap"></div>'
            + '</div>';
        });
      }else{
        html = '<div class="text-muted">暂无可报工的产品编号</div>';
      }
      $('#item-list').html(html);

      function updateSelectedCount(){
        var c = $('.item-no-check:checked').length;
        $('#selected-count').text(c);
      }
      $(document).off('change.itemnos').on('change.itemnos', '.item-no-check', updateSelectedCount);
      updateSelectedCount();

      $(document).off('change.itemimages').on('change.itemimages', '.item-image-input', function(){
        var $input = $(this);
        var $block = $input.closest('.product-item-block');
        var itemNo = $block.data('item-no');
        var files = this.files;
        if(!itemNo || !files || !files.length){ return; }
        if(!itemImages[itemNo]){
          itemImages[itemNo] = [];
        }
        $block.find('.item-no-check').prop('checked', true).trigger('change');
        Array.prototype.forEach.call(files, function(file){
          var formData = new FormData();
          formData.append('file', file);
          $.ajax({
            url:'/api/common/upload',
            type:'POST',
            data:formData,
            processData:false,
            contentType:false,
            headers:{'Authorization':'Bearer '+tk},
            success:function(r){
              if(r.code===1 && r.data && r.data.url){
                var url = r.data.url;
                itemImages[itemNo].push(url);
                var $wrap = $('<div class="me-2 mb-2 position-relative" style="width:70px;height:70px;overflow:hidden;border:1px solid #ddd;border-radius:4px;"></div>');
                var $img = $('<img>').attr('src', url).css({width:'100%',height:'100%',objectFit:'cover'});
                var $del = $('<span class="badge bg-danger" style="position:absolute;top:2px;right:2px;cursor:pointer;">×</span>');
                $del.on('click', function(){
                  var arr = itemImages[itemNo] || [];
                  var idx = arr.indexOf(url);
                  if(idx>-1){
                    arr.splice(idx,1);
                  }
                  $wrap.remove();
                });
                $wrap.append($img).append($del);
                $block.find('.item-image-preview').append($wrap);
              }else{
                alert(r.msg||'上传失败');
              }
            },
            error:function(){
              alert('上传失败');
            }
          });
        });
        $input.val('');
      });
    }
  });
  $('#form-report').on('submit', function(e){
    e.preventDefault();
    var workType=$('#work-type').val();
    var hours=parseFloat($('#work-hours').val()||'0')||0;
    var selectedItems=[];
    if(workType==='piece'){
      $('.product-item-block').each(function(){
        var $block = $(this);
        var $check = $block.find('.item-no-check');
        if($check.prop('checked')){
          var no = $block.data('item-no');
          if(no){
            var imgs = itemImages[no] || [];
            selectedItems.push({item_no:no,images:imgs});
          }
        }
      });
      if(!selectedItems.length){
        alert('请选择要报工的产品编号');
        return;
      }
    }
    if(workType==='hour'&&hours<=0){
      alert('请填写工时');
      return;
    }
    var payload={allocation_id:allocationId,work_type:workType};
    if(workType==='piece'){
      var nos = selectedItems.map(function(it){return it.item_no;});
      // 直接以数组方式提交，后端可按 item_nos[] 接收
      payload.item_nos = nos;
      var imgMap = {};
      selectedItems.forEach(function(it){
        if(it.images && it.images.length){
          imgMap[it.item_no] = it.images;
        }
      });
      if(Object.keys(imgMap).length){
        payload.images = JSON.stringify(imgMap);
      }
    }else{
      payload.work_hours=hours;
    }
    $.ajax({
      url:'/api/worker/report',
      method:'POST',
      data:payload,
      headers:{'Authorization':'Bearer '+tk},
      success:function(r){
        if(r.code===1){
          alert('报工成功');
          location.href='/index/user/index';
        }else{
          alert(r.msg||'报工失败');
        }
      }
    });
  });
});
</script>
