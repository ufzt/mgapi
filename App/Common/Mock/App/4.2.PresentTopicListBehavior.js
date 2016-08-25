// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case

// my_user_id 用户id  type video|project  relation_id video_id|project_id
var params = {
  my_user_id    : '69461',
  type          : 'video',
  relation_id   : '1040',
  current_page  : 1,
  page_size     : 20,
  order_no      : order_no(),channel:'UFZT',
  service       : 'topic_list',
};

$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
