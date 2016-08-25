var pad_num = function(num){
  num += "";
  return num.replace(/^(\d)$/,"0$1");
}

function getRandomString(len) {  
    len = len || 32;  
    var $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    var maxPos = $chars.length;  
    var pwd = '';  
    for (i = 0; i < len; i++) {  
        pwd += $chars.charAt(Math.floor(Math.random() * maxPos));  
    }  
    return pwd;  
}  

var order_no = function(){
  var date = new Date();
  var year = pad_num(date.getFullYear()-2000);
  var month = pad_num(date.getMonth()+1);
  var day = pad_num(date.getDate());
  var hour = pad_num(date.getHours());
  var minute = pad_num(date.getMinutes());
  var second = pad_num(date.getSeconds());
  return year +month +day +hour +minute +second + getRandomString(4);
}

document.write(' API MOCK SCRIPT (C) https://github.com/ufzt 2016');
document.write('<script src="http://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>');
