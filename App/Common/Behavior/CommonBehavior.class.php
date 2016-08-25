<?php
namespace Common\Behavior;

use Common\Atom\Result;
use Think\Log;

class CommonBehavior{

  # pick 参数 ['current_page', 'page_size']
  static function pick_page_params(){
    # current_page 当前页
    # 默认第一页
    if (!isset($_REQUEST['current_page']) || intval($_REQUEST['current_page']) < 1 )
      $_REQUEST['current_page'] = 1;

    # page_size 每页的个数
    # 默认20个
    if (!isset($_REQUEST['page_size']) || intval($_REQUEST['page_size']) < 1 )
      $_REQUEST['page_size'] = 20;
  }

  # pick 参数 'my_user_id'
  static function pick_my_user_id(){
    if (!isset($_REQUEST['my_user_id']) || intval($_REQUEST['my_user_id']) < 1 )
      $_REQUEST['my_user_id'] = '';
  }

  # 必填参数检查, 传入参数名数组
  static function inquire($params){
    $result = new Result();
    foreach ($params as $param) {
      if (empty($_REQUEST[$param])) {
        $result->code  = Result::EMPTY_PARAM;
        $result->param = $param;
        return $result;
      }
    }

    $result->success();
    return $result;
  }

  protected static function pack_replace($str){
    $str = str_replace(
      '/public/upload/app_video',C('site').'/public/upload/app_video',$str
    );
    $str = preg_replace('/style=.*?>/','style="width:100%"/>',$str);
    $str = stripslashes($str);
    return $str;
  }

  # 定义日志级别
  const LOG_ERROR = 'ERR';   // 错误信息, 生产级别
  const LOG_DEBUG = 'DEBUG'; // 调试信息, 开发级别
  # 写日志方法
  protected static function mp_log($method, $log, $log_level=self::LOG_ERROR){
    $text = $method."()\n\t".$log."\n";
    Log::record($text, $log_level);
  }

  //验证手机号码
  protected static function mobile($str){
    if ($str=='')
      return false;

    return preg_match('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}|^170[059]\d{7}$#', $str);
  }
}
