// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case


var params = {
  keyword       : '搜素内容',
  type          : '',
  order_no      : order_no(),
  current_page  : '',
  page_size     : '',
  channel       : 'UFZT',
  service       : 'search_video_project'
};




$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
