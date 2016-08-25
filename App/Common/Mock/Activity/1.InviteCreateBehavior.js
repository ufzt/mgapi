// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case

var params = {
  order_no   : order_no(),
  channel    : '00HH52',
  service    : 'activity.create_invite',
    user_id  : 1000
};





$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});




//http://dev.api.com/?callback=H&order_no=x&channel=00HH52&service=activity.create_invite&user_id=68360
