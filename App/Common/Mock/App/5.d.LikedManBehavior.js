// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case


var params = {
  type          : 'comment',
  relation_id   : '15816',
  order_no      : order_no(),
  channel       : 'UFZT',
  service       : 'liked_man'
};


$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
