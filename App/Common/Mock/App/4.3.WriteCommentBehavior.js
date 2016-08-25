// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case

// my_user_id 用户id  type comment  relation_id comment_id
var params = {
  comment       : '添加子评论',
  my_user_id    : '1000',
  type          : 'comment',
  relation_id   : '37',
  order_no      : order_no(),
  channel       : 'UFZT',
  service       : 'write_comment'
};

$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
