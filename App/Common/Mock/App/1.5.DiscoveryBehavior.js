// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case

// user_id 用户ID  branch 分类  sort_type 排序方式 
var params = {
  my_user_id  : '1000',
  // branch      : 'cool_play',
  sort_type   : 1,
  current_page: 2,
  // page_size   : 35,
  order_no    : order_no(),channel:'UFZT',
  service     : 'discovery'
};

$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
