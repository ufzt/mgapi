// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case

var params = {
  order_no   : order_no(),
  channel    : '00HH51',
  service    : 'activity.visit_invite',
    sn_code  : 'invite16080574631413466257'
};





$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});




//http://dev.api.com/?callback=H&order_no=x&channel=00HH51&service=activity.visit_invite&sn_code=invite16080574631413466257
