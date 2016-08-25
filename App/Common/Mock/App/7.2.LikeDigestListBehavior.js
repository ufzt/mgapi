// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case


var params = {
  my_user_id    : '22235',
  current_page  : '',
  page_size     : '',
  order_no      : order_no(),
  channel       : 'UFZT',
  service       : 'like_message_list'
};


$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
