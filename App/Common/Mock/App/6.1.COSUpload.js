// C-1 【请求参数】和【返回参数】的key值 统一规范为snake_case


var params = {
  // file_path     : 'C:/Users/Administrator/Desktop/图片库/11.jpg',
  file_path     : '/home/ufzt/work/mgapi/App/Common/Behavior/Derivative/office.mp4',
  file_name     : 'office.mp4',
  cos_folder    : 'BannerImage',
  order_no      : order_no(),channel:'UFZT',
  service       : 'cos_upload'
};


$.ajax({type:'POST', dataType:'text', url:'/', data: params,
        success:function(data,s,xhr){console.log(data);}});
