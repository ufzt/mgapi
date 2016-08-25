// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case


var params = {
  tag           : '创',
  order_no      : order_no(),
  channel       : 'UFZT',
  service       : 'search_ugc_tags'
};




$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
