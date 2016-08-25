// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case


var params = {
  my_user_id    : '1000',
  follow_user_id: '39005',
  order_no      : order_no(),
  channel       : 'UFZT',
  service       : 'unfollow_maker'
};



$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
