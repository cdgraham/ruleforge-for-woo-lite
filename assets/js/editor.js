(function($){
  function insertRow($ta, sample){
    try{ var arr = JSON.parse($ta.val()||"[]"); arr.push(sample); $ta.val(JSON.stringify(arr,null,2)); }
    catch(e){ alert('JSON parse error—please fix your JSON first.'); }
  }
  $(function(){
    $('#rf-add-cond').on('click', function(){ insertRow($('#rf-conditions'), {"type":"subtotal","op":">=","value":100}); });
    $('#rf-add-action').on('click', function(){ insertRow($('#rf-actions'), {"type":"fee_percent","value":2,"label":"Surcharge"}); });
  });
})(jQuery);
