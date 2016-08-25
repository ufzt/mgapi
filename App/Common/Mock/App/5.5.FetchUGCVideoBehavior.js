// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case


var params = {
  video_id      : '1062',
  order_no      : order_no(),
  channel       : 'UFZT',
  service       : 'fetch_ugc_video'
};




$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
