// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case
var params = {
  type          : 'finalize', // type 提交类型  draft 草稿  finalize 定稿
  my_user_id    : '1000',
  // video_id      : '101010',// 没有video_id就是新增UGC，有就是编辑
  // UGC prop
    title       : 'hello',
    photo_url   : 'http://api-10046922.file.myqcloud.com/test/系统推送.jpg',
    video_url   : 'http://api-10046922.file.myqcloud.com/test/8896_office.mp4',
  order_no      : order_no(),channel:'UFZT',
  service       : 'submit_ugc_video'
};

$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
