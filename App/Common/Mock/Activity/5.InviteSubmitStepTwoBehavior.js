// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case

var params = {
  order_no   : order_no(),
  channel    : '00HH51',
  service    : 'activity.submit_invite_step2',
    sn_code       : 'invite16080574631413466257',
    dkey          : '11d32a5ca7937fd1dd829uS.B.KUCaZmeiBODFUdCfqLeLJXw9HDW',
    password      : 'password'
};


$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});





//http://dev.api.com/?callback=H&order_no=x&channel=00HH51&password=password&service=activity.submit_invite_step2&sn_code=invite16080574631413466257&dkey=11d32a5ca7937fd1dd829uS.B.KUCaZmeiBODFUdCfqLeLJXw9HDW
