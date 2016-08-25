// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case

// my_user_id 用户id  type video|project|comment  relation_id video_id|project_id|comment_id
var params = {
  my_user_id    : '68360',
  type          : 'video',
  relation_id   : '1361',
  order_no      : order_no(),
  channel       : 'UFZT',
  service       : 'click_liked'
};


$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
