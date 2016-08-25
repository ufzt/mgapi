// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case

// my_user_id 用户id  type video|project  relation_id video_id|project_id
var params = {
  comment       : '测试父评论',
  my_user_id    : '1000',
  type          : 'project',
  relation_id   : '317',
  order_no      : order_no(), channel:'UFZT',
  service       : 'write_topic'
};


$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
